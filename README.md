# Sooqify

A classifieds marketplace inspired by OpenSooq, built with **Laravel** and **Bootstrap 5**.
Users post ads in a category tree, visitors browse and search them, and moderators approve every ad before it goes public.

Built as a training project. As requested in the brief, it has **no payment gateway and no AI features**.

## Features

**Visitors**
- Home page with search, category tiles, and the latest ads
- Ads listing with keyword search, city filter, price range, pagination, and a clear-filters button
- Unlimited category depth (for example Vehicles > Cars > Toyota), with a breadcrumb and subcategory pills; choosing a parent category also shows its children's ads
- Ad page with image gallery, similar ads, view counter, and a "Show phone number" button (login required, rate limited, so the number is not in the page source)

**Registered users**
- Register, log in, and verify their email address
- Post ads with up to 8 images (max 2 MB each, JPG/PNG/WebP)
- **My ads**: edit, mark as sold, delete, and renew ads, and see rejection reasons
- Favorites, and reporting ads to the moderators

**Moderators and admins**
- Moderation queue: approve an ad, or reject it with a reason
- Reports page: dismiss a report, or remove the reported ad
- Roles and permissions managed with `spatie/laravel-permission`

## Ad lifecycle

```
pending --approve--> active --owner--> sold
   ^        |           |
   |     reject      30 days pass
   |        v           v
   +-- rejected      expired --renew--> active
```

- New ads start as `pending` and are invisible to the public until approved.
- Editing an `active` or `rejected` ad sends it back to `pending`.
- Ads expire 30 days after approval. The `ads:expire` command marks them as `expired`, and expired ads are hidden even before the command runs.
- Owners can renew an expired ad, or an active ad during its last 7 days.

## Roles

| Role | Can do |
|------|--------|
| `user` | Post and manage their own ads, favorites, reports |
| `moderator` | Everything above, plus approve and reject ads and review reports |
| `admin` | Everything above, plus delete any ad |

The permissions `users.manage` and `categories.manage` are seeded for the admin, but there is no screen for them yet.

## Tech stack

- PHP 8.3 or newer, Laravel
- MySQL
- Bootstrap 5 and Bootstrap Icons (`laravel/ui` scaffolding), built with Vite
- `spatie/laravel-permission`

## Setup

```bash
git clone <repository-url> sooqify
cd sooqify

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`. Also set:

```
APP_NAME=Sooqify
APP_URL=http://127.0.0.1:8000
```

`APP_URL` must match the address you open in the browser, or email verification links fail with "Invalid signature".

```bash
php artisan migrate --seed
php artisan storage:link

npm run dev          # keep running (or use "npm run build" once)
php artisan serve    # in a second terminal
```

`migrate --seed` creates the roles and permissions, the cities, the category tree, and the admin account.

### Accounts

| Account | Password | Role |
|---------|----------|------|
| `admin@sooqify.test` | `password` | admin |

**Change this password outside local development.**

### Demo data (optional)

```bash
php artisan db:seed --class=DemoAdSeeder
```

This creates 3 active ads in every category, owned by 5 demo accounts (`demo1@sooqify.test` to `demo5@sooqify.test`, password `password`), with 4 pictures per ad. Pictures are taken from `storage/app/demo-images/` (put some `.jpg` or `.png` files there), or from images already uploaded to `storage/app/public/ads`. Running the seeder again replaces the previous demo ads. It refuses to run in production.

To remove the demo data:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'like', 'demo%@sooqify.test')->delete();
```

```bash
rm storage/app/public/ads/demo-*
```

### Email (verification)

Posting an ad requires a verified email address. For local development, either set:

```
MAIL_MAILER=log
```

and copy the verification link from `storage/logs/laravel.log`, or run [Mailpit](https://mailpit.axllent.org/) and use its SMTP settings (host `127.0.0.1`, port `1025`). In production, use your company's real mail service.

### Scheduler (ad expiry)

During development:

```bash
php artisan schedule:work
```

On a server, add one cron entry:

```
* * * * * cd /path/to/sooqify && php artisan schedule:run >> /dev/null 2>&1
```

You can also run the expiry manually with `php artisan ads:expire`.

## Tests

```bash
php artisan test
```

The tests use an in-memory SQLite database by default (see `phpunit.xml`). Never point them at your development database, because they wipe it.

## Project structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/AdController.php` | Public listing, ad page, posting, phone reveal |
| `app/Http/Controllers/MyAdController.php` | Owner's ads: edit, sold, delete, renew |
| `app/Http/Controllers/Admin/` | Moderation queue and reports review |
| `app/Policies/AdPolicy.php` | Who can edit or delete an ad |
| `app/Console/Commands/ExpireAds.php` | Scheduled expiry (`ads:expire`) |
| `database/seeders/` | Roles, cities, categories, and demo ads |
| `resources/views/` | Blade views (Bootstrap 5) |

## Known limitations and ideas

- Cities and categories are managed by seeders; there is no admin screen for them yet
- Uploaded images are stored as they are, with no resizing or thumbnails
- The phone number belongs to each ad; users have no phone field on their profile
- No messaging between buyers and sellers
- English only; prices are shown in JOD
