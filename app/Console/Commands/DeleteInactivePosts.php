<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteInactivePosts extends Command
{
    protected $signature = 'posts:delete-inactive';

    protected $description = 'Bir haftadır hiç yorum almayan yayındaki yazıları siler';

    public function handle(): int
    {
        $threshold = Carbon::now()->subDays(7);

        $query = Post::query()
            ->where('status', Post::STATUS_PUBLISHED)
            ->where(function ($query) use ($threshold) {
                $query
                    ->whereDoesntHave('comments', function ($commentQuery) use ($threshold) {
                        $commentQuery->where('created_at', '>', $threshold);
                    })
                    ->where(function ($postQuery) use ($threshold) {
                        $postQuery
                            ->where('published_at', '<=', $threshold)
                            ->orWhereHas('comments', function ($commentQuery) use ($threshold) {
                                $commentQuery->where('created_at', '<=', $threshold);
                            });
                    });
            });

        if (! $query->exists()) {
            $this->info('Silinecek pasif yazı bulunamadı.');

            return self::SUCCESS;
        }

        $deletedCount = 0;

        $query
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$deletedCount) {
                foreach ($posts as $post) {
                    $post->delete();
                    $deletedCount++;
                }
            });

        $this->info("{$deletedCount} adet pasif yazı silindi.");

        return self::SUCCESS;
    }
}