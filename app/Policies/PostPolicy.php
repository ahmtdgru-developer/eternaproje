<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function viewAnyManaged(User $user): Response
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_WRITER], true)
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function viewManaged(User $user, Post $post): Response
    {
        return $user->role === User::ROLE_ADMIN || $post->user_id === $user->id
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function create(User $user): Response
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_WRITER], true)
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function update(User $user, Post $post): Response
    {
        return $user->role === User::ROLE_ADMIN || ($user->role === User::ROLE_WRITER && $post->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function delete(User $user, Post $post): Response
    {
        return $this->update($user, $post);
    }
}