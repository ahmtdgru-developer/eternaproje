<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private readonly PostService $postService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PostResource::collection($this->postService->listPublished()),
        ]);
    }

    public function featured(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PostResource::collection($this->postService->listFeatured()),
        ]);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $this->authorize('viewAnyManaged', Post::class);

        return response()->json([
            'success' => true,
            'data' => $this->postService->listManageableForUser($request->user()),
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        return response()->json([
            'success' => true,
            'data' => new PostResource($this->postService->createForUser($request->user(), $request->validated())),
        ], 201);
    }

    public function show(Request $request, string $post): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PostResource($this->postService->showPublishedById($post, $request->user())),
        ]);
    }

    public function showMine(Request $request, Post $post): JsonResponse
    {
        $this->authorize('viewManaged', $post);

        return response()->json([
            'success' => true,
            'data' => $this->postService->showManageableForUser($request->user(), $post),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        return response()->json([
            'success' => true,
            'data' => new PostResource($this->postService->updateForUser($request->user(), $post, $request->validated())),
        ]);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $this->postService->deleteForUser($request->user(), $post);

        return response()->json([
            'success' => true,
            'message' => 'Blog silindi.',
        ]);
    }
}