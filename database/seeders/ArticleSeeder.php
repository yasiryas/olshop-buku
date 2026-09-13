<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    private const COVER_PALETTES = [
        ['#7c3aed', '#c026d3'],
        ['#2563eb', '#06b6d4'],
        ['#dc2626', '#f97316'],
        ['#059669', '#14b8a6'],
        ['#b45309', '#eab308'],
        ['#db2777', '#7c3aed'],
        ['#0f766e', '#22d3ee'],
        ['#4f46e5', '#8b5cf6'],
    ];

    public function run(): void
    {
        $admin = User::where('email', 'admin@mail.com')->first() ?? User::first();
        $penulis = User::where('email', 'penulis@mail.com')->first() ?? User::first();

        $articles = $this->articles();
        $count = count($articles);

        foreach ($articles as $index => $article) {
            $author = ($index % 2 === 0) ? $penulis : $admin;
            $imagePath = $this->articleCover($article['title'], $index, $count);
            $categoryId = Category::where('name', $article['category'])->value('id') ?? Category::first()->id;

            Article::updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'content' => $this->toHtml($article['content']),
                    'featured_image' => $imagePath,
                    'user_id' => $author->id,
                    'category_id' => $categoryId,
                    'is_published' => true,
                    'published_at' => now()->subDays($count - $index),
                ]
            );
        }
    }

    private function toHtml(array $paragraphs): string
    {
        return collect($paragraphs)
            ->map(fn ($paragraph) => Str::startsWith($paragraph, '## ')
                ? '<h2>' . Str::after($paragraph, '## ') . '</h2>'
                : '<p>' . $paragraph . '</p>')
            ->implode('');
    }

    private function articleCover(string $title, int $index, int $total): string
    {
        $fileName = sprintf('articles/article-cover-%02d.svg', $index + 1);
        $disk = Storage::disk('public');

        if ($disk->exists($fileName)) {
            return $fileName;
        }

        [$from, $to] = self::COVER_PALETTES[$index % count(self::COVER_PALETTES)];
        $titleText = htmlspecialchars(Str::limit($title, 34, '…'), ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="450" fill="url(#bg)"/>
  <circle cx="690" cy="80" r="130" fill="#ffffff" opacity="0.12"/>
  <circle cx="80" cy="410" r="150" fill="#ffffff" opacity="0.10"/>
  <g transform="translate(150,45)">
    <rect x="0" y="30" width="90" height="360" rx="8" fill="#111827" opacity="0.9"/>
    <rect x="18" y="55" width="54" height="310" rx="4" fill="#ffffff"/>
    <rect x="32" y="180" width="160" height="18" fill="#9ca3af" opacity="0.7"/>
    <rect x="32" y="150" width="120" height="14" fill="#d1d5db" opacity="0.85"/>
  </g>
  <g transform="translate(260,45)">
    <rect x="0" y="18" width="90" height="375" rx="8" fill="#030712" opacity="0.95"/>
    <rect x="18" y="44" width="54" height="322" rx="4" fill="#ffffff"/>
    <rect x="32" y="170" width="150" height="18" fill="#9ca3af" opacity="0.7"/>
  </g>
  <text x="420" y="345" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#ffffff">{$titleText}</text>
  <text x="421" y="385" font-family="Arial, sans-serif" font-size="18" fill="#ffffff" opacity="0.85">Wigati Buku</text>
</svg>
SVG;

        $disk->put($fileName, $svg);

        return $fileName;
    }

    private function articles(): array
    {
        return [
            [
                'title' => 'Tips Memilih Buku yang Tepat untuk Anak',
                'category' => 'Anak-Anak',
                'content' => [
                    'Memilih buku untuk anak bukanlah perkara mudah. Setiap anak memiliki ketertarikan yang berbeda terhadap jenis cerita, ilustrasi, dan gaya bahasa. Orang tua yang cermat akan memperhatikan usia, kemampuan membaca, serta minat sang anak sebelum membeli buku agar waktu membaca terasa menyenangkan dan tidak membosankan.',
                    'Pilihlah buku dengan ilustrasi penuh warna, cerita sederhana, dan pesan moral yang kuat. Untuk anak balita, pastikan bahan bukunya aman, tidak mudah sobek, dan sudutnya tidak tajam. Buku board book atau buku kain menjadi pilihan terbaik untuk kelompok usia ini karena tahan lama dan nyaman digenggam.',
                    'Sementara itu, anak usia sekolah dasar mulai gemar membaca cerita pendek, dongeng, maupun komik edukasi. Pada tahap ini, yang terpenting bukan hanya alur cerita, tetapi juga kosakata baru yang dapat memperkaya bahasa anak. Pilihkan cerita dengan tokoh yang kuat agar anak bisa belajar nilai-nilai seperti kejujuran dan keberanian.',
                    '## Ciptakan jeda membaca yang rutin',
                    'Selain memilih buku, orang tua juga perlu membangun jadwal membaca rutin. Sepuluh hingga dua puluh menit sebelum tidur adalah waktu yang tepat untuk membacakan buku bersama anak. Jadikan aktivitas ini menyenangkan dengan menirukan suara tokoh dan mengajukan pertanyaan ringan tentang isi cerita.',
                    'Terakhir, libatkan anak dalam pemilihan buku. Ajak mereka ke toko buku atau katalog online, lalu biarkan mereka memilih buku yang menarik. Wigati Buku menyediakan koleksi buku anak dari berbagai penerbit terpercaya, sehingga Anda dapat membantu buah hati menemukan bacaan favorit yang mendidik sekaligus menghibur.',
                ],
            ],
            [
                'title' => 'Resensi Novel Laskar Pelangi Karya Andrea Hirata',
                'category' => 'Novel',
                'content' => [
                    'Laskar Pelangi adalah novel pertama karya Andrea Hirata yang terbit pada tahun 2005. Novel ini menceritakan perjuangan sepuluh anak dari desa Gantong, Belitung, dalam memperebutkan pendidikan di tengah keterbatasan sarana dan ekonomi. Buku ini berhasil terjual jutaan eksemplar dan diadaptasi menjadi film yang sangat populer.',
                    'Kekuatan utama novel ini terletak pada gaya bahasa yang ringan, jenaka, namun menyentuh. Andrea Hirata menghadirkan tokoh-tokoh yang hidup, seperti Ikal, Lintang, dan Mahar, dengan latar belakang yang berbeda-beda. Pembaca diajak merasakan semangat anak-anak yang tetap bermimpi besar meski sekolah mereka hampir ditutup oleh pemerintah.',
                    'Melalui tokoh Bu Muslimah dan Pak Harfan, novel ini juga menyuarakan pentingnya peran guru. Mereka mengajar dengan penuh pengabdian tanpa pamrih, menjadi pelita bagi anak-anak yang haus ilmu. Nilai menghargai pendidikan dan pantang menyerah menjadikan novel ini relevan untuk dibaca oleh siapa pun.',
                    '## Pesan moral yang abadi',
                    'Laskar Pelangi mengajarkan bahwa keterbatasan bukan penghalang untuk meraih mimpi. Setiap pembaca dapat mengambil pelajaran tentang persahabatan, kesetiaan, dan keberanian menghadapi hidup. Cerita ini juga memperkenalkan keindahan budaya Belitung yang kini menjadi daya tarik wisata.',
                    'Bagi Anda yang belum membaca novel fenomenal ini, Wigati Buku menyediakan edisi terbaru dalam berbagai format. Buku ini cocok dijadikan bacaan awal untuk mengenal karya sastra Indonesia modern sekaligus memicu motivasi untuk terus belajar.',
                ],
            ],
            [
                'title' => 'Mengenal Dunia Komik: Lebih dari Sekadar Hiburan',
                'category' => 'Komik',
                'content' => [
                    'Komik sering dianggap hanya sebagai hiburan ringan, padahal media ini dapat menjadi alat edukasi yang efektif. Perpaduan antara gambar yang kuat dan alur cerita yang menarik membuat informasi lebih mudah diserap, terutama oleh anak-anak dan remaja yang tumbuh di era visual.',
                    'Komik seperti Naruto, One Piece, dan karya-karya lokal Indonesia mampu menyampaikan nilai-nilai persahabatan, kegigihan, dan keberanian. Melalui konflik antartokoh, pembaca belajar bahwa setiap masalah memiliki solusi dan setiap perjuangan butuh proses. Inilah yang membuat komik tidak sekadar menghibur tetapi juga membangun karakter.',
                    'Di Indonesia, komik lokal semakin berkembang dan diakui dunia. Karya seperti Si Juki dan komik silat tradisional memperkenalkan budaya Nusantara dengan kemasan yang modern. Mendukung komik lokal berarti ikut melestarikan budaya sekaligus mengembangkan industri kreatif dalam negeri.',
                    '## Koleksi komik yang layak dimiliki',
                    'Bagi kolektor pemula, mulailah dengan komik yang memiliki cerita lengkap dan pesan positif. Perhatikan juga kualitas cetak dan kertas agar koleksi tetap awet dalam jangka panjang. Simpan komik di tempat kering dan jauh dari sinar matahari langsung agar warnanya tidak pudar.',
                    'Wigati Buku menghadirkan beragam komik lokal maupun internasional, mulai dari edisi reguler hingga eksklusif. Anda dapat mencari judul favorit, membandingkan harga, dan memesan langsung dengan proses yang mudah serta aman.',
                ],
            ],
            [
                'title' => 'Pentingnya Literasi Keuangan Sejak Dini',
                'category' => 'Pendidikan',
                'content' => [
                    'Literasi keuangan adalah kemampuan untuk memahami dan mengelola uang dengan bijak. Membiasakan anak mengenal uang serta cara mengelolanya sejak dini memberikan dampak positif jangka panjang bagi masa depannya. Anak yang terbiasa berpikir tentang menabung cenderung mampu membedakan kebutuhan dan keinginan.',
                    'Orang tua dapat memulai dengan cara sederhana, misalnya memberikan uang saku dan mengajak anak menghitung sisa belanja. Ajarkan mereka menabung sedikit demi sedikit ke dalam celengan, lalu menceritakan tujuan menabung seperti membeli buku atau mainan yang diinginkan. Kebiasaan kecil ini menumbuhkan rasa tanggung jawab.',
                    'Buku-buku literasi keuangan anak kini banyak tersedia dengan cerita yang menarik dan ilustrasi yang ramah. Buku tersebut menjelaskan konsep menabung, berbagi, dan berbelanja dengan bahasa yang mudah dipahami anak. Membacakan buku seperti ini bersama anak membuat pembelajaran terasa seperti bermain.',
                    '## Membangun kebiasaan finansial yang sehat',
                    'Saat anak memasuki usia remaja, tingkatkan kompleksitas pembelajaran menjadi pengenalan terhadap perbedaan kebutuhan dan keinginan, serta konsekuensi meminjam uang. Diskusikan pula contoh pengambilan keputusan finansial dalam kehidupan sehari-hari agar mereka terbiasa berpikir kritis.',
                    'Wigati Buku merekomendasikan berbagai buku edukasi keuangan untuk anak dan remaja dari penerbit yang kredibel. Mulailah sejak dini, karena kemampuan mengelola uang adalah bekal keterampilan hidup yang tidak kalah penting dibandingkan pelajaran akademis.',
                ],
            ],
            [
                'title' => 'Koleksi Buku Agama untuk Keluarga Muslim',
                'category' => 'Agama',
                'content' => [
                    'Membaca buku-buku agama dapat memperkaya wawasan keislaman seluruh anggota keluarga. Mulai dari buku tentang Asmaul Husna, kisah para nabi, hingga tafsir surat-surat pilihan, setiap bacaan membawa manfaat bagi keimanan dan perilaku sehari-hari.',
                    'Untuk anak-anak, pilih buku bergambar dengan cerita nabi yang sederhana. Kisah-kisah teladan seperti Nabi Ibrahim yang taat dan Nabi Muhammad yang jujur dapat menjadi role model bagi anak. Sertakan diskusi santai setelah membaca agar anak memahami pesan moral dari setiap kisah.',
                    'Bagi remaja dan dewasa, buku-buku seperti tentang adab, fiqih ibadah, dan sirah nabawiyah sangat direkomendasikan. Bacaan ini menjawab banyak persoalan kehidupan dengan dalil yang jelas, sekaligus memperkuat kecintaan terhadap ilmu agama di tengah derasnya arus informasi.',
                    '## Membangun budaya membaca dan beramal',
                    'Ajak anak membaca bersama-sama secara rutin, misalnya satu jam sebelum magrib atau setelah salat berjamaah. Jadikan momen ini menyenangkan dan jangan memaksakan target tertentu. Penting juga untuk menyandingkan bacaan dengan praktik nyata, seperti bersikap jujur dan rendah hati.',
                    'Wigati Buku menyediakan koleksi buku agama yang lengkap untuk seluruh usia, dari mata pelajaran dasar hingga referensi tingkat lanjut. Dapatkan koleksi terbaik untuk keluarga Anda dan jadikan rumah sebagai pusat tumbuhnya generasi yang cinta ilmu dan berakhlak mulia.',
                ],
            ],
            [
                'title' => 'Membaca di Era Digital: Antara Buku Fisik dan E-Book',
                'category' => 'Pendidikan',
                'content' => [
                    'Di era digital, kebiasaan membaca mengalami transformasi besar. Buku fisik tetap digemari karena memberikan pengalaman membaca yang nyata, aroma kertas, dan kepuasan membalik halaman. Sementara itu, e-book menawarkan kepraktisan dan portabilitas yang sulit ditandingi.',
                    'Banyak orang kini memilih e-book karena bisa membawa ratusan judul dalam satu perangkat. Fitur pencarian teks, penanda halaman, dan pengaturan ukuran huruf menjadi keunggulan utama pembaca digital. Harga e-book yang biasanya lebih murah juga menjadi pertimbangan bagi pembaca aktif.',
                    'Namun buku fisik tetap memiliki keunggulan tersendiri. Membaca buku cetak cenderung membuat fokus lebih terjaga karena tidak ada notifikasi yang mengganggu. Koleksi buku juga menjadi dekorasi yang mempercantik ruangan serta bisa dipinjamkan kepada orang lain.',
                    '## Pilih yang paling nyaman untuk Anda',
                    'Yang terpenting adalah membangun kebiasaan membaca yang konsisten, apa pun bentuk medianya. Kombinasikan keduanya sesuai kebutuhan: gunakan e-book saat bepergian dan nikmati buku fisik saat berada di rumah. Berikan juga contoh positif kepada anak dengan rutin membaca di depan mereka.',
                    'Wigati Buku mendukung kedua gaya membaca tersebut. Kami menjual buku fisik berkualitas serta menghadirkan layanan pembelian online yang praktis. Temukan judul favorit Anda dan jadikan membaca sebagai gaya hidup keluarga di tengah kemajuan teknologi.',
                ],
            ],
            [
                'title' => 'Cara Membangun Kebiasaan Membaca Buku',
                'category' => 'Pendidikan',
                'content' => [
                    'Membangun kebiasaan membaca tidak bisa dilakukan dengan instan. Dibutuhkan konsistensi, niat yang kuat, dan lingkungan yang mendukung. Banyak orang ingin membaca lebih banyak, tetapi gagal karena tidak memiliki strategi yang jelas dalam membagi waktu.',
                    'Mulailah dengan target kecil yang realistis, misalnya sepuluh halaman atau lima belas menit setiap hari. Alih-alih memaksakan jumlah buku dalam sebulan, fokuslah pada rutinitas harian yang ringan. Kebiasaan kecil yang berulang akan tumbuh tanpa terasa menjadi kebiasaan besar.',
                    'Ciptakan sudut baca yang nyaman di rumah lengkap dengan pencahayaan yang baik dan tempat duduk yang mendukung. Jauhkan gadget dari area tersebut agar Anda tidak mudah tergoda notifikasi. Waktu terbaik biasanya pagi hari atau sebelum tidur, tergantung ritme tubuh masing-masing.',
                    '## Beragamkan bacaan agar tidak bosan',
                    'Agar kegiatan membaca tetap menarik, variasi genre sangat disarankan. Selang-seling antara fiksi dan nonfiksi, buku pengembangan diri, dan biografi. Anda juga bisa bergabung dengan komunitas membaca untuk berbagi rekomendasi dan mendapat motivasi tambahan.',
                    'Wigati Buku siap membantu Anda menemukan buku yang tepat untuk memulai kebiasaan membaca. Katalog kami mencakup ribuan judul dengan deskripsi yang jelas sehingga Anda dapat memilih sesuai minat dan kebutuhan. Mulai hari ini, dan jadikan membaca sebagai teman setia Anda.',
                ],
            ],
            [
                'title' => '7 Novel Indonesia yang Wajib Dibaca',
                'category' => 'Novel',
                'content' => [
                    'Sastra Indonesia memiliki kekayaan karya yang tidak perlu diragukan. Sejak era Balai Pustaka hingga generasi penulis muda, banyak novel yang mampu menyentuh hati dan memengaruhi cara pandang pembaca. Berikut tujuh judul yang layak masuk dalam daftar bacaan Anda.',
                    'Laskar Pelangi karya Andrea Hirata, Bumi Manusia karya Pramoedya Ananta Toer, dan Negeri 5 Menara karya Ahmad Fuadi adalah tigatiga judul yang merepresentasikan semangat perjuangan dan harapan. Ketiganya mengangkat tema pendidikan, keadilan, dan perjalanan hidup yang sangat menginspirasi.',
                    'Dari ranah fiksi populer, Pulang karya Tere Liye dan Cantik Itu Luka karya Eka Kurniawan menawarkan cerita yang kuat dengan alur menegangkan. Sementara itu, Laut Bercerita karya Leila S. Chudori dan Pergi karya Tere Liye menghadirkan perspektif sejarah dan keluarga yang dalam.',
                    '## Temukan gaya bercerita favorit Anda',
                    'Setiap novel membawa gaya penceritaan yang berbeda. Pramoedya dikenal dengan realisme sejarah, sedangkan Tere Liye menghadirkan cerita yang ringan namun menyentuh. Eksplorasi berbagai gaya ini akan memperkaya apresiasi Anda terhadap sastra Indonesia.',
                    'Kunjungi Wigati Buku untuk menemukan ketujuh novel di atas beserta rekomendasi lainnya. Kami menawarkan harga kompetitif, pengemasan rapi, dan pengiriman ke seluruh Indonesia. Lengkapi rak buku Anda dengan karya-karya terbaik sastra Indonesia.',
                ],
            ],
            [
                'title' => 'Tips Mengatur Reading List agar Lebih Produktif',
                'category' => 'Pendidikan',
                'content' => [
                    'Reading list yang berantakan sering menjadi penyebab seseorang berhenti membaca. Banyak orang menumpuk daftar puluhan judul tanpa pernah menyelesaikan satupun. Padahal, dengan pengaturan yang baik, reading list justru bisa menjadi alat yang membuat kegiatan membaca lebih produktif.',
                    'Mulailah dengan memilih tiga buku untuk sebulan: satu buku fiksi, satu buku nonfiksi, dan satu buku sesuai kebutuhan pekerjaan atau sekolah. Membatasi jumlah buku membuat Anda lebih fokus dan tidak mudah berpindah-pindah judul sebelum tamat.',
                    'Urutkan prioritas berdasarkan tujuan Anda. Jika ingin memperdalam profesi, dahulukan buku pengembangan diri. Jika ingin bersantai, sisipkan novel ringan di sela-sela bacaan berat. Seimbangkan antara buku yang menantang dan buku yang menyenangkan agar motivasi tetap tinggi.',
                    '## Gunakan aplikasi pencatat bacaan',
                    'Manfaatkan aplikasi penanda bacaan untuk mencatat progres, kutipan favorit, dan catatan penting. Data tersebut berguna untuk mengevaluasi kebiasaan dan merencanakan pembelian buku berikutnya. Jangan lupa review singkat setiap selesai membaca sebagai pengingat isi buku.',
                    'Wigati Buku membantu Anda merencanakan pembelian dengan daftar kategori yang rapi dan fitur pencarian yang cepat. Susun reading list hari ini, pilih judul yang benar-benar ingin Anda baca, dan nikmati perjalanan menambah wawasan buku demi buku.',
                ],
            ],
            [
                'title' => 'Sejarah Singkat Novel di Indonesia',
                'category' => 'Novel',
                'content' => [
                    'Perkembangan novel di Indonesia tidak lepas dari peran penerbit Balai Pustaka yang berdiri pada masa kolonial. Novel pertama yang tercatat, seperti Azab dan Sengsara karya Merari Siregar, lahir dari lembaga ini dan menjadi cikal bakal tradisi membaca masyarakat Indonesia.',
                    'Pada era kemerdekaan hingga tahun 1960-an, sastrawan mulai menulis novel dengan tema perjuangan dan nasionalisme. Karya-karya Pramoedya Ananta Toer menjadi tonggak penting karena berani mengangkat isu sosial dengan realisme yang tajam.',
                    'Tahun 1970-an hingga 1990-an memperlihatkan keragaman tema, mulai dari roman percintaan hingga kritik sosial. Muncul nama-nama seperti Nh. Dini dan Ahmad Tohari yang memperkaya khazanah narasi Indonesia dengan sentuhan budaya lokal yang kuat.',
                    '## Era modern dan kebangkitan genre populer',
                    'Memasuki abad ke-21, novel Indonesia semakin beragam dengan kehadiran genre fantasi, teenlit, dan thriller. Penulis muda seperti Tere Liye dan Dee Lestari berhasil menarik pembaca lintas generasi dan mengangkat novel Indonesia ke panggung internasional.',
                    'Mempelajari sejarah novel membantu kita menghargai setiap karya yang ada saat ini. Wigati Buku mengoleksi novel klasik maupun kontemporer, lengkap untuk menjadi bahan pembelajaran maupun hiburan. Temukan jejak sejarah sastra melalui setiap halaman yang Anda baca.',
                ],
            ],
            [
                'title' => 'Rekomendasi Komik Islami untuk Anak',
                'category' => 'Komik',
                'content' => [
                    'Komik islami menjadi pilihan cerdas bagi orang tua yang ingin mengenalkan nilai-nilai keislaman kepada anak dengan cara yang menyenangkan. Sebab, anak-anak lebih cepat memahami cerita bergambar daripada teks panjang yang belum tentu mereka minati.',
                    'Beberapa judul komik islami mengangkat kisah para nabi, seperti Nabi Nuh, Nabi Ibrahim, dan Nabi Muhammad. Penyajiannya dibuat ringan dengan ilustrasi yang menarik namun tetap menjaga adab dan kesopanan sesuai tuntunan agama.',
                    'Selain kisah nabi, tersedia pula komik tentang adab sehari-hari, seperti belajar mengenai cara bersalaman, berdoa sebelum makan, dan menjaga kebersihan. Cerita-cerita ini membantu anak menerjemahkan ajaran agama menjadi perilaku nyata dalam kehidupan sehari-hari.',
                    '## Jadikan bacaan islami sebagai rutinitas',
                    'Sisipkan komik islami dalam kegiatan membaca keluarga, misalnya pada akhir pekan. Diskusikan pesan yang terkandung agar anak tidak sekadar menikmati gambar, tetapi juga memahami maknanya. Ilustrasi berwarna dengan dialog sederhana sangat membantu proses ini.',
                    'Wigati Buku menghadirkan pilihan komik islami dari berbagai penerbit dengan harga bersahabat. Filter kategori Agama dan Anak-Anak memudahkan Anda mencari judul terbaik. Berinvestasilah pada bacaan yang menyenangkan sekaligus menumbuhkan akhlak mulia.',
                ],
            ],
            [
                'title' => 'Mengapa Anak-Anak Perlu Dibiasakan Membaca',
                'category' => 'Anak-Anak',
                'content' => [
                    'Membaca adalah jendela dunia yang membuka banyak kemungkinan bagi tumbuh kembang anak. Anak yang gemar membaca memiliki kosakata yang lebih kaya, daya pikir yang tajam, dan kemampuan empati yang lebih baik karena terbiasa memahami sudut pandang tokoh cerita.',
                    'Kegiatan membaca juga melatih fokus dan konsentrasi, dua keterampilan yang semakin langka di tengah gempuran media sosial. Saat anak asyik menekuni sebuah buku tanpa gangguan, otak mereka belajar untuk bekerja dalam satu tugas dalam waktu yang lebih lama.',
                    'Orang tua berperan besar dalam menumbuhkan kebiasaan ini. Membacakan cerita sejak dini, menyediakan buku di rumah, dan menjadi teladan dengan rutin membaca adalah kunci utamanya. Sekalipun sederhana, kehadiran buku yang mudah dijangkau sangat memengaruhi minat anak.',
                    '## Menjadikan membaca sebagai kebiasaan keluarga',
                    'Konsistensi lebih penting daripada intensitas. Sepuluh menit membaca setiap hari jauh lebih baik daripada tiga jam membaca sebulan sekali. Jadwalkan waktu membaca bersama dan biarkan anak memilih buku yang mereka sukai secara mandiri.',
                    'Wigati Buku menyediakan koleksi buku anak terlengkap, mulai dari cerita bergambar, dongeng, hingga ensiklopedia anak. Berikan hadiah berupa buku untuk setiap pencapaian kecil si kecil, dan jadikan membaca sebagai kegiatan yang selalu dinanti-nanti.',
                ],
            ],
            [
                'title' => '10 Buku Pengembangan Diri yang Mengubah Hidup',
                'category' => 'Pendidikan',
                'content' => [
                    'Buku pengembangan diri memiliki kekuatan untuk mengubah pola pikir dan kebiasaan seseorang. Banyak orang sukses mengakui bahwa bacaan jenis ini turut membentuk cara mereka bekerja, berhubungan dengan orang lain, dan mencapai tujuan hidup.',
                    'Judul seperti Atomic Habits karya James Clear mengajarkan bahwa perubahan besar dimulai dari kebiasaan kecil yang diulang setiap hari. Habit yang baik dipicu oleh lingkungan yang mendukung, bukan kemauan semata. Prinsip inilah yang menjadikan buku ini sangat populer di seluruh dunia.',
                    'Selain itu, buku seperti Mindset karya Carol Dweck menjelaskan perbedaan pola pikir berkembang dan pola pikir tetap. Memahami keduanya membantu Anda menghadapi kegagalan dengan lebih sehat. Adapun buku The Power of Habit menawarkan kerangka untuk memahami bagaimana kebiasaan bekerja di otak.',
                    '## Terapkan isi buku, bukan sekadar membacanya',
                    'Rahasia efektivitas buku pengembangan diri terletak pada penerapannya. Catat satu aksi nyata setelah membaca setiap bab dan lakukan selama sepekan. Dengan begitu, pengetahuan berubah menjadi perubahan perilaku yang riil dalam kehidupan Anda.',
                    'Wigati Buku menghadirkan buku-buku pengembangan diri terjemahan maupun karya penulis lokal dengan kualitas terjamin. Pilih satu buku yang paling relevan dengan kondisi Anda saat ini, baca pelan-pelan, dan praktikkan isinya untuk hasil yang nyata.',
                ],
            ],
            [
                'title' => 'Perbedaan Buku Fiksi dan Nonfiksi',
                'category' => 'Pendidikan',
                'content' => [
                    'Buku fiksi dan nonfiksi adalah dua kategori besar dalam dunia penerbitan yang memiliki karakteristik berbeda. Memahami perbedaannya membantu Anda memilih bacaan yang sesuai dengan tujuan, baik untuk hiburan, pembelajaran, maupun referensi pekerjaan.',
                    'Buku fiksi berisi cerita imajinatif yang tidak terikat fakta, seperti novel, kumpulan cerpen, dan komik. Fiksi bertujuan menghibur sekaligus menyampaikan pesan melalui tokoh dan alur. Kendati tidak nyata, fiksi mampu mengajarkan banyak hal tentang kehidupan dan berkontribusi mengembangkan empati.',
                    'Sementara itu, buku nonfiksi menyajikan informasi yang berdasarkan fakta dan kejadian nyata. Contohnya biografi, buku sejarah, buku pengembangan diri, dan buku pelajaran. Nonfiksi dirancang untuk menambah pengetahuan dan keterampilan pembacanya secara langsung.',
                    '## Kombinasikan keduanya untuk hasil terbaik',
                    'Membaca kedua jenis buku secara bergantian memberikan keseimbangan. Fiksi menjaga imajinasi dan kepekaan, sedangkan nonfiksi menajamkan logika dan wawasan. Kombinasi keduanya menjadikan pola pikir Anda lebih lengkap dan fleksibel.',
                    'Wigati Buku mengelompokkan koleksinya secara rapi sehingga Anda mudah membedakan fiksi dan nonfiksi. Kunjungi halaman kategori untuk menjelajahi ribuan judul dan temukan komposisi bacaan terbaik untuk kebutuhan Anda.',
                ],
            ],
            [
                'title' => 'Resensi Novel Bumi Manusia',
                'category' => 'Novel',
                'content' => [
                    'Bumi Manusia adalah novel pembuka Tetralogi Buru karya Pramoedya Ananta Toer. Diterbitkan pada tahun 1980, novel ini langsung menuai kontroversi sekaligus pujian karena keberaniannya mengangkat tema penjajahan, cinta, dan kebangkitan manusia pribumi.',
                    'Cerita berpusat pada Minke, seorang pemuda Jawa cerdas yang menjadi pribumi pertama yang bersekolah di HBS. Melalui perspektifnya, pembaca diajak melihat ketimpangan sosial di era Hindia Belanda serta pergulatan identitas antara menjadi priyayi dan menjadi manusia merdeka.',
                    'Pramoedya menghadirkan tokoh wanita yang kuat, Annelies dan Nyai Ontosoroh, yang merepresentasikan penderitaan sekaligus ketangguhan kaum perempuan. Interaksi mereka dengan Minke membentuk benang merah tentang perlawanan terhadap penindasan dan ketidakadilan.',
                    '## Kenapa novel ini wajib dibaca',
                    'Bumi Manusia bukan sekadar roman sejarah. Novel ini memuat refleksi mendalam tentang martabat manusia, pendidikan, dan kemerdekaan berpikir. Bahasa Pramoedya yang khas membuat peristiwa sejarah terasa hidup dan relevan hingga hari ini.',
                    'Temukan novel Bumi Manusia dan karya Pramoedya lainnya di Wigati Buku. Kami memastikan keaslian produk dan kualitas cetakan sehingga Anda dapat menikmati salah satu mahakarya sastra Indonesia dengan nyaman dan aman.',
                ],
            ],
            [
                'title' => 'Manfaat Membaca untuk Kesehatan Otak',
                'category' => 'Pendidikan',
                'content' => [
                    'Membaca bukan hanya memperkaya wawasan, tetapi juga memberikan manfaat nyata bagi kesehatan otak. Berbagai penelitian menunjukkan bahwa kegiatan membaca rutin meningkatkan koneksi antar neuron dan memperlambat penurunan fungsi kognitif seiring bertambahnya usia.',
                    'Saat membaca, otak aktif memproses informasi, membayangkan suasana cerita, dan menghubungkan konsep yang satu dengan yang lain. Aktivitas seperti ini melatih daya ingat, kemampuan analisis, serta konsentrasi. Itulah sebabnya membaca dianjurkan untuk segala usia.',
                    'Menariknya, membaca fiksi terbukti meningkatkan empati karena pembaca diajak memahami pikiran dan perasaan tokoh. Proses ini melatih otak untuk mensimulasikan kehidupan sosial yang bermanfaat dalam interaksi nyata sehari-hari.',
                    '## Jadikan membaca bagian dari gaya hidup sehat',
                    'Sama seperti olahraga yang menjaga tubuh, membaca menjaga pikiran tetap tajam. Kombinasikan membaca dengan pola hidup sehat seperti tidur cukup dan makan bergizi agar otak bekerja optimal. Mulailah dengan jadwal membaca yang realistis sebelum tidur.',
                    'Wigati Buku mendukung gaya hidup sehat Anda dengan menyediakan berbagai buku berkualitas, dari novel hingga buku sains populer. Investasikan waktu membaca setiap hari, dan rasakan sendiri dampaknya terhadap ketajaman berpikir serta ketenangan batin.',
                ],
            ],
            [
                'title' => 'Panduan Memilih Buku Pelajaran Kurikulum Merdeka',
                'category' => 'Pendidikan',
                'content' => [
                    'Kurikulum Merdeka mengubah cara pembelajaran di sekolah, termasuk pemilihan buku pegangan siswa. Buku pelajaran yang baik harus selaras dengan capaian pembelajaran serta memuat aktivitas yang mendorong berpikir kritis, bukan sekadar menghafal.',
                    'Perhatikan kesesuaian buku dengan fase dan jenjang kelas siswa. Pastikan materi, latihan soal, dan ilustrasi disusun sesuai perkembangan usia. Buku yang terlalu padat akan membingungkan, sedangkan buku yang terlalu ringan kurang menantang.',
                    'Pilih penerbit yang terpercaya dan sudah memiliki izin edar dari Kementerian Pendidikan. Buku resmi dijamin kebenaran materinya, dilengkapi pendamping bagi guru, dan memudahkan koordinasi dengan kurikulum nasional.',
                    '## Manfaatkan buku pendamping dan digital',
                    'Selain buku utama, buku pendamping seperti lembar kerja dan soal pengayaan sangat membantu. Beberapa penerbit juga menyediakan versi digital yang memudahkan akses kapan saja. Kombinasikan keduanya untuk pengalaman belajar yang lebih lengkap.',
                    'Wigati Buku menyediakan buku pelajaran Kurikulum Merdeka untuk jenjang SD hingga SMA. Anda dapat memesan dalam jumlah besar untuk kebutuhan sekolah atau yayasan, lengkap dengan layanan pengiriman yang cepat ke seluruh Indonesia.',
                ],
            ],
            [
                'title' => 'Komik Edukasi yang Cocok untuk Remaja',
                'category' => 'Komik',
                'content' => [
                    'Komik edukasi menjadi jembatan antara hiburan dan pengetahuan bagi remaja. Dengan visual yang menarik, remaja yang kurang suka membaca teks panjang tetap bisa memahami konsep sains, sejarah, hingga literasi digital melalui komik yang dikemas apik.',
                    'Komik sains seperti tentang antariksa, tubuh manusia, atau evolusi membuat materi yang rumit terasa lebih mudah dicerna. Ilustrasi membantu pembaca membayangkan fenomena yang abstrak, sementara dialog antar tokoh menjaga nada cerita tetap menyenangkan.',
                    'Kategori sejarah juga menarik karena disajikan dalam bentuk tokoh dan peristiwa yang hidup. Remaja bisa menelusuri perjuangan kemerdekaan atau peradaban dunia tanpa merasa seperti sedang belajar sungguhan. Komik jenis ini sering menjadi pintu masuk minat akademis baru.',
                    '## Pilih komik dengan konten yang sehat',
                    'Pastikan konten komik sesuai usia dan nilai keluarga. Periksa peringkat usia, ulasan orang tua, dan kualitas penerbit. Diskusikan isi komik bersama remaja untuk menjaga mereka tetap kritis terhadap cerita yang dibaca.',
                    'Wigati Buku memiliki koleksi komik edukasi yang ramah remaja dengan harga terjangkau. Jadikan komik sebagai sarana belajar yang menyenangkan di rumah maupun di sekolah, dan tumbuhkan kecintaan membaca sejak dini.',
                ],
            ],
            [
                'title' => 'Tips Membaca Cepat (Speed Reading)',
                'category' => 'Pendidikan',
                'content' => [
                    'Speed reading adalah keterampilan membaca dengan kecepatan tinggi tanpa kehilangan pemahaman. Kemampuan ini sangat berguna bagi pelajar, mahasiswa, maupun profesional yang harus membaca banyak materi dalam waktu terbatas.',
                    'Teknik dasar speed reading dimulai dengan menghilangkan kebiasaan bersuara dalam hati (subvokalisasi) dan menghentikan gerakan mata mundur. Latihlah pandangan mata untuk menangkap kelompok kata, bukan kata per kata yang membuat tempo membaca melambat.',
                    'Gunakan jari atau penunjuk lain untuk memandu gerakan mata. Penunjuk membantu mata tetap fokus dan mengurangi pembacaan ulang yang tidak perlu. Berlatihlah dengan bacaan ringan terlebih dahulu lalu tingkatkan kesulitan secara bertahap.',
                    '## Pemahaman tetap menjadi prioritas',
                    'Kecepatan tanpa pemahaman tidak ada artinya. Jeda sejenak setelah beberapa bab untuk merangkum poin penting dalam benak atau catatan. Baca ulang bagian sulit dengan perlahan, dan tetap jaga kecepatan pada bagian yang memang membutuhkan ketelitian.',
                    'Wigati Buku menyediakan beragam buku yang dapat melatih keterampilan membaca cepat, termasuk buku latihan dan kumpulan esai. Terapkan teknik ini secara rutin dan rasakan peningkatan produktivitas membaca Anda.',
                ],
            ],
            [
                'title' => 'Koleksi Buku Fiqih Anak untuk Keluarga',
                'category' => 'Agama',
                'content' => [
                    'Fiqih anak adalah bekal penting bagi orang tua untuk mengajarkan ibadah dan adab sesuai syariat. Buku fiqih yang dirancang khusus untuk anak menyajikan materi tata cara salat, wudhu, puasa, dan zakat dengan bahasa yang sederhana dan menarik.',
                    'Buku-buku ini biasanya dilengkapi ilustrasi langkah demi langkah sehingga anak mudah mengikuti. Pelafalan doa dan bacaan salat diberikan dengan panduan membaca yang jelas, lengkap beserta artinya agar anak memahami makna yang diucapkan.',
                    'Pilih buku fiqih sesuai usia anak. Untuk anak kecil, fokus pada fundamental seperti wudhu dan salat. Untuk anak yang lebih besar, tambahkan materi tentang puasa, zakat, dan adab bermuamalah agar pemahaman keagamaan semakin utuh.',
                    '## Praktikkan bersama, bukan sekadar membaca',
                    'Pendampingan orang tua membuat pembelajaran fiqih lebih hidup. Ajak anak mempraktikkan gerakan salat, mendampingi saat menghafal doa, dan berikan pujian atas setiap kemajuan. Pendekatan ini membuat anak merasa ibadah adalah kewajiban yang menyenangkan.',
                    'Wigati Buku menghadirkan koleksi buku fiqih anak dari penerbit yang kredibel. Berikan bekal ilmu agama yang benar sejak dini agar anak tumbuh menjadi pribadi yang taat, santun, dan mencintai ibadahnya sepanjang hidup.',
                ],
            ],
            [
                'title' => 'Resensi Novel Ayah: Kisah Bijak Sekaligus Mengharukan',
                'category' => 'Novel',
                'content' => [
                    'Ayah adalah novel karya Andrea Hirata yang mengangkat sosok ayah dengan latar kisah yang khas kepulauan Belitung. Novel ini menghadirkan perenungan tentang cinta orang tua, keterbatasan, dan keikhlasan dalam cara yang sangat mengharukan.',
                    'Tokoh Sabari, sang ayah, digambarkan sebagai pria sederhana dengan pengorbanan tanpa batas. Meski hidup serba kekurangan, Sabari melakukan apa pun untuk kebahagiaan anak-anaknya. Pembaca diajak menyelami cinta yang tulus yang tidak selalu terucap.',
                    'Hirata menulis novel ini dengan gaya khasnya yang puitis namun jenaka. Perpaduan antara humor dan kesedihan membuat emosi pembaca naik turun. Latar budaya Melayu yang kental memperkaya cerita dan memberi warna yang berbeda dari novel sebelumnya.',
                    '## Pelajaran tentang kasih orang tua',
                    'Melalui kisah Sabari, pembaca diingatkan bahwa rasa kasih sayang tidak diukur dari materi. Kehadiran, perhatian, dan pengorbanan kecil sehari-hari justru merupakan wujud cinta yang paling berharga bagi seorang anak.',
                    'Wigati Buku menyediakan novel Ayah dan karya Andrea Hirata lainnya dengan edisi terbaru. Buku ini cocok menjadi hadiah untuk ayah tercinta atau bacaan refleksi bagi keluarga dan para orang tua.',
                ],
            ],
            [
                'title' => 'Cara Membuat Perpustakaan Mini di Rumah',
                'category' => 'Pendidikan',
                'content' => [
                    'Memiliki perpustakaan mini di rumah adalah impian banyak pencinta buku. Ruang sederhana dengan koleksi terpilih dapat menjadi tempat keluarga melepas penat sambil menambah wawasan, bahkan tanpa membutuhkan ruangan khusus yang luas.',
                    'Mulailah dengan memilih sudut rumah yang tenang dan memiliki pencahayaan alami yang baik. Gunakan rak buku yang kokoh, kursi atau bantal yang nyaman, serta lampu baca sebagai pelengkap. Tambahkan tanaman kecil agar suasana terasa lebih segar.',
                    'Kurasi koleksi berdasarkan anggota keluarga. Sisipkan buku anak, buku fiksi, nonfiksi, dan majalah agar semua orang menemukan bacaannya. Beri label sederhana pada setiap rak sehingga anggota keluarga mudah menemukan buku yang dicari.',
                    '## Perawatan dan pengembangan koleksi',
                    'Lindungi buku dari debu dengan rutin membersihkannya dan hindari paparan sinar matahari langsung. Lakukan penyortiran berkala, tukar buku dengan kerabat, atau donasikan buku yang sudah tidak terpakai agar koleksi tetap relevan.',
                    'Wigati Buku dapat menjadi mitra dalam membangun perpustakaan rumah Anda. Kami menawarkan banyak pilihan judul dengan harga terbaik. Bangun sudut baca keluarga hari ini dan tanamkan kebiasaan membaca sejak dini untuk generasi yang cerdas.',
                ],
            ],
            [
                'title' => 'Buku Tahsin dan Tajwid untuk Pemula',
                'category' => 'Agama',
                'content' => [
                    'Membaca Al-Qur\'an dengan benar adalah ibadah yang pahalanya sangat besar. Buku tahsin dan tajwid menjadi panduan penting bagi siapa saja yang ingin memperbaiki kualitas bacaan, mulai dari makhraj huruf hingga hukum-hukum bacaan.',
                    'Pemula disarankan memulai dari buku yang mengenalkan huruf hijaiyah beserta cara pengucapannya. Fokus pada makhraj yang benar menjadi dasar agar bacaan tidak berubah makna. Sebaiknya belajar dengan pembimbing agar kesalahan pengucapan tidak menetap.',
                    'Setelah lancar mengenal huruf, lanjutkan ke buku tajwid yang membahas hukum bacaan seperti nun sukun, mim sukun, dan bacaan mad. Pilih buku dengan contoh-contoh aplikatif dari ayat Al-Qur\'an agar teori mudah langsung dipraktikkan.',
                    '## Konsistensi mengalahkan target berlebihan',
                    'Jangan mengejar materi terlalu cepat. Latihan harian dalam porsi kecil dengan pengulangan berkala jauh lebih efektif daripada belajar banyak dalam satu waktu. Rekam bacaan Anda untuk dievaluasi atau gunakan aplikasi pendamping sebagai verifikasi.',
                    'Wigati Buku menyediakan buku tahsin, tajwid, dan panduan membaca Al-Qur\'an untuk pemula hingga tingkat lanjut. Mulai perjalanan memperbaiki bacaan Al-Qur\'an Anda hari ini dengan panduan yang benar dari sumber terpercaya.',
                ],
            ],
            [
                'title' => 'Komik Lokal Indonesia yang Mendunia',
                'category' => 'Komik',
                'content' => [
                    'Kebangkitan komik lokal Indonesia patut dibanggakan. Banyak karya anak bangsa kini dikerjakan untuk pasar internasional, baik sebagai komik orisinal maupun kontributor industri kreatif global. Hal ini membuktikan bahwa kualitas komik lokal diakui dunia.',
                    'Sejak era komik klasik seperti Si Buta dari Gua Hantu dan Gundala, industri komik Indonesia memiliki sejarah panjang. Warisan tersebut kini dilanjutkan oleh generasi baru dengan gaya yang segar dan digital-first yang menjangkau pembaca global.',
                    'Berbagai studio lokal telah bekerja sama dengan penerbit internasional, menghasilkan komik dengan standar profesional. Dukungan platform digital juga memudahkan komik lokal dikenal pembaca dari berbagai negara tanpa hambatan fisik.',
                    '## Dukung karya anak bangsa',
                    'Membeli komik lokal berarti ikut menghidupkan ekosistem kreatif dalam negeri. Karya-karya ini kerap menyisipkan budaya Nusantara, mulai dari mitologi hingga kuliner, sehingga turut memperkenalkan Indonesia ke dunia.',
                    'Wigati Buku bangga menyediakan komik-komik lokal terbaik dalam koleksinya. Temukan judul favorit dari genre aksi, fantasi, hingga komedi. Dukung terus penulis dan ilustrator Indonesia dengan memilih produk asli berkualitas.',
                ],
            ],
            [
                'title' => 'Pentingnya Buku Cerita Bergambar untuk Balita',
                'category' => 'Anak-Anak',
                'content' => [
                    'Buku cerita bergambar adalah jembatan pertama anak memasuki dunia literasi. Melalui gambar yang menarik, balita mulai mengenal objek, warna, emosi, serta mengembangkan kemampuan bahasa sebelum mereka mampu membaca kata secara utuh.',
                    'Membacakan buku bergambar melatih keterampilan mendengar dan memperluas kosakata. Anak yang rutin dibacakan cerita cenderung lebih mudah diajak komunikasi dan lebih percaya diri. Interaksi membalik halaman juga melatih motorik halus mereka.',
                    'Pilih buku dengan gambar besar, warna kontras, dan teks yang pendek. Cerita dengan pengulangan kalimat membantu balita meniru dan menghafal. Pilihlah tema yang dekat dengan keseharian, seperti makan, tidur, atau bermain.', 
                    '## Menjadikan sesi bercerita sebagai rutinitas',
                    'Jadwalkan waktu bercerita, misalnya sebelum tidur, dengan suasana yang tenang. Tirukan suara tokoh, gunakan intonasi yang bervariasi, dan ajukan pertanyaan sederhana untuk merangsang daya pikir. Konsistensi lebih penting daripada durasi yang panjang.',
                    'Wigati Buku memiliki koleksi buku cerita bergambar untuk balita dengan ilustrasi berkualitas dari berbagai penerbit. Bantu si kecil tumbuh menjadi anak yang cinta membaca sejak usia dini.',
                ],
            ],
            [
                'title' => '5 Tokoh Sastra Indonesia dan Karyanya',
                'category' => 'Novel',
                'content' => [
                    'Perjalanan sastra Indonesia tidak bisa dipisahkan dari tokoh-tokoh yang berani menulis di zamannya. Mereka tidak hanya menghasilkan karya, tetapi juga meletakkan fondasi bagi perkembangan bahasa dan budaya literasi bangsa.',
                    'Hamka adalah sastrawan sekaligus ulama yang terkenal lewat karya Tenggelamnya Kapal Van der Wijck. Pramoedya Ananta Toer melahirkan Tetralogi Buru yang menjadi salah satu novel Asia paling penting. Keduanya menulis dengan keberanian dan kedalaman.',
                    'Selain mereka, Nh. Dini dikenal dengan novel-novelnya yang peka terhadap isu perempuan, seperti Pada Sebuah Kapal. Sementara itu, Ahmad Tohari mempopulerkan sastra pedesaan melalui trilogi Ronggeng Dukuh Paruk. Chairil Anwar, meski lebih dikenal sebagai penyair, ikut memperkaya khazanah ini.',
                    '## Relevansi karya sastra hingga kini',
                    'Karya-karya tersebut tetap relevan karena menyentuh tema kemanusiaan yang abadi, seperti cinta, keadilan, dan identitas. Membacanya memberi perspektif baru tentang sejarah serta memperkaya cara kita memandang masyarakat.',
                    'Wigati Buku mengoleksi karya lengkap para tokoh sastra Indonesia dalam edisi asli terpercaya. Jelajahi bagian Novel dan Sastra untuk menemukan buku-buku klasik yang menjadi investasi wawasan bagi diri Anda.',
                ],
            ],
            [
                'title' => 'Tips Menulis Resensi Buku yang Menarik',
                'category' => 'Pendidikan',
                'content' => [
                    'Resensi buku adalah ulasan yang memberikan gambaran isi sekaligus penilaian atas sebuah karya. Resensi yang baik membantu pembaca lain memutuskan apakah buku tersebut cocok untuk mereka dan sering menjadi media yang menyenangkan untuk berbagi pendapat.',
                    'Mulailah resensi dengan data buku: judul, penulis, penerbit, dan tahun terbit. Lanjutkan dengan ringkasan singkat tanpa membocorkan akhir cerita. Fokus pada inti buku, alur, atau argumen penulis agar pembaca mendapatkan gambaran yang jujur.',
                    'Berikan penilaian pribadi dengan alasan yang jelas. Sebutkan kekuatan dan kekurangan buku secara seimbang, serta bandingkan dengan karya lain jika memungkinkan. Sertakan kutipan kecil yang mewakili gaya penulis untuk menambah daya tarik.',
                    '## Akhiri dengan rekomendasi yang jelas',
                    'Tutup resensi dengan saran untuk pembaca, misalnya siapa yang cocok membaca buku ini dan kapan waktu membacanya. Jaga nada tulis tetap netral dan hindari menjelekkan demi sensasi agar resensi Anda dipercaya.',
                    'Wigati Buku menyediakan banyak pilihan buku baru yang siap Anda ulas. Mulailah membuat review sederhana hari ini, bagikan ke teman atau komunitas, dan kembangkan kemampuan menulis sambil menumbuhkan budaya literasi.',
                ],
            ],
            [
                'title' => 'Buku Motivasi Islami untuk Kehidupan Sehari-hari',
                'category' => 'Agama',
                'content' => [
                    'Buku motivasi islami menawarkan inspirasi yang berakar pada nilai-nilai agama. Berbeda dengan motivasi umum, bacaan ini menyandingkan semangat berprestasi dengan ketenangan batin karena semuanya diletakkan dalam bingkai keimanan kepada Allah.',
                    'Buku-buku seperti tentang manajemen hati, keluarga bahagia, dan produktivitas muslimah banyak tersedia dengan gaya bahasa yang ringan. Pembaca diajak merenungkan ayat dan hadis yang relevan, lalu menerapkannya dalam keseharian secara aplikatif.',
                    'Motivasi islami juga mengajarkan cara menghadapi kegagalan dengan sabar dan tawakal. Sudut pandang ini membuat pembaca tidak mudah putus asa karena menyadari bahwa setiap ujian memiliki hikmah dan setiap usaha bernilai di sisi Allah.',
                    '## Amalkan nilai yang dibaca',
                    'Pilih buku motivasi islami yang selaras dengan kebutuhan Anda saat ini, baik tentang ibadah, karier, maupun keluarga. Catat satu pelajaran penting dan amalkan dalam kehidupan nyata agar buku tersebut benar-benar mengubah perilaku.',
                    'Wigati Buku menyediakan buku motivasi islami dari penulis-penulis terpercaya. Jadikan bacaan ini sebagai penguat semangat dan penghibur hati, sehingga jawaban atas kebimbangan selalu ditemukan dalam cahaya ilmu dan iman.',
                ],
            ],
            [
                'title' => 'Rekomendasi Buku Remaja Genre Young Adult',
                'category' => 'Novel',
                'content' => [
                    'Genre Young Adult (YA) menjadi favorit tidak hanya remaja, tetapi juga pembaca dewasa yang merindukan cerita segar tentang pencarian identitas. Buku YA biasanya menampilkan tokoh utama usia belasan dengan konflik yang dekat dengan pengalaman pembaca.',
                    'Buku YA lokal kini semakin berkembang dengan tema-tema yang relevan, seperti persahabatan, cinta pertama, tekanan sekolah, dan kesehatan mental. Cerita yang jujur dan relatable menjadi daya tarik utama genre ini bagi generasi muda.',
                    'Untuk pembaca internasional, seri seperti tentang petualangan, distopia, dan fantasi menawarkan pelarian imajinatif dengan pesan moral yang kuat. Perpaduan plot yang menegangkan dengan nilai-nilai kehidupan membuat genre ini begitu digemari.',
                    '## Pilih topik yang sesuai dengan kebutuhan',
                    'Orang tua dapat memandu remaja memilih buku YA dengan tema yang positif dan inspiratif. Diskusikan isi cerita bersama untuk mempererat hubungan sekaligus mengasah pemikiran kritis remaja terhadap nilai-nilai yang disajikan.',
                    'Wigati Buku mengoleksi berbagai judul young adult dari penulis Indonesia maupun asing. Pencarian berdasarkan kategori Novel akan membantu Anda menemukan bacaan yang pas untuk remaja di rumah.',
                ],
            ],
            [
                'title' => 'Belajar Coding untuk Anak dengan Buku Bergambar',
                'category' => 'Pendidikan',
                'content' => [
                    'Di tengah era digital, kemampuan berpikir komputasional menjadi keterampilan penting yang bisa dimulai sejak anak-anak. Berbagai buku coding untuk anak hadir dengan pendekatan menyenangkan yang tidak menuntut anak menghafal bahasa pemrograman.',
                    'Buku coding anak umumnya mengajarkan logika melalui permainan, puzzle, dan cerita. Konsep seperti urutan, pengulangan, dan percabangan disampaikan dengan analogi sederhana sebelum anak mengenal tools pemrograman visual seperti Scratch.',
                    'Ilustrasi yang ceria dan karakter yang ramah membuat anak tidak merasa sedang belajar materi berat. Aktivitas di tiap bab juga dirancang untuk dikerjakan bersama orang tua, sehingga mendekatkan hubungan keluarga sambil membangun keterampilan baru.',
                    '## Mulai dari logika, bukan perangkat',
                    'Kemampuan berpikir logis dapat dilatih bahkan tanpa gawai, misalnya lewat permainan kartu atau permainan papan. Setelah anak nyaman dengan konsep berpikir terstruktur, barulah perkenalkan perangkat dan aplikasi coding sebagai pelengkap.',
                    'Wigati Buku menyediakan buku belajar coding untuk anak dalam bahasa Indonesia dan bahasa Inggris. Tanamkan keterampilan masa depan sejak dini dengan buku bergambar yang menyenangkan dan mendidik.',
                ],
            ],
            [
                'title' => 'Membangun Minat Baca Anak di Era Gadget',
                'category' => 'Anak-Anak',
                'content' => [
                    'Kehadiran gadget memang mengubah kebiasaan bermain dan belajar anak. Meski demikian, minat baca tetap dapat ditumbuhkan jika orang tua cermat mengemas kegiatan membaca agar tidak kalah menarik dibanding layar gawai.',
                    'Kuncinya adalah menjadikan buku sebagai pilihan yang menyenangkan, bukan paksaan. Hadirkan buku dengan topik favorit anak, sediakan di tempat yang mudah dijangkau, dan jadikan membaca sebagai rutinitas yang dinanti-nanti setiap hari.',
                    'Batasi waktu layar secara wajar dan gantikan sebagian dengan sesi bercerita bersama. Saat anak melihat orang tua asyik membaca buku, mereka akan meniru kebiasaan tersebut secara alami tanpa perlu disuruh-suruh.',
                    '## Jadikan membaca pengalaman menyenangkan',
                    'Gunakan teknologi dengan bijak, misalnya e-book atau audiobook untuk mengisi waktu perjalanan. Kombinasikan dengan aktivitas fisik sederhana setelah membaca, seperti bermain peran atau menggambar tokoh cerita agar pengalaman lebih utuh.',
                    'Wigati Buku menawarkan koleksi buku anak yang menarik sesuai minat dan usia. Temukan buku yang bisa mengalahkan daya tarik layar, dan jadikan rumah Anda tempat tumbuhnya generasi pecinta literasi.',
                ],
            ],
            [
                'title' => 'Resensi Buku Filosofi Teras',
                'category' => 'Pendidikan',
                'content' => [
                    'Filosofi Teras karya Henry Manampiring merevolusi cara banyak orang Indonesia memandang kebahagiaan. Buku ini memperkenalkan ajaran Stoisisme yang diolah dengan konteks lokal sehingga mudah dipahami dan langsung terasa relevan.',
                    'Inti buku ini adalah pemisahan antara hal yang bisa dikontrol dan tidak bisa dikontrol. Dengan berfokus pada hal yang bisa kita kendalikan, kecemasan dan kekecewaan sehari-hari dapat ditekan. Konsep ini disajikan dengan bahasa yang membumi dan jenaka.',
                    'Manampiring mengilustrasikan berbagai situasi keseharian, seperti macet, kritik, hingga masalah pekerjaan, melalui kacamata stoik. Contoh-contoh yang dekat dengan pembaca membuat teori filsafat terasa praktis dan tidak menggurui.',
                    '## Manfaat nyata bagi kesehatan mental',
                    'Membaca buku ini membantu mengelola emosi dan menumbuhkan ketenangan dalam menghadapi masalah. Kehadirannya kian relevan di tengah tekanan hidup modern, dan banyak pembaca mengaku mendapat perspektif baru setelah menyelesaikannya.',
                    'Wigati Buku menyediakan Filosofi Teras dan buku pengembangan diri lainnya. Mulailah perjalanan menata pikiran dan emosi Anda dengan bacaan yang bermakna dan terbukti mengubah cara berpikir.',
                ],
            ],
            [
                'title' => 'Kisah Nabi untuk Anak: Belajar Akhlak Mulia',
                'category' => 'Agama',
                'content' => [
                    'Kisah para nabi adalah bahan bacaan yang sangat bermanfaat untuk menanamkan akhlak mulia pada anak. Setiap kisah mengandung teladan tentang kejujuran, kesabaran, dan keberanian yang bisa menjadi panutan dalam kehidupan sehari-hari.',
                    'Cerita Nabi Nuh yang sabar berdakwah selama ratusan tahun mengajarkan keteguhan. Kisah Nabi Ibrahim mengajarkan kepatuhan pada perintah Allah, sementara kisah Nabi Yusuf mengajarkan keikhlasan dan memaafkan.',
                    'Untuk anak usia dini, sajikan kisah dengan bahasa yang sederhana dan ilustrasi yang ramah. Hindari bagian yang terlalu berat, dan tekankan pesan moralnya. Mendampingi anak saat membaca membantu mereka memahami konteks cerita dengan baik.',
                    '## Hubungkan cerita dengan kehidupan sehari-hari',
                    'Setelah membaca, diskusikan bagaimana nilai dalam kisah bisa diterapkan, misalnya berkata jujur kepada teman atau bersabar saat menunggu giliran. Kaitan langsung antara cerita dan tindakan membuat pembelajaran berkesan.',
                    'Wigati Buku menyediakan buku kisah nabi untuk berbagai usia dengan ilustrasi menarik. Jadikan malam sebagai waktu bercerita keluarga dan tanamkan nilai-nilai keislaman dengan cara yang lembut dan menyenangkan.',
                ],
            ],
            [
                'title' => 'Komik Fantasi Indonesia: Menggali Imajinasi',
                'category' => 'Komik',
                'content' => [
                    'Genre fantasi dalam komik lokal Indonesia sedang naik daun. Karya-karya baru mengangkat mitologi Nusantara, dunia paralel, dan petualangan epik yang memanfaatkan kekayaan budaya sebagai bahan cerita yang segar dan orisinal.',
                    'Keunggulan komik fantasi terletak pada kebebasan imajinasi, baik dari sisi alur, dunia yang diciptakan, maupun desain visual. Setiap panel menawarkan petualangan baru yang membuat pembaca betah menjelajah dari halaman ke halaman.',
                    'Banyak komik fantasi lokal kini dikerjakan dengan standar artistik kelas dunia, dari pewarnaan hingga penulisan dialog. Kolaborasi dengan platform digital juga memperluas jangkauan cerita hingga ke pembaca internasional.',
                    '## Membaca untuk mengembangkan kreativitas',
                    'Membaca fantasi merangsang kreativitas dan kemampuan berpikir naratif. Pembaca diajak membayangkan dunia yang tidak ada, memahami hukum yang berbeda, dan merasakan emosi tokoh yang berjuang melawan kekuatan yang lebih besar.',
                    'Wigati Buku mengkurasi komik fantasi lokal maupun internasional yang seru untuk segala usia. Temukan dunia imajinasi baru di rak Komik dan biarkan imajinasimu terbang tanpa batas.',
                ],
            ],
            [
                'title' => 'Buku Bahasa Inggris untuk Pemula yang Menyenangkan',
                'category' => 'Pendidikan',
                'content' => [
                    'Belajar bahasa Inggris tidak harus membosankan. Buku-buku berbahasa Inggris untuk pemula kini dirancang dengan cerita sederhana, kosakata bertahap, dan ilustrasi pendukung sehingga proses belajar terasa seperti membaca cerita, bukan menghafal.',
                    'Mulailah dari buku dengan tingkat vocabulary yang sesuai kemampuan. Kisah pendek dengan kalimat singkat membantu membangun kepercayaan diri. Bacalah dengan bersuara untuk melatih pelafalan sekaligus memahami alur cerita.',
                    'Pilih buku yang membahas tema favorit Anda, seperti hobi, kuliner, atau perjalanan, agar motivasi tetap terjaga. Catat kosakata baru dan ulangi penggunaannya dalam kalimat sederhana agar lebih mudah diingat.',
                    '## Konsistensi adalah kunci berbahasa',
                    'Luangkan sepuluh hingga lima belas menit setiap hari untuk membaca bahan bahasa Inggris. Kombinasikan dengan menonton video atau mendengar audio agar kemampuan mendengar ikut terlatih. Proses ini perlahan membentuk kefasihan yang alami.',
                    'Wigati Buku menyediakan buku bahasa Inggris untuk pemula, novel graded reader, dan kamus praktis. Mulailah belajar bahasa asing dengan cara yang santai dan tetap terukur progresnya setiap bulan.',
                ],
            ],
            [
                'title' => '8 Novel Klasik Dunia yang Abadi',
                'category' => 'Novel',
                'content' => [
                    'Novel klasik dunia memiliki daya tarik yang tidak lekang oleh waktu. Karya-karya ini tetap dibaca lintas generasi karena menyentuh tema-tema dasar manusia seperti cinta, keserakahan, dan keadilan dengan kedalaman yang tidak pernah basi.',
                    'Judul seperti Pride and Prejudice karya Jane Austen mengupas masyarakat dan prasangka dengan satire yang tajam. Sementara itu, To Kill a Mockingbird karya Harper Lee berbicara tentang moralitas dan keberanian di tengah ketidakadilan sosial.',
                    'Karya seperti The Great Gatsby karya F. Scott Fitzgerald dan 1984 karya George Orwell menawarkan kritik sosial yang tetap relevan. Adapun One Hundred Years of Solitude karya Gabriel García Márquez membawa pembaca ke dalam realisme magis yang memukau.',
                    '## Mulai dari terjemahan yang baik',
                    'Bagi pembaca Indonesia, tersedia terjemahan berkualitas yang memudahkan memahami karya-karya tersebut. Pilih edisi terjemahan dari penerbit terpercaya agar makna dan gaya penulisan aslinya tetap terjaga.',
                    'Wigati Buku menyediakan novel klasik dunia dalam edisi cetak pilihan. Memiliki koleksi klasik bukan sekadar menambah wawasan, tetapi juga menghadirkan kekayaan sastra global di rak buku rumah Anda.',
                ],
            ],
            [
                'title' => 'Pentingnya Literasi Digital di Keluarga',
                'category' => 'Pendidikan',
                'content' => [
                    'Literasi digital tidak hanya soal mampu menggunakan gawai, tetapi juga memahami cara memilah, mengevaluasi, dan menggunakan informasi digital secara bijak. Keterampilan ini menjadi kebutuhan dasar keluarga modern di tengah banjir informasi.',
                    'Orang tua perlu mengajarkan anak memverifikasi kebenaran informasi sebelum membagikannya. Kenalkan perbedaan antara berita, opini, dan iklan, serta bagaimana mengenali indikasi berita palsu. Diskusi terbuka menjadi media belajar yang paling efektif.',
                    'Batasi penggunaan gawai secara seimbang dan dorong aktivitas di dunia nyata. Tetapkan zona bebas layar di rumah, misalnya saat makan bersama, untuk menjaga kualitas interaksi keluarga dan kesehatan mata anak.',
                    '## Peran orang tua sebagai teladan',
                    'Anak belajar dari sikap orang tuanya. Ketika orang tua memeriksa fakta sebelum menyebarkan informasi dan mengatur waktu layarnya sendiri, anak akan meniru kebiasaan tersebut. Kecakapan digital ditularkan melalui contoh, bukan hanya nasihat.',
                    'Wigati Buku menyediakan buku tentang literasi digital, keamanan internet, dan etika bermedia sosial yang cocok untuk orang tua maupun remaja. Bekali keluarga Anda dengan wawasan agar tetap aman dan bijak di dunia digital.',
                ],
            ],
            [
                'title' => 'Doa dan Adab Membaca Buku sesuai Tuntunan Islam',
                'category' => 'Agama',
                'content' => [
                    'Dalam Islam, menuntut ilmu adalah ibadah yang dianjurkan, dan kegiatan membaca buku merupakan bagian dari ikhtiar memperoleh ilmu. Karena itu, memulai dan menyudahi membaca dengan tata cara yang baik bernilai pahala bagi seorang muslim.',
                    'Bacalah doa terlebih dahulu agar ilmu yang diperoleh bermanfaat dan mudah dipahami. Berwudhu dan mengatur posisi duduk yang sopan juga dianjurkan agar suasana belajar lebih khidmat dari awal hingga akhir.',
                    'Saat membaca, jaga adab seperti tidak berbicara atas nama penulis, mencatat pelajaran penting, serta berhenti pada bagian yang belum dipahami untuk ditanyakan kepada orang yang berilmu. Sikap kritis dan rendah hati membuat ilmu semakin berkah.',
                    '## Amalkan ilmu yang telah didapat',
                    'Ilmu yang tidak diamalkan ibarat pohon tanpa buah. Setelah membaca, terapkan manfaatnya dalam kehidupan, bagikan kepada orang lain, dan jadikan kebiasaan belajar sebagai kesempatan beribadah yang menyenangkan.',
                    'Wigati Buku menyediakan buku adab, doa harian, dan materi keislaman lainnya untuk membantu keluarga muslim menjaga kualitas ibadah dan akhlak. Mulailah setiap sesi membaca dengan niat ikhlas menuntut ilmu dalam ketaatan.',
                ],
            ],
            [
                'title' => 'Tips Memilih Buku Anak Sesuai Usia',
                'category' => 'Anak-Anak',
                'content' => [
                    'Memilih buku sesuai usia anak membantu kegiatan membaca berjalan menyenangkan dan efektif. Buku yang terlalu sulit membuat anak frustrasi, sedangkan buku yang terlalu mudah cepat membosankan. Kesesuaian usia menjadi kunci pengalaman membaca yang ideal.',
                    'Untuk anak usia nol hingga tiga tahun, pilih board book dengan gambar besar dan sedikit teks. Pada usia empat hingga enam tahun, perkenalkan cerita bergambar dengan kalimat yang lebih panjang, sembari mulai mengenalkan huruf dan kata sederhana.',
                    'Anak usia tujuh hingga sembilan tahun umumnya siap membaca buku bergambar dengan teks lebih banyak atau mulai berpindah ke novel anak tipis. Sementara usia sepuluh tahun ke atas dapat diperkenalkan pada buku yang lebih kompleks sesuai minatnya.',
                    '## Kenali minat, bukan sekadar usia',
                    'Usia hanyalah panduan kasar; minat anak adalah kompas yang lebih akurat. Perhatikan topik yang paling membuat anak bersemangat dan pilihkan judul yang menantang namun tetap nyaman dibaca. Beri ruang anak untuk memilih sendiri.',
                    'Wigati Buku melengkapi rak buku anak dengan kategori usia yang jelas, sehingga memilih buku menjadi mudah dan tepat sasaran. Temukan bacaan yang tumbuh bersama si kecil dari tahun ke tahun.',
                ],
            ],
            [
                'title' => 'Bookstagram: Cara Menumbuhkan Semangat Membaca',
                'category' => 'Pendidikan',
                'content' => [
                    'Bookstagram adalah aktivitas berbagi pengalaman membaca melalui platform media sosial dengan fokus pada buku. Komunitas ini berkembang pesat karena menyatukan pembaca dari berbagai kalangan dan memperkuat budaya literasi dengan cara yang modern.',
                    'Memulai bookstagram tidak perlu peralatan mahal. Foto buku dengan pencahayaan alami, latar sederhana, dan sedikit properti sudah cukup untuk tampil menarik. Yang terpenting adalah keteraturan unggahan dan kejujuran dalam mengulas buku.',
                    'Bergabung dengan bookstagram membantu Anda menemukan rekomendasi baru yang tak terpikirkan sebelumnya. Respons dari sesama pembaca juga menumbuhkan motivasi, apalagi saat buku yang kita baca dibahas oleh orang lain.',
                    '## Dari hobi menjadi kebiasaan',
                    'Unggahan rutin memaksa Anda untuk konsisten membaca, dan lambat laun berubah menjadi kebiasaan yang sehat. Atur target realistis, misalnya satu ulasan per minggu, agar semangat tidak cepat menguap dan tetap terasa menyenangkan.',
                    'Wigati Buku sering menjadi sumber koleksi para bookstagrammer Indonesia. Jelajahi katalog kami untuk menemukan buku dengan sampul menarik dan konten berkualitas yang layak menjadi unggahan berikutnya.',
                ],
            ],
        ];
    }
}