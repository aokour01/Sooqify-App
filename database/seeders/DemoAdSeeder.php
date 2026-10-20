<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoAdSeeder extends Seeder
{
    private const ADS_PER_CATEGORY = 3;
    private const IMAGES_PER_AD = 4;

    /** category name => [titles, [min price, max price] or null, has condition] */
    private array $catalog = [
        'Toyota'              => [['Toyota Corolla 2018', 'Toyota Camry 2020', 'Toyota Yaris 2017'], [6000, 22000], true],
        'Hyundai'             => [['Hyundai Elantra 2019', 'Hyundai Tucson 2018', 'Hyundai Accent 2016'], [5000, 20000], true],
        'Kia'                 => [['Kia Sportage 2019', 'Kia Cerato 2018', 'Kia Picanto 2017'], [4500, 18000], true],
        'Motorcycles'         => [['Yamaha R6 2017', 'Honda CBR 2019', 'Vespa scooter 2020'], [1200, 9000], true],
        'Trucks'              => [['Isuzu pickup 2015', 'Mitsubishi L200 2018', 'Ford Transit van 2016'], [8000, 30000], true],
        'Apartments for Sale' => [['3-bedroom apartment, new building', '2-bedroom apartment near the park', 'Spacious penthouse with terrace'], [45000, 180000], false],
        'Apartments for Rent' => [['2-bedroom apartment, furnished', 'Studio near the university', 'Family apartment, 3 bedrooms'], [200, 700], false],
        'Land'                => [['Residential plot, 600 m2', 'Corner land with street access', 'Farm land with water well'], [20000, 150000], false],
        'iPhone'              => [['iPhone 13 128GB', 'iPhone 14 Pro 256GB', 'iPhone 12 64GB'], [250, 900], true],
        'Samsung'             => [['Samsung Galaxy S22', 'Samsung Galaxy A54', 'Samsung Galaxy S23 Ultra'], [180, 850], true],
        'Laptops'             => [['Dell Latitude i5 16GB', 'MacBook Air M1', 'HP Pavilion gaming laptop'], [250, 1100], true],
        'TVs'                 => [['Samsung 55 inch 4K Smart TV', 'LG 43 inch LED TV', 'Sony 65 inch OLED'], [180, 1200], true],
        'Furniture'           => [['Corner sofa, 6 seats', 'Solid wood dining table', 'Queen bed with mattress'], [60, 900], true],
        'Kitchen'             => [['Stainless steel fridge', 'Gas cooker, 5 burners', 'Dishwasher, almost new'], [80, 700], true],
        'Garden'              => [['Garden table and 4 chairs', 'Electric lawn mower', 'Outdoor swing seat'], [30, 400], true],
        'Men Clothing'        => [['Leather jacket, size L', 'Wool winter coat', 'Formal suit, size 50'], [15, 150], true],
        'Women Clothing'      => [['Evening dress, size M', 'Winter coat, size S', 'Handbag, genuine leather'], [15, 200], true],
        'Kids'                => [['Baby stroller', 'Kids bicycle, 16 inch', 'Wooden toy kitchen set'], [10, 150], true],
        'Job Offers'          => [['Sales representative wanted', 'Part-time accountant needed', 'Delivery drivers wanted'], null, false],
        'Job Seekers'         => [['Graphic designer looking for work', 'Experienced driver available', 'Fresh graduate seeking an office job'], null, false],
        'Home Services'       => [['Plumbing and water heater repair', 'Electrician for homes and shops', 'Furniture moving service'], [10, 80], false],
        'Cleaning'            => [['Apartment deep cleaning', 'Office cleaning service', 'Carpet and sofa cleaning'], [15, 90], false],
        'Repairs'             => [['Phone screen repair', 'Washing machine repair', 'Laptop repair and upgrades'], [10, 70], false],
        'Gym Equipment'       => [['Treadmill, foldable', 'Adjustable dumbbell set', 'Multi-gym station'], [50, 700], true],
        'Bicycles'            => [['Mountain bike 27.5 inch', 'City bike with basket', 'Road bike, carbon frame'], [60, 600], true],
        'Consoles'            => [['PlayStation 5 with 2 controllers', 'Xbox Series X', 'Nintendo Switch OLED'], [180, 550], true],
        'Video Games'         => [['FIFA game for PS5', 'Zelda for Switch', 'Collection of 10 PS4 games'], [10, 60], true],
        'Toys'                => [['Lego city set', 'Remote control car', 'Board games bundle'], [8, 80], true],
        'Perfumes'            => [['Original perfume 100ml', 'Oud perfume set', 'Gift pack for men'], [15, 150], true],
        'Skin Care'           => [['Skin care set', 'Hair dryer, professional', 'Beard trimmer kit'], [10, 90], true],
        'Courses'             => [['English IELTS preparation course', 'Programming course for beginners', 'Graphic design workshop'], [30, 300], false],
        'Private Lessons'     => [['Math lessons for high school', 'Piano lessons at home', 'Arabic and English tutoring'], [8, 40], false],
    ];

    private array $descriptions = [
        'In very good condition, well maintained and ready to use. Serious buyers only. Contact me for more details or to arrange a viewing.',
        'Great deal at a fair price. Genuine listing with no hidden problems. Available to see any day this week, and the price is slightly negotiable.',
        'Selling because I no longer need it. Everything works perfectly and it has been looked after. Message me with any questions.',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Refusing to seed demo data in production.');
            return;
        }

        $cityIds    = City::pluck('id');
        $categories = Category::where('is_active', true)->whereDoesntHave('children')->get();

        if ($cityIds->isEmpty() || $categories->isEmpty()) {
            $this->command->error('Run the city and category seeders first (php artisan db:seed).');
            return;
        }

        $users = $this->demoUsers();
        $this->clearPreviousDemoAds($users->pluck('id'));
        $sources = $this->sourceImages();
        $this->command->info(count($sources) . ' source image(s) found.');

        foreach ($categories as $category) {
            [$titles, $range, $hasCondition] = $this->catalog[$category->name] ?? [
                [$category->name . ' in good shape', 'Quality ' . $category->name, $category->name . ', great deal'],
                [20, 500],
                true,
            ];

            for ($i = 0; $i < self::ADS_PER_CATEGORY; $i++) {
                $title       = $titles[$i % count($titles)];
                $publishedAt = now()->subDays(rand(0, 20))->subMinutes(rand(0, 1400));
                $price       = $range ? (int) (round(rand($range[0], $range[1]) / 5) * 5) : null;

                $ad = Ad::forceCreate([
                    'user_id'       => $users->random()->id,
                    'category_id'   => $category->id,
                    'city_id'       => $cityIds->random(),
                    'title'         => $title,
                    'slug'          => Str::slug($title) . '-' . Str::lower(Str::random(6)),
                    'description'   => $this->descriptions[array_rand($this->descriptions)],
                    'price'         => $price,
                    'is_negotiable' => $price ? (bool) rand(0, 1) : false,
                    'condition'     => $hasCondition ? ['new', 'used'][rand(0, 1)] : null,
                    'contact_phone' => '07' . random_int(10000000, 99999999),
                    'status'        => Ad::STATUS_ACTIVE,
                    'views'         => rand(5, 400),
                    'published_at'  => $publishedAt,
                    'expires_at'    => $publishedAt->copy()->addDays(30),
                    'created_at'    => $publishedAt,
                    'updated_at'    => $publishedAt,
                ]);

                $picks = $sources ? $this->pickSources($sources, self::IMAGES_PER_AD) : [];

                for ($n = 0; $n < self::IMAGES_PER_AD; $n++) {
                    $path = $picks
                        ? $this->copyImage($picks[$n])
                        : $this->makePlaceholder($category->name, crc32($category->name) + $n);

                    if ($path) {
                        $ad->images()->create(['path' => $path, 'sort_order' => $n]);
                    }
                }
            }
        }

        $this->command->info('Demo ads created. Demo logins: demo1@sooqify.test ... demo5@sooqify.test (password: password)');
    }

    private function demoUsers()
    {
        $names = ['Omar Haddad', 'Lina Saleh', 'Khaled Nasser', 'Sara Mansour', 'Yousef Aziz'];

        return collect($names)->map(function ($name, $i) {
            $user = User::firstOrCreate(
                ['email' => 'demo' . ($i + 1) . '@sooqify.test'],
                ['name' => $name, 'password' => bcrypt('password')]
            );

            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            return $user;
        });
    }

    /** Re-running the seeder replaces the earlier demo ads instead of duplicating them. */
    private function clearPreviousDemoAds($userIds): void
    {
        $adIds = Ad::withTrashed()->whereIn('user_id', $userIds)->pluck('id');

        if ($adIds->isEmpty()) {
            return;
        }

        $files = AdImage::whereIn('ad_id', $adIds)->get()
            ->flatMap(fn ($image) => [$image->path, $image->thumb_path])
            ->filter()
            ->all();

        Storage::disk('public')->delete($files);

        Ad::withTrashed()->whereIn('id', $adIds)->forceDelete();
    }
    /** Real pictures to reuse: storage/app/demo-images first, then images already uploaded to storage/app/public/ads */
    private function sourceImages(): array
    {
        $folders = [storage_path('app/demo-images'), storage_path('app/public/ads')];
        $files = [];

        foreach ($folders as $folder) {
            foreach (glob($folder . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [] as $file) {
                if (! str_starts_with(basename($file), 'demo-')) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
    /** Picks different pictures for one ad, and only repeats them if there are fewer sources than needed */
    private function pickSources(array $sources, int $count): array
    {
        shuffle($sources);

        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = $sources[$i % count($sources)];
        }

        return $picked;
    }

    /** Each demo ad gets its own copy, so removing demo data never touches your originals */
    private function copyImage(string $source): string
    {
        $path = 'ads/demo-' . Str::uuid() . '.' . strtolower(pathinfo($source, PATHINFO_EXTENSION));
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /** Draws a colored 800x600 JPEG with the category name on it, using PHP's GD extension. */
    private function makePlaceholder(string $label, int $colorIndex): ?string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagejpeg')) {
            return null;
        }

        $palette = [[52, 152, 219], [46, 204, 113], [155, 89, 182], [241, 196, 15],
            [230, 126, 34], [231, 76, 60], [26, 188, 156], [52, 73, 94]];
        [$r, $g, $b] = $palette[$colorIndex % count($palette)];

        // Draw small, then enlarge, because GD's built-in font is tiny
        $small = imagecreatetruecolor(200, 150);
        imagefilledrectangle($small, 0, 0, 200, 150, imagecolorallocate($small, $r, $g, $b));

        $label = Str::limit($label, 22, '');
        $x = max(0, (int) ((200 - imagefontwidth(5) * strlen($label)) / 2));
        $y = (int) ((150 - imagefontheight(5)) / 2);
        imagestring($small, 5, $x, $y, $label, imagecolorallocate($small, 255, 255, 255));

        $big = imagecreatetruecolor(800, 600);
        imagecopyresampled($big, $small, 0, 0, 0, 0, 800, 600, 200, 150);

        ob_start();
        imagejpeg($big, null, 80);
        $data = ob_get_clean();

        $path = 'ads/demo-' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $data);

        return $path;
    }
}
