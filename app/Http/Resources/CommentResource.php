<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'author' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'surname' => $this->user?->surname,
                'role' => $this->user?->role,
            ],
            'post' => $this->whenLoaded('post', function () {
                return [
                    'id' => $this->post?->id,
                    'title' => $this->post?->title,
                    'slug' => $this->post?->slug,
                ];
            }),
        ];
    }
}