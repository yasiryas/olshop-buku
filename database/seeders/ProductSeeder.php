<?php

namespace Database\Seeders;

use App\Models\Product;
use Database\Seeders\Concerns\CopiesImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use CopiesImages;

    public function run(): void
    {
        $products = [
            [
                'name' => 'Laskar Pelangi',
                'category' => 'Novel',
                'photo' => 'assets/images/product-1.webp',
                'photoFile' => 'laskar-pelangi.webp',
                'price' => 95000,
                'stockIn' => 50,
                'about' => 'Novel inspiratif karya Andrea Hirata yang berkisah tentang perjuangan 10 anak dari desa kecil di Belitung demi meraih mimpi melalui pendidikan.',
            ],
            [
                'name' => 'Bumi Manusia',
                'category' => 'Novel',
                'photo' => 'assets/images/product-2.webp',
                'photoFile' => 'bumi-manusia.webp',
                'price' => 110000,
                'stockIn' => 35,
                'about' => 'Roman tetralogi karya Pramoedya Ananta Toer yang mengisahkan kehidupan Minke di era kolonial Hindia Belanda.',
            ],
            [
                'name' => 'Naruto Shippuden Vol.1',
                'category' => 'Komik',
                'photo' => 'assets/images/product-3.webp',
                'photoFile' => 'naruto-shippuden.webp',
                'price' => 45000,
                'stockIn' => 80,
                'about' => 'Komik seru karya Masashi Kishimoto yang menceritakan petualangan Naruto Uzumaki di era Shippuden.',
            ],
            [
                'name' => 'One Piece Vol. 100',
                'category' => 'Komik',
                'photo' => 'assets/images/product-4.webp',
                'photoFile' => 'one-piece-100.webp',
                'price' => 50000,
                'stockIn' => 65,
                'about' => 'Petualangan Luffy dan kru Topi Jerami dalam manga legendaris karya Eiichiro Oda.',
            ],
            [
                'name' => 'Matematika untuk SMA Kelas X',
                'category' => 'Pendidikan',
                'photo' => 'assets/images/herosection.webp',
                'photoFile' => 'matematika-sma-x.webp',
                'price' => 120000,
                'stockIn' => 40,
                'about' => 'Buku pelajaran matematika kurikulum merdeka untuk jenjang SMA kelas X yang lengkap dan mudah dipahami.',
            ],
            [
                'name' => 'Al-Baqarah dan Asmaul Husna',
                'category' => 'Agama',
                'photo' => 'assets/images/logo-sample.webp',
                'photoFile' => 'al-baqarah.webp',
                'price' => 85000,
                'stockIn' => 25,
                'about' => 'Kajian tafsir surat Al-Baqarah dilengkapi pemahaman Asmaul Husna untuk kehidupan sehari-hari.',
            ],
            [
                'name' => 'Ensiklopedia Cerdas Anak Muslim',
                'category' => 'Anak-Anak',
                'photo' => 'assets/images/photo.png',
                'photoFile' => 'ensiklopedia-anak.png',
                'price' => 145000,
                'stockIn' => 30,
                'about' => 'Ensiklopedia bergambar untuk memperkenalkan ilmu pengetahuan dan nilai-nilai Islam kepada anak sejak dini.',
            ],
            [
                'name' => 'Si Kancil dan Teman-Teman',
                'category' => 'Anak-Anak',
                'photo' => 'assets/images/product-1.webp',
                'photoFile' => 'kancil.webp',
                'price' => 56000,
                'stockIn' => 45,
                'about' => 'Kumpulan dongeng nusantara pilihan untuk anak dengan ilustrasi penuh warna yang mendidik.',
            ],
        ];

        foreach ($products as $product) {
            $photoPath = $this->copyImageToStorage($product['photo'], 'product_photos', $product['photoFile']);
            $categoryId = \App\Models\Category::where('name', $product['category'])->value('id');

            $newProduct = Product::updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'name' => $product['name'],
                    'photo' => $photoPath,
                    'price' => $product['price'],
                    'about' => $product['about'],
                    'category_id' => $categoryId,
                ]
            );

            $newProduct->stockMutations()->firstOrCreate(
                [
                    'type' => 'in',
                    'description' => 'Stok awal',
                ],
                [
                    'quantity' => $product['stockIn'],
                ]
            );
        }
    }
}