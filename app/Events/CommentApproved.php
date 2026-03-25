<?php

namespace App\Events;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentApproved implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = (new CommentResource($comment))->resolve();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('posts.' . $this->comment['post']['id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.approved';
    }
}
