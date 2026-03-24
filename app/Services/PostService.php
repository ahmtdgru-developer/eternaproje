<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostService
{
    public function listPublished(): Collection
    {
        return Post::query()
            ->with(['user', 'categories'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->latest()
            ->get();
    }

    public function listManageableForUser(User $user): Collection
    {
        $query = Post::query()
            ->with(['user', 'categories'])
            ->latest();

        if ($user->role === User::ROLE_ADMIN) {
            return $query->get();
        }

        return $query
            ->where('user_id', $user->id)
            ->get();
    }

    public function showPublishedById(int|string $postId): Post
    {
        return Post::query()
            ->with(['user', 'categories', 'comments.user'])
            ->whereKey($postId)
            ->where('status', Post::STATUS_PUBLISHED)
            ->firstOrFail();
    }

    public function showManageableForUser(User $user, Post $post): Post
    {
        if ($user->role === User::ROLE_ADMIN) {
            return $post->load(['user', 'categories', 'comments.user']);
        }

        if ($post->user_id === $user->id) {
            return $post->load(['user', 'categories', 'comments.user']);
        }

        throw new AuthorizationException('Bu işlem için yetkiniz yok.');
    }

    public function createForUser(User $user, array $data): Post
    {
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $post = Post::create([
            ...$data,
            'user_id' => $user->id,
            'slug' => $this->generateUniqueSlug($data['title']),
        ]);

        if ($categoryIds !== []) {
            $post->categories()->sync($categoryIds);
        }

        return $post->load(['user', 'categories']);
    }

    public function updateForUser(User $user, Post $post, array $data): Post
    {
        $hasCategoryIds = array_key_exists('category_ids', $data);
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $post->update($data);

        if ($hasCategoryIds) {
            $post->categories()->sync($categoryIds);
        }

        return $post->load(['user', 'categories']);
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
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}