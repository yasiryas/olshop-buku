<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $buyer;
    private Category $category;
    private Product $product;
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->owner = User::factory()->create(['email' => 'owner-api@test.com']);
        $this->owner->assignRole('owner');
        $this->buyer = User::factory()->create(['email' => 'buyer-api@test.com']);
        $this->buyer->assignRole('buyer');

        $this->category = Category::create([
            'name' => 'Komik',
            'slug' => 'komik',
            'icon' => 'category_icons/komik.svg',
        ]);

        $this->product = Product::create([
            'name' => 'Komik Naruto',
            'slug' => 'komik-naruto',
            'photo' => 'products/naruto.webp',
            'price' => 55000,
            'about' => 'Buku komik',
            'category_id' => $this->category->id,
        ]);

        $this->article = Article::create([
            'title' => 'Resensi Komik',
            'slug' => 'resensi-komik',
            'content' => 'Konten artikel',
            'user_id' => $this->owner->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function test_search_products_returns_json(): void
    {
        $response = $this->getJson('/search-products?search=komik');

        $response->assertOk()->assertJsonCount(1);
        $response->assertJsonPath('0.name', 'Komik Naruto');
    }

    public function test_search_articles_returns_json(): void
    {
        $response = $this->getJson('/search/articles?q=komik');

        $response->assertOk()->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'Resensi Komik');
    }

    public function test_product_edit_data_returns_json_for_owner(): void
    {
        $response = $this->actingAs($this->owner)->getJson("/admin/products/{$this->product->id}/edit-data");

        $response->assertOk()->assertJson([
            'id' => $this->product->id,
            'name' => 'Komik Naruto',
            'price' => '55000',
            'about' => 'Buku komik',
        ]);
    }

    public function test_category_edit_data_returns_json_for_owner(): void
    {
        $response = $this->actingAs($this->owner)->getJson("/admin/categories/{$this->category->id}/edit-data");

        $response->assertOk()->assertJson(['id' => $this->category->id, 'name' => 'Komik']);
    }

    public function test_article_edit_data_returns_json_for_owner(): void
    {
        $response = $this->actingAs($this->owner)->getJson("/admin/articles/{$this->article->id}/edit-data");

        $response->assertOk()->assertJson(['id' => $this->article->id, 'title' => 'Resensi Komik']);
    }

    public function test_edit_data_forbidden_for_wrong_role(): void
    {
        $response = $this->actingAs($this->buyer)->getJson("/admin/products/{$this->product->id}/edit-data");

        $response->assertForbidden()->assertJsonStructure(['message']);
    }

    public function test_cart_rates_returns_json_for_buyer(): void
    {
        Cart::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->buyer)->getJson('/cart/rates?city_id=1');

        $response->assertOk()->assertJsonIsArray();
    }

    public function test_cart_rates_forbidden_for_owner(): void
    {
        $response = $this->actingAs($this->owner)->getJson('/cart/rates?city_id=1');

        $response->assertForbidden()->assertJsonStructure(['message']);
    }
}