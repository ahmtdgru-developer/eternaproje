<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($this->categoryService->list()),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($this->categoryService->create($request->validated())),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($this->categoryService->update($category, $request->validated())),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return response()->json([
            'success' => true,
            'message' => 'Kategori silindi.',
        ]);
    }
}