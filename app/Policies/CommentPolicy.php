<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->role === User::ROLE_ADMIN
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function create(User $user): Response
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_WRITER, User::ROLE_USER], true)
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function viewPending(User $user): Response
    {
        return $this->viewAny($user);
    }

    public function approve(User $user, Comment $comment): Response
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Comment $comment): Response
    {
        return $user->role === User::ROLE_ADMIN || ($comment->user_id === $user->id && ! $comment->is_approved)
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }
}