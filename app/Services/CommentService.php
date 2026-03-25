<?php

namespace App\Services;

use App\Events\CommentApproved;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCommentApprovedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class CommentService
{
    public function list(array $filters = []): Collection
    {
        $query = Comment::query()
            ->with(['user', 'post'])
            ->latest();

        $approvalStatus = $filters['approval_status'] ?? 'all';

        if ($approvalStatus === 'pending') {
            $query->where('is_approved', false);
        }

        if ($approvalStatus === 'approved') {
            $query->where('is_approved', true);
        }

        return $query->get();
    }

    public function listApprovedForPost(Post $post): Collection
    {
        return $post->comments()
            ->with('user')
            ->where('is_approved', true)
            ->oldest()
            ->get();
    }

    public function listPending(): Collection
    {
        return $this->list(['approval_status' => 'pending']);
    }

    public function createForUser(User $user, Post $post, array $data): Comment
    {
        if ($post->status !== Post::STATUS_PUBLISHED) {
            throw new AuthorizationException('Sadece yayındaki yazılara yorum yapılabilir.');
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $data['content'],
            'is_approved' => $user->role === User::ROLE_ADMIN,
        ]);

        $comment->load(['user', 'post']);

        if ($comment->is_approved) {
            $this->notifyPostAuthor($comment);
            broadcast(new CommentApproved($comment));
        }

        return $comment;
    }

    public function approve(Comment $comment): Comment
    {
        $comment->update([
            'is_approved' => true,
        ]);

        $comment->load(['user', 'post']);

        $this->notifyPostAuthor($comment);
        broadcast(new CommentApproved($comment));

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }

    private function notifyPostAuthor(Comment $comment): void
    {
        $postAuthor = $comment->post?->user;

        if (! $postAuthor || $postAuthor->id === $comment->user_id) {
            return;
        }

        $postAuthor->notify(new PostCommentApprovedNotification($comment));
    }
}