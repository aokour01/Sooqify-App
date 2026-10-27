# Sooqify

A classifieds website inspired by OpenSooq, built with Laravel and Bootstrap 5. People post ads in a category tree, visitors browse and search them, and a moderator approves every ad before it goes public. There is no payment gateway and no AI.

## Main features

- Search and filter ads by text, category, city and price, with a category tree and ad counts
- Unlimited category depth (for example Vehicles > Cars > Toyota)
- Registered users (email verification required) post ads with images, edit, renew, mark as sold, save favorites, report ads and receive notifications
- Every new or edited ad waits for approval. Approved ads stay online for 30 days and then expire
- The phone number is hidden until a logged-in user clicks "Show phone number"
- Moderators approve or reject ads and review reports
- The admin also manages users (roles, blocking), all ads and the categories
- A dashboard for users and one for staff

## Requirements

PHP 8.3 or newer, Composer, Node.js with npm, and MySQL.

## Setup

```bash
git clone <repository-url> sooqify
cd sooqify

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database and set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`. Also set:

```
APP_NAME=Sooqify
APP_URL=http://127.0.0.1:8000
```

`APP_URL` must match the address in your browser, or email verification links will fail.

```bash
php artisan migrate --seed
php artisan storage:link

npm run dev          # keep it running
php artisan serve    # in a second terminal
```

## Admin account

| Email | Password |
|-------|----------|
| `admin@sooqify.test` | `password` |

Change this password anywhere except on your own computer.

## Email verification

Users must verify their email before posting. For local work, set `MAIL_MAILER=log` in `.env` and copy the verification link from `storage/logs/laravel.log`.

## Ad expiry

Ads expire automatically through the Laravel scheduler. During development run `php artisan schedule:work`. On a server, add this cron entry:

```
* * * * * cd /path/to/sooqify && php artisan schedule:run >> /dev/null 2>&1
```

## Demo data (optional)

```bash
php artisan db:seed --class=DemoAdSeeder
```

It creates sample ads in every category, owned by demo accounts `demo1@sooqify.test` to `demo5@sooqify.test` (password `password`). Put some `.jpg` or `.png` files in `storage/app/demo-images/` first if you want real pictures. It refuses to run in production.

## More details

See `SOOQIFY_DOCUMENTATION.md` for how the project works: the database, every model, route and controller, the workflows, and a guide to how everything connects.
