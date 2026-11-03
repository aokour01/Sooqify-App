# Sooqify

A classifieds website (like OpenSooq) built with Laravel and Bootstrap. Users post ads, a moderator approves them, and visitors browse and search by category, city and price.

## Features

- Category tree, search and filters
- Post, edit, renew and delete ads with images
- Every ad is reviewed by a moderator before it goes public
- Favorites, reports, and a dashboard for users and for staff
- Live notifications (Pusher) and emails to staff (Mailtrap)
- Scheduled clean-up of expired and unapproved ads

## Requirements

PHP 8.3+, Composer, Node.js + npm, MySQL

## Setup

```bash
git clone <https://github.com/aokour01/sooqify> sooqify
cd sooqify

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and set the database, `APP_URL`, your Mailtrap mail settings and your Pusher keys. Then:

```bash
php artisan migrate --seed
php artisan storage:link
```

## Run (one terminal each)

```bash
php artisan serve
npm run dev
php artisan queue:work
php artisan schedule:work
```

## Admin login

`admin@sooqify.test` / `password` (change it outside your own computer)
