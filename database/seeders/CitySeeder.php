<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Amman',
            'Zarqa',
            'Irbid',
            'Aqaba',
            'Salt',
            'Madaba',
            'Jerash',
            'Ajloun',
            'Karak',
            'Mafraq',
            'Tafilah',
            'Maan',
        ];

        foreach ($cities as $name) {
            City::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
