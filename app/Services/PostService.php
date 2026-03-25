<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostService
{
    public function listFeatured(): Collection
    {
        $now = Carbon::now();
        $last24Hours = $now->copy()->subDay();
        $last7Days = $now->copy()->subDays(7);

        $commentStatsQuery = Comment::query()
            ->selectRaw('post_id')
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as comments_last_24h_count',
                [$last24Hours]
            )
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as comments_last_7d_count',
                [$last7Days]
            )
            ->selectRaw('COUNT(*) as comments_total_count')
            ->where('is_approved', true)
            ->groupBy('post_id');

        return Post::query()
            ->select('posts.*')
            ->leftJoinSub($commentStatsQuery, 'comment_stats', function ($join) {
                $join->on('comment_stats.post_id', '=', 'posts.id');
            })
            ->selectRaw('COALESCE(comment_stats.comments_total_count, 0) as comments_count')
            ->selectRaw(
                '
                (
                    (COALESCE(comment_stats.comments_last_24h_count, 0) * 5)
                    + (COALESCE(comment_stats.comments_last_7d_count, 0) * 3)
                    + COALESCE(comment_stats.comments_total_count, 0)
                    + CASE
                        WHEN posts.published_at >= ? THEN 20
                        WHEN posts.published_at >= ? THEN 10
                        WHEN posts.published_at >= ? THEN 5
                        ELSE 0
                    END
                ) as score
                ',
                [$last24Hours, $now->copy()->subDays(3), $last7Days]
            )
            ->with(['user', 'categories'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->orderByDesc('score')
            ->limit(5)
            ->get();
    }

    public function listPublished(): Collection
    {
        return Post::query()
            ->with(['user', 'categories'])
            ->withCount([
                'comments' => fn ($query) => $query->where('is_approved', true),
            ])
            ->where('status', Post::STATUS_PUBLISHED)
            ->latest()
            ->get();
    }

    public function listManageableForUser(User $user): Collection
    {
        $query = Post::query()
            ->with(['user', 'categories'])
            ->withCount('comments')
            ->latest();

        if ($user->role === User::ROLE_ADMIN) {
            return $query->get();
        }

        return $query
            ->where('user_id', $user->id)
            ->get();
    }

    public function showPublishedById(int|string $postId, ?User $user = null): Post
    {
        return Post::query()
            ->with([
                'user',
                'categories',
                'comments' => fn ($query) => $query
                    ->where(function ($commentQuery) use ($user) {
                        $commentQuery->where('is_approved', true);

                        if ($user) {
                            $commentQuery->orWhere('user_id', $user->id);
                        }
                    })
                    ->with('user')
                    ->latest(),
            ])
            ->withCount([
                'comments' => fn ($query) => $query
                    ->where(function ($commentQuery) use ($user) {
                        $commentQuery->where('is_approved', true);

                        if ($user) {
                            $commentQuery->orWhere('user_id', $user->id);
                        }
                    }),
            ])
            ->whereKey($postId)
            ->where('status', Post::STATUS_PUBLISHED)
            ->firstOrFail();
    }

    public function showManageableForUser(User $user, Post $post): Post
    {
        if ($user->role === User::ROLE_ADMIN) {
            return $post->load(['user', 'categories', 'comments.user'])->loadCount('comments');
        }

        if ($post->user_id === $user->id) {
            return $post->load(['user', 'categories', 'comments.user'])->loadCount('comments');
        }

        throw new AuthorizationException('Bu işlem için yetkiniz yok.');
    }

    public function createForUser(User $user, array $data): Post
    {
        $categoryIds = $data['category_ids'] ?? [];
        $coverImage = $data['cover_image'] ?? null;
        unset($data['category_ids']);
        unset($data['cover_image']);

        $data = $this->preparePublishPayload($data);

        $post = Post::create([
            ...$data,
            'user_id' => $user->id,
            'slug' => $this->generateUniqueSlug($data['title']),
        ]);

        if ($categoryIds !== []) {
            $post->categories()->sync($categoryIds);
        }

        $this->syncCoverImage($post, $coverImage);

        return $post->load(['user', 'categories'])->loadCount('comments');
    }

    public function updateForUser(User $user, Post $post, array $data): Post
    {
        $hasCategoryIds = array_key_exists('category_ids', $data);
        $categoryIds = $data['category_ids'] ?? [];
        $coverImage = $data['cover_image'] ?? null;
        unset($data['category_ids']);
        unset($data['cover_image']);

        if ($user->role !== User::ROLE_ADMIN) {
            unset($data['status'], $data['published_at']);

            if ($post->status === Post::STATUS_PUBLISHED) {
                $data['status'] = Post::STATUS_DRAFT;
                $data['published_at'] = null;
            }
        }

        $data = $this->preparePublishPayload($data, $post);

        $post->update($data);

        if ($hasCategoryIds) {
            $post->categories()->sync($categoryIds);
        }

        $this->syncCoverImage($post, $coverImage);

        return $post->load(['user', 'categories'])->loadCount('comments');
    }

    public function deleteForUser(User $user, Post $post): void
    {
        $post->delete();
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Post::query()->where('slug', $slug)->whereNull('deleted_at')->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function preparePublishPayload(array $data, ?Post $post = null): array
    {
        if (!array_key_exists('status', $data)) {
            return $data;
        }

        if ($data['status'] === Post::STATUS_DRAFT) {
            $data['published_at'] = null;

            return $data;
        }

        if ($data['status'] === Post::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = $post?->published_at ?? Carbon::now();
        }

        return $data;
    }

    private function syncCoverImage(Post $post, mixed $coverImage): void
    {
        if (!$coverImage instanceof UploadedFile) {
            return;
        }

        $post->clearMediaCollection('cover_image');
        $post->addMedia($coverImage)->toMediaCollection('cover_image');
    }
}
