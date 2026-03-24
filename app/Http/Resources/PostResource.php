<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'cover_image' => $this->cover_image,
            'slug' => $this->slug,
            'published_at' => $this->published_at,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'author' => new UserSummaryResource($this->whenLoaded('user')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
