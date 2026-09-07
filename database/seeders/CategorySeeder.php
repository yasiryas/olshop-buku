<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\CopiesImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use CopiesImages;

    public function run(): void
    {
        $categories = [
            ['name' => 'Novel', 'icon' => 'assets/svgs/ic-note.svg', 'iconFile' => 'novel.svg'],
            ['name' => 'Komik', 'icon' => 'assets/svgs/ic-shopping-bag.svg', 'iconFile' => 'komik.svg'],
            ['name' => 'Pendidikan', 'icon' => 'assets/svgs/ic-house.svg', 'iconFile' => 'pendidikan.svg'],
            ['name' => 'Agama', 'icon' => 'assets/svgs/ic-grid.svg', 'iconFile' => 'agama.svg'],
            ['name' => 'Anak-Anak', 'icon' => 'assets/svgs/ic-vitamins-filled.svg', 'iconFile' => 'anak-anak.svg'],
        ];

        foreach ($categories as $category) {
            $iconPath = $this->copyImageToStorage($category['icon'], 'category_icons', $category['iconFile']);

            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $iconPath,
                ]
            );
        }
    }
}