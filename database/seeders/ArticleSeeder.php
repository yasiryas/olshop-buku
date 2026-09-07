<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\Concerns\CopiesImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    use CopiesImages;

    public function run(): void
    {
        $admin = User::where('email', 'admin@mail.com')->first();
        $penulis = User::where('email', 'penulis@mail.com')->first();

        $articles = [
            [
                'title' => 'Tips Memilih Buku yang Tepat untuk Anak',
                'category' => 'Anak-Anak',
                'author' => $penulis,
                'image' => 'assets/images/herosection.webp',
                'imageFile' => 'tips-memilih-buku-anak.webp',
                'content' => '<p>Memilih buku untuk anak bukanlah perkara mudah. Anak-anak memiliki ketertarikan yang berbeda-beda terhadap jenis cerita, ilustrasi, dan gaya bahasa.</p><p>Pilihlah buku dengan ilustrasi penuh warna, cerita sederhana, dan pesan moral yang kuat. Pastikan juga bahannya aman dan tidak mudah sobek bagi anak-anak yang masih balita.</p>',
            ],
            [
                'title' => 'Resensi Novel Laskar Pelangi Karya Andrea Hirata',
                'category' => 'Novel',
                'author' => $admin,
                'image' => 'assets/images/product-1.webp',
                'imageFile' => 'resensi-laskar-pelangi.webp',
                'content' => '<p>Laskar Pelangi adalah novel pertama karya Andrea Hirata yang terbit pada tahun 2005. Novel ini menceritakan kisah sepuluh anak di desa Gantong, Belitung, dalam memperjuangkan pendidikan.</p><p>Dengan gaya bahasa yang ringan namun penuh makna, Laskar Pelangi berhasil membawa pembaca merasakan perjuangan anak-anak yang memiliki keterbatasan sarana namun kaya akan semangat.</p>',
            ],
            [
                'title' => 'Mengenal Dunia Komik: Lebih dari Sekadar Hiburan',
                'category' => 'Komik',
                'author' => $penulis,
                'image' => 'assets/images/product-3.webp',
                'imageFile' => 'mengenal-dunia-komik.webp',
                'content' => '<p>Komik sering dianggap sekadar hiburan, padahal komik juga dapat menjadi media edukasi yang efektif. Alur cerita yang menarik dengan visual yang kuat membuat informasi lebih mudah diserap.</p><p>Komik seperti Naruto, One Piece, dan karya-karya lokal Indonesia mampu menyampaikan nilai-nilai persahabatan, pantang menyerah, dan keberanian.</p>',
            ],
            [
                'title' => 'Pentingnya Literasi Keuangan Sejak Dini',
                'category' => 'Pendidikan',
                'author' => $admin,
                'image' => 'assets/images/product-2.webp',
                'imageFile' => 'literasi-keuangan-dini.webp',
                'content' => '<p>Literasi keuangan adalah kemampuan memahami dan mengelola keuangan dengan baik. Membiasakan anak mengenal uang dan cara mengelolanya sejak dini berdampak positif pada masa depannya.</p><p>Orang tua dapat mulai mengajarkan menabung, membedakan kebutuhan dan keinginan, serta menghargai nilai uang melalui kegiatan sehari-hari.</p>',
            ],
            [
                'title' => 'Koleksi Buku Agama untuk Keluarga Muslim',
                'category' => 'Agama',
                'author' => $penulis,
                'image' => 'assets/images/logo-sample.webp',
                'imageFile' => 'buku-agama-keluarga.webp',
                'content' => '<p>Membaca buku-buku agama dapat memperkaya wawasan keislaman keluarga. Mulai dari buku tentang Asmaul Husna, kisah para nabi, hingga tafsir surat-surat pilihan.</p><p>Ajak anak membaca bersama-sama dan diskusikan nilai-nilai yang terkandung agar pemahaman lebih mendalam.</p>',
            ],
            [
                'title' => 'Membaca di Era Digital: Antara Buku Fisik dan E-Book',
                'category' => 'Pendidikan',
                'author' => $admin,
                'image' => 'assets/images/photo.png',
                'imageFile' => 'membaca-era-digital.webp',
                'content' => '<p>Di era digital, kebiasaan membaca bertransformasi. Buku fisik tetap digemari untuk pengalaman membaca yang nyata, sementara e-book menawarkan kepraktisan dan portabilitas.</p><p>Keduanya memiliki kelebihan masing-masing. Yang terpenting adalah membangun kebiasaan membaca yang konsisten setiap hari.</p>',
            ],
        ];

        foreach ($articles as $article) {
            $imagePath = $this->copyImageToStorage($article['image'], 'articles', $article['imageFile']);
            $categoryId = Category::where('name', $article['category'])->value('id');
            $authorId = $article['author']->id;

            Article::updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'featured_image' => $imagePath,
                    'user_id' => $authorId,
                    'category_id' => $categoryId,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }
    }
}