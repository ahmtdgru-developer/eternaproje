<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/Laravel-12-red" alt="Laravel Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/Lisans-MIT-green" alt="License"></a>
</p>

---

## Proje Hakkında

Bu proje, **Laravel 12** kullanılarak geliştirilmiş bir **Blog Yönetim Sistemi**dir.

Gerçek hayat senaryolarına uygun olacak şekilde **temiz mimari (Clean Architecture)** prensipleri uygulanmıştır.

---

## Özellikler

* Kullanıcı kayıt / giriş / çıkış (Register / Login / Logout)
* Rol bazlı yetkilendirme (admin, yazar, kullanıcı)
* Blog yazısı yönetimi (CRUD)
* Kategori yönetimi
* Yorum sistemi (onay mekanizmalı)
* Öne çıkan yazılar algoritması
* Bildirim sistemi (mail, database, broadcast)
* Activity log (model değişiklik takibi)
* Dosya yükleme (Media Library)

---

## Kullanılan Teknolojiler

* Laravel 12
* Laravel Sanctum (API Authentication)
* MySQL
* Spatie Media Library
* Spatie Activity Log
* Laravel Reverb (Broadcast)
* Vue 3 + TailwindCSS (opsiyonel frontend)

---

## Kurulum

### 1. Projeyi Klonla

```bash
git clone https://github.com/ahmtdgru-developer/blog-system.git
cd blog-system
```

---

### 2. Bağımlılıkları Yükle

```bash
composer install
```

---

### 3. Ortam Dosyasını Ayarla

```bash
cp .env.example .env
```

`.env` dosyasını düzenle:

```env
DB_DATABASE=blog_system
DB_USERNAME=root
DB_PASSWORD=
```

---

### 4. Uygulama Anahtarı Oluştur

```bash
php artisan key:generate
```

---

### 5. Migration Çalıştır

```bash
php artisan migrate
```

---

### 6. Uygulamayı Başlat

```bash
php artisan serve
```

Uygulama şu adreste çalışır:

```
http://127.0.0.1:8000
```

---

## API Kimlik Doğrulama

Bu projede **Laravel Sanctum** kullanılmaktadır.

### Kayıt Ol

```
POST /api/register
```

### Giriş Yap

```
POST /api/login
```

### Çıkış Yap

```
POST /api/logout
```

Korunan endpoint’ler için header:

```
Authorization: Bearer {token}
```

---

## Proje Yapısı

```
app/
 ├── Http/Controllers/API
 ├── Services
 ├── Models
 ├── Policies
```

* Controller → request yönetimi
* Service → business logic
* Model → veri katmanı

---

## Notlar

* `.env` dosyası güvenlik nedeniyle repoya eklenmemiştir
* MySQL kullanılması önerilir
* Projede clean architecture yaklaşımı uygulanmıştır

---

## Lisans

Bu proje MIT lisansı ile açık kaynak olarak sunulmaktadır.
