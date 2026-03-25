# Eterna Blog

Laravel 12 ve Vue 3 Composition API kullanılarak geliştirilen blog yönetim sistemi.

Bu proje Laravel içine gömülü Vue yapısı ile çalışır. Inertia kullanılmaz. Frontend, API endpoint'leri ile doğrudan haberleşir.

## Kullanılan Teknolojiler

### Backend
- Laravel 12
- Laravel Sanctum
- Laravel Reverb
- Spatie Media Library
- Spatie Activity Log
- MySQL

### Frontend
- Vue 3 Composition API
- Vue Router
- Tailwind CSS
- vue-yup-form
- vue-select
- vue-the-mask
- Laravel Echo
- Axios

## Mevcut Özellikler

- Kullanıcı kayıt, giriş ve çıkış işlemleri
- E-posta veya telefon ile giriş
- Rol yapısı: `admin`, `writer`, `user`
- Policy tabanlı yetkilendirme
- Blog yazısı oluşturma, listeleme, güncelleme ve silme
- Kapak görseli yükleme
- Kategori oluşturma, listeleme, güncelleme ve silme
- Yorum ekleme
- Admin için yorum onaylama ve silme
- Öne çıkan yazılar algoritması ve API endpoint'i
- Activity log entegrasyonu
- Reverb üzerinden canlı yorum onay akışı
- Yorum onaylandığında yazara `mail`, `database` ve `broadcast` notification gönderimi
- Bir haftadır yorum almayan yayınlanmış yazıların otomatik silinmesi
- Dashboard ekranı
- Yazı detay sayfası
- Seed data

## Roller ve Davranışlar

### admin
- Tüm yazıları yönetebilir
- Kategorileri yönetebilir
- Tüm yorumları görebilir, onaylayabilir ve silebilir
- Yorum yazdığında yorum direkt onaylı kaydolur

### writer
- Kendi yazılarını oluşturabilir, düzenleyebilir ve silebilir
- Yayındaki bir yazısını düzenlerse yazı tekrar taslak durumuna alınır
- Yorum yazabilir

### user
- Yayındaki yazıları görebilir
- Yorum yazabilir
- Kendi onay bekleyen yorumunu silebilir

## Kurulum

### 1. Depoyu klonla

```bash
git clone <repo-url>
cd eternaproje
```

### 2. PHP bağımlılıklarını yükle

```bash
composer install
```

### 3. JavaScript bağımlılıklarını yükle

```bash
npm install
```

### 4. Ortam dosyasını hazırla

```bash
copy .env.example .env
```

`.env` içinde veritabanı ayarlarını güncelle.

### 5. Uygulama anahtarını oluştur

```bash
php artisan key:generate
```

### 6. Migration ve seed işlemlerini çalıştır

```bash
php artisan migrate:fresh --seed
```

### 7. Storage link oluştur

```bash
php artisan storage:link
```

### 8. Geliştirme ortamını başlat

Bir terminalde:

```bash
php artisan serve
```

İkinci terminalde:

```bash
npm run dev
```

Üçüncü terminalde:

```bash
php artisan reverb:start
```

Uygulamayı tarayıcıda şu adresten aç:

- `http://127.0.0.1:8000`

`npm run dev` komutu Vite geliştirme sunucusunu başlatır. Bu sunucu genelde `5173` portunu kullanır, port doluysa farklı bir port açabilir. Bu projede kullanıcı arayüzüne doğrudan Vite portundan değil, Laravel uygulamasının çalıştığı `8000` portundan girilir.

## Seed Hesapları

Tüm seed kullanıcılarının şifresi:

```text
password
```

Hazır hesaplar:

- `admin@eterna.test`
- `writer1@eterna.test`
- `writer2@eterna.test`
- `user1@eterna.test`
- `user2@eterna.test`
- `user3@eterna.test`

## API Endpoint Özeti

### Auth
- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

### Posts
- `GET /api/posts`
- `GET /api/featured-posts`
- `GET /api/posts/{post}`
- `GET /api/my-posts`
- `GET /api/my-posts/{post}`
- `POST /api/posts`
- `PUT /api/posts/{post}`
- `PATCH /api/posts/{post}`
- `DELETE /api/posts/{post}`

### Categories
- `GET /api/categories`
- `POST /api/categories`
- `PUT /api/categories/{category}`
- `PATCH /api/categories/{category}`
- `DELETE /api/categories/{category}`

### Comments
- `GET /api/comments`
- `GET /api/comments/pending`
- `PATCH /api/comments/{comment}/approve`
- `DELETE /api/comments/{comment}`
- `GET /api/posts/{post}/comments`
- `POST /api/posts/{post}/comments`

## Frontend Sayfaları

- `/login`
- `/register`
- `/`
- `/posts/:post`
- `/my-posts`
- `/my-posts/create`
- `/my-posts/:post/edit`
- `/categories`
- `/categories/create`
- `/categories/:category/edit`
- `/comments`

## Planlı Komutlar

### Pasif yazıları silme komutu

Manuel çalıştırmak için:

```bash
php artisan posts:delete-inactive
```

Bu komut şu kuralla çalışır:
- Yayında olan yazıları kontrol eder
- Son 7 gün içinde hiç yorum almayan yazıları soft delete eder
- Hiç yorumu yoksa ve yayın tarihi 7 günden eskiyse yine soft delete eder

Ayrıca günlük olarak scheduler üzerinden çalışacak şekilde tanımlanmıştır.

## Notlar

- Kapak görselleri Spatie Media Library ile yönetilir.
- API isteklerinde Sanctum bearer token kullanılır.
- Frontend tarafında auth kontrolü route guard ile yapılır.
- Activity log tarafında sadece değişen alanlar kaydedilir.
- Öne çıkan yazılar skoru anlık olarak veritabanı sorgusunda hesaplanır.
- Yorum onaylandığında ilgili yazı detay ekranına Reverb üzerinden canlı olarak düşer.
- Mail bildirimi lokal ortamda `MAIL_MAILER=log` olduğu için gerçek SMTP yerine `storage/logs/laravel.log` içine yazılır.