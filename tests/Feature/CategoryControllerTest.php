<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function actingAdmin(): User
    {
        return User::where('email', 'admin@mail.com')->firstOrFail();
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->actingAdmin())->delete("/admin/categories/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['category_id' => $category->id]);
    }

    public function test_category_with_articles_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Article::create([
            'title' => 'Judul',
            'content' => 'Isi',
            'slug' => 'judul-artikel',
            'featured_image' => null,
            'user_id' => $this->actingAdmin()->id,
            'category_id' => $category->id,
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->actingAdmin())->delete("/admin/categories/{$category->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->actingAdmin())->delete("/admin/categories/{$category->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_database_blocks_deleting_category_with_products(): void
    {
        $this->expectException(QueryException::class);

        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $category->delete();
    }
}