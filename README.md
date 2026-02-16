# Test Repolla

Prototipo Laravel con Livewire, Alpine.js, Tailwind CSS y Vite.

## Stack

- **Laravel 12**
- **Livewire 3** (incluye Alpine.js)
- **Tailwind CSS**
- **Vite**
- **SQLite** (mydb.sqlite)

## Requisitos

- PHP 8.4+
- Composer
- Node.js & npm
- Extensión PHP SQLite3 (`sudo apt install php-sqlite3` en Debian)

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/mydb.sqlite
php artisan migrate
npm install && npm run build
```

## Desarrollo

```bash
php artisan serve
# En otra terminal:
npm run dev
```

## Licencia

MIT
