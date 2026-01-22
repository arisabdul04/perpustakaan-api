# Laravel Project Perpustakaan API

Project test interview HWGroup

## 🚀 Tech Stack

* PHP >= 8.x
* Laravel >= 12.x
* PostgreSQL
* Composer

## 📦 Installation

Ikuti langkah-langkah berikut untuk menjalankan project secara lokal:

### 1️⃣ Clone Repository

```bash
git clone https://github.com/arisabdul04/perpustakaan-api.git
cd perpustakaan-api
```

### 2️⃣ Install Dependency

```bash
composer install
```

### 3️⃣ Copy File Environment

```bash
cp .env.example .env
```

### 4️⃣ Konfigurasi Database

Sesuaikan konfigurasi database pada file `.env`:

```env
DB_CONNECTION=postgresql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=perpustakaan
DB_USERNAME=postgres
DB_PASSWORD=
```

### 5️⃣ Generate Application Key

```bash
php artisan key:generate
```

### 6️⃣ Migrasi Database

```bash
php artisan migrate
```

Jika tersedia seeder:

```bash
php artisan db:seed
```

### 7️⃣ Jalankan Server

```bash
php artisan serve
```

Aplikasi akan berjalan di:

```
http://127.0.0.1:8000
```

## 📚 API Documentation

Gunakan Postman / Insomnia untuk mengakses endpoint API collection di file README.md Perpustakaan.postman_collection.json

## 🧪 Testing (Opsional)

```bash
php artisan test
```
