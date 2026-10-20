<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /** Bootstrap Icons class for each top-level category */
    private array $icons = [
        'Vehicles'            => 'bi-car-front-fill',
        'Real Estate'         => 'bi-building',
        'Electronics'         => 'bi-tv',
        'Home & Garden'       => 'bi-house-door',
        'Fashion & Kids'      => 'bi-bag',
        'Jobs'                => 'bi-briefcase',
        'Services'            => 'bi-tools',
        'Sports & Fitness'    => 'bi-bicycle',
        'Gaming & Toys'       => 'bi-controller',
        'Health & Beauty'     => 'bi-heart-pulse',
        'Education & Training' => 'bi-mortarboard',
    ];

    public function run(): void
    {
        $tree = [
            'Vehicles' => [
                'Cars' => ['Toyota', 'Hyundai', 'Kia'],
                'Motorcycles' => [],
                'Trucks' => [],
            ],
            'Real Estate' => [
                'Apartments for Sale' => [],
                'Apartments for Rent' => [],
                'Land' => [],
            ],
            'Electronics' => [
                'Mobile Phones' => ['iPhone', 'Samsung'],
                'Laptops' => [],
                'TVs' => [],
            ],
            'Home & Garden' => ['Furniture' => [], 'Kitchen' => [], 'Garden' => []],
            'Fashion & Kids' => ['Men Clothing' => [], 'Women Clothing' => [], 'Kids' => []],
            'Jobs' => ['Job Offers' => [], 'Job Seekers' => []],
            'Services' => ['Home Services' => [], 'Cleaning' => [], 'Repairs' => []],
            'Sports & Fitness' => ['Gym Equipment' => [], 'Bicycles' => []],
            'Gaming & Toys' => ['Consoles' => [], 'Video Games' => [], 'Toys' => []],
            'Health & Beauty' => ['Perfumes' => [], 'Skin Care' => []],
            'Education & Training' => ['Courses' => [], 'Private Lessons' => []],
        ];

        $this->createNodes($tree);
    }

    private function createNodes(array $nodes, ?Category $parent = null): void
    {
        $order = 0;

        foreach ($nodes as $key => $value) {
            $name     = is_array($value) ? $key : $value;
            $children = is_array($value) ? $value : [];

            $category = Category::updateOrCreate(
                ['slug' => Str::slug(($parent ? $parent->slug . '-' : '') . $name)],
                [
                    'name'       => $name,
                    'parent_id'  => $parent?->id,
                    'sort_order' => $order++,
                    'icon'       => $parent ? null : ($this->icons[$name] ?? null),
                ]
            );

            $this->createNodes($children, $category);
        }
    }
}
