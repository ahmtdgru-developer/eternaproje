<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function create(User $user): Response
    {
        return $user->role === User::ROLE_ADMIN
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function update(User $user, Category $category): Response
    {
        return $user->role === User::ROLE_ADMIN
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }

    public function delete(User $user, Category $category): Response
    {
        return $user->role === User::ROLE_ADMIN
            ? Response::allow()
            : Response::deny('Bu işlem için yetkiniz yok.');
    }
}