<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function all(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Comment::class);

        $validated = $request->validate([
            'approval_status' => ['nullable', Rule::in(['all', 'pending', 'approved'])],
        ]);

        return response()->json([
            'success' => true,
            'data' => CommentResource::collection($this->commentService->list($validated)),
        ]);
    }

    public function index(Post $post): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => CommentResource::collection($this->commentService->listApprovedForPost($post)),
        ]);
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $this->authorize('create', Comment::class);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($this->commentService->createForUser($request->user(), $post, $request->validated())),
            'message' => $request->user()->role === User::ROLE_ADMIN
                ? 'Yorum kaydedildi.'
                : 'Yorumunuz onay bekliyor.',
        ], 201);
    }

    public function pending(): JsonResponse
    {
        $this->authorize('viewPending', Comment::class);

        return response()->json([
            'success' => true,
            'data' => CommentResource::collection($this->commentService->listPending()),
        ]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $this->authorize('approve', $comment);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($this->commentService->approve($comment)),
            'message' => 'Yorum onaylandı.',
        ]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json([
            'success' => true,
            'message' => 'Yorum silindi.',
        ]);
    }
}