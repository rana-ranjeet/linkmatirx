<?php

namespace Database\Seeders;

use App\Models\WebsiteCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Arts & Entertainment',
            'Business',
            'Education',
            'Finance',
            'Health & Wellness',
            'Lifestyle',
            'Technology',
            'Travel',
        ] as $name) {
            WebsiteCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
