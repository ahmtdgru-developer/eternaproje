<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Ahmet',
            'surname' => 'Doğru',
            'phone' => '5551112233',
            'email' => 'admin@eterna.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $writers = collect([
            [
                'name' => 'Elif',
                'surname' => 'Yılmaz',
                'phone' => '5551112234',
                'email' => 'writer1@eterna.test',
            ],
            [
                'name' => 'Mert',
                'surname' => 'Kaya',
                'phone' => '5551112235',
                'email' => 'writer2@eterna.test',
            ],
        ])->map(fn (array $writer) => User::query()->create([
            ...$writer,
            'password' => Hash::make('password'),
            'role' => User::ROLE_WRITER,
        ]));

        $users = collect([
            [
                'name' => 'Zeynep',
                'surname' => 'Demir',
                'phone' => '5551112236',
                'email' => 'user1@eterna.test',
            ],
            [
                'name' => 'Can',
                'surname' => 'Aydın',
                'phone' => '5551112237',
                'email' => 'user2@eterna.test',
            ],
            [
                'name' => 'Selin',
                'surname' => 'Koç',
                'phone' => '5551112238',
                'email' => 'user3@eterna.test',
            ],
        ])->map(fn (array $user) => User::query()->create([
            ...$user,
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
        ]));

        $categories = collect([
            'Laravel',
            'Vue',
            'PHP',
            'Frontend',
            'Backend',
            'Yazılım',
        ])->map(function (string $name) {
            return Category::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        });

        $posts = $this->seedPosts($writers, $categories);
        $this->seedComments($posts, $admin, $writers, $users);
    }

    private function seedPosts(Collection $writers, Collection $categories): Collection
    {
        return collect([
            [
                'key' => 'hot_recent',
                'user' => $writers[0],
                'title' => 'Laravel 12 ile API Geliştirme Rehberi',
                'content' => 'Laravel 12 ile temiz bir API geliştirmek için route, request, service ve policy katmanlarını birlikte kullanmak büyük kolaylık sağlar.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subHours(8),
                'categories' => ['Laravel', 'Backend', 'PHP'],
            ],
            [
                'key' => 'mid_recent',
                'user' => $writers[0],
                'title' => 'Vue 3 Composition API ile Form Yönetimi',
                'content' => 'Composition API ile form state yönetimi, reusable composable yazımı ve validasyon süreçleri çok daha okunabilir hale gelir.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(2),
                'categories' => ['Vue', 'Frontend'],
            ],
            [
                'key' => 'draft_one',
                'user' => $writers[0],
                'title' => 'Henüz Yayına Alınmamış Yazı Taslağı',
                'content' => 'Bu içerik bir taslaktır ve yazar panelinde görünür, public tarafta görünmez.',
                'status' => Post::STATUS_DRAFT,
                'published_at' => null,
                'categories' => ['Yazılım'],
            ],
            [
                'key' => 'old_popular',
                'user' => $writers[1],
                'title' => 'Backend Servis Katmanı Neden Önemlidir',
                'content' => 'Controller içinde büyüyen iş kurallarını servis katmanına taşımak, okunabilirlik ve test edilebilirlik açısından önemli avantaj sağlar.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(9),
                'categories' => ['Backend', 'Yazılım'],
            ],
            [
                'key' => 'new_with_some_comments',
                'user' => $writers[1],
                'title' => 'Vue Router ile Yetki Bazlı Sayfa Yönetimi',
                'content' => 'Route guard kullanımı sayesinde kullanıcıyı rolüne göre doğru sayfalara yönlendirmek çok daha güvenli bir arayüz sağlar.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDay(),
                'categories' => ['Vue', 'Frontend'],
            ],
            [
                'key' => 'draft_two',
                'user' => $writers[1],
                'title' => 'Yazar Paneli İçin Yeni İçerik Fikirleri',
                'content' => 'Bu içerik de taslak durumundadır ve düzenleme akışlarını test etmek için hazırlanmıştır.',
                'status' => Post::STATUS_DRAFT,
                'published_at' => null,
                'categories' => ['Laravel'],
            ],
            [
                'key' => 'fresh_but_quiet',
                'user' => $writers[0],
                'title' => 'Yeni Yayınlanan Ama Hâlâ Sessiz Kalan Yazı',
                'content' => 'Bu yazı çok yeni yayımlandı fakat henüz yeterli etkileşim almadı.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subHours(4),
                'categories' => ['Laravel', 'Yazılım'],
            ],
            [
                'key' => 'week_old_active',
                'user' => $writers[1],
                'title' => 'Bir Haftalık Yazıda Etkileşim Nasıl Ölçülür',
                'content' => 'Skorlama algoritmaları yorum ve yayın tarihi gibi sinyalleri birlikte değerlendirir.',
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(6),
                'categories' => ['Backend', 'PHP'],
            ],
        ])->mapWithKeys(function (array $data) use ($categories) {
            $post = Post::query()->create([
                'user_id' => $data['user']->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'slug' => Str::slug($data['title']),
                'published_at' => $data['published_at'],
                'status' => $data['status'],
            ]);

            $post->categories()->sync(
                $categories
                    ->whereIn('name', $data['categories'])
                    ->pluck('id')
                    ->all()
            );

            return [$data['key'] => $post];
        });
    }

    private function seedComments(Collection $posts, User $admin, Collection $writers, Collection $users): void
    {
        $comments = [
            [
                'post' => $posts['hot_recent'],
                'user' => $users[0],
                'content' => 'Eline sağlık, servis ve policy ayrımı çok net anlatılmış.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'post' => $posts['hot_recent'],
                'user' => $users[1],
                'content' => 'Bu yapıyı örnek projeme uygulamayı düşünüyorum.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'post' => $posts['hot_recent'],
                'user' => $users[2],
                'content' => 'Route ve request katmanını biraz daha açarsan çok iyi olur.',
                'is_approved' => false,
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'post' => $posts['hot_recent'],
                'user' => $admin,
                'content' => 'Bu yazı featured hesaplama için güçlü aday olacak gibi duruyor.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'post' => $posts['mid_recent'],
                'user' => $admin,
                'content' => 'Composition API için temiz bir giriş olmuş.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'post' => $posts['mid_recent'],
                'user' => $users[0],
                'content' => 'Özellikle form yapısı kısmı çok işime yaradı.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(2)->addHours(4),
            ],
            [
                'post' => $posts['old_popular'],
                'user' => $writers[0],
                'content' => 'Servis katmanı büyük projelerde gerçekten çok fark yaratıyor.',
                'is_approved' => false,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'post' => $posts['old_popular'],
                'user' => $users[0],
                'content' => 'Bu konuya dair daha fazla örnek görmek isterim.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'post' => $posts['old_popular'],
                'user' => $users[1],
                'content' => 'Eski ama hâlâ çok değerli bir içerik olmuş.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(7)->subHours(5),
            ],
            [
                'post' => $posts['old_popular'],
                'user' => $users[2],
                'content' => 'Benzer bir yapıyı iş yerinde kullanıyoruz.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(6)->subHours(6),
            ],
            [
                'post' => $posts['old_popular'],
                'user' => $admin,
                'content' => 'Genel toplam yorumu yüksek tutmak için iyi bir aday.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(5)->subHours(10),
            ],
            [
                'post' => $posts['new_with_some_comments'],
                'user' => $users[0],
                'content' => 'Route guard tarafı da örnekle anlatılırsa güzel olur.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subHours(10),
            ],
            [
                'post' => $posts['new_with_some_comments'],
                'user' => $users[1],
                'content' => 'Bu yazı özellikle dashboard akışı için çok işime yaradı.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subHours(14),
            ],
            [
                'post' => $posts['fresh_but_quiet'],
                'user' => $users[2],
                'content' => 'Yazı yeni ama yorum sayısı şimdilik düşük.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subHours(1),
            ],
            [
                'post' => $posts['week_old_active'],
                'user' => $users[0],
                'content' => 'Bir haftalık içeriklerde yaş katsayısı gerçekten fark yaratıyor.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'post' => $posts['week_old_active'],
                'user' => $users[1],
                'content' => 'Skor hesabında yorum zamanlaması da çok önemli.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'post' => $posts['week_old_active'],
                'user' => $users[2],
                'content' => 'Bu yazı featured listede orta sıralarda olabilir.',
                'is_approved' => true,
                'created_at' => Carbon::now()->subDays(4),
            ],
        ];

        foreach ($comments as $data) {
            Comment::unguarded(function () use ($data) {
                Comment::query()->create([
                    'post_id' => $data['post']->id,
                    'user_id' => $data['user']->id,
                    'content' => $data['content'],
                    'is_approved' => $data['is_approved'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ]);
            });
        }
    }
}