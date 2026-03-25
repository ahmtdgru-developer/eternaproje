<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('posts.{postId}', function ($user, $postId) {
    return $user !== null;
}, ['guards' => ['sanctum']]);
