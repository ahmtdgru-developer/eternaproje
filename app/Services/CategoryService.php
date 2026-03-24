<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function list(): Collection
    {
        return Category::query()
            ->latest()
            ->get();
    }

    public function create(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name']),
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $payload = [
            'name' => $data['name'],
        ];

        if ($category->name !== $data['name']) {
            $payload['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        $category->update($payload);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $query = Category::query();

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        while ((clone $query)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}