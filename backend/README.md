# Backend Laravel API

Struktur ini adalah scaffold awal untuk Laravel API sistem informasi sekolah.

## Modul MVP

- Authentication
- Dashboard
- Students
- Parents
- Teachers
- Classes
- Academic Years
- Subjects
- Attendances
- Grades
- Report Cards

## Setup saat dependency tersedia

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
