<?php

namespace Tests\Feature;

use App\Models\ProductTransaction;
use App\Models\ProductReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): object
    {
        return \App\Models\User::where('email', $email)->firstOrFail();
    }

    public function test_owner_all_pages_render(): void
    {
        $this->actingAs($this->user('owner@mail.com'));

        $pages = [
            '/dashboard',
            '/admin/products',
            '/admin/categories',
            '/admin/articles',
            '/admin/staff',
            '/admin/stocks',
            '/admin/stocks/history',
            '/admin/customers',
            '/admin/reports',
            '/admin/settings',
            '/product_transactions',
            '/admin/returns',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }

        $customer = \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'buyer'))->firstOrFail();
        $this->get("/admin/customers/{$customer->id}")->assertOk();
    }

    public function test_admin_pages_and_forbidden_staff_settings(): void
    {
        $this->actingAs($this->user('admin@mail.com'));

        foreach (['/dashboard', '/admin/products', '/admin/categories', '/admin/stocks', '/admin/stocks/history', '/admin/customers', '/admin/reports', '/product_transactions', '/admin/returns'] as $page) {
            $this->get($page)->assertOk();
        }

        $this->get('/admin/staff')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/articles')->assertForbidden();
    }

    public function test_penulis_pages_and_forbidden_products(): void
    {
        $this->actingAs($this->user('penulis@mail.com'));

        $this->get('/dashboard')->assertOk();
        $this->get('/admin/articles')->assertOk();
        $this->get('/admin/products')->assertForbidden();
    }

    public function test_buyer_front_and_order_pages(): void
    {
        $this->actingAs($this->user('buyer@mail.com'));

        foreach (['/', '/product', '/blog', '/about', '/contact', '/search'] as $page) {
            $this->get($page)->assertOk();
        }

        $this->get('/profile')->assertOk();
        $this->get('/carts')->assertOk();
        $this->get('/product_transactions')->assertOk();

        $own = ProductTransaction::where('user_id', $this->user('buyer@mail.com')->id)->first();
        if ($own) {
            $this->get("/product_transactions/{$own->id}")->assertOk();
        }
    }

    public function test_ajax_search_endpoints_return_partials(): void
    {
        $this->actingAs($this->user('owner@mail.com'));

        $endpoints = [
            '/product_transactions?search=1',
            '/admin/products?search=buku',
            '/admin/categories?search=novel',
            '/admin/staff?search=',
            '/admin/returns?search=',
            '/admin/stocks?search=',
            '/admin/stocks/history?search=',
        ];

        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $this->assertTrue($response->status() === 200, 'Ajax failed: ' . $url . ' => ' . $response->status());
            $this->assertStringContainsString('<td', $response->getContent());
        }

        $this->getJson('/admin/reports?from=2025-01-01&to=2025-12-31')->assertOk();
    }

    public function test_order_and_return_workflows(): void
    {
        $owner = $this->user('owner@mail.com');
        $this->actingAs($owner);

        $pending = ProductTransaction::where('status', ProductTransaction::STATUS_PENDING)->firstOrFail();
        $this->post("/admin/orders/{$pending->id}/approve")->assertRedirect();
        $pending->refresh();
        $this->assertSame(ProductTransaction::STATUS_PROCESSING, $pending->status);

        $this->post("/admin/orders/{$pending->id}/ship", ['tracking_number' => 'JNE123456'])->assertRedirect();
        $pending->refresh();
        $this->assertSame(ProductTransaction::STATUS_SHIPPED, $pending->status);

        $this->post("/admin/orders/{$pending->id}/complete")->assertRedirect();
        $pending->refresh();
        $this->assertSame(ProductTransaction::STATUS_COMPLETED, $pending->status);

        $newPending = ProductTransaction::where('status', ProductTransaction::STATUS_PENDING)->firstOrFail();
        $this->post("/admin/orders/{$newPending->id}/reject", ['rejection_note' => 'Stok habis'])->assertRedirect();
        $newPending->refresh();
        $this->assertSame(ProductTransaction::STATUS_REJECTED, $newPending->status);

        $requested = ProductReturn::where('status', ProductReturn::STATUS_REQUESTED)->first();
        if ($requested) {
            $order = $requested->transaction;
            $this->post("/admin/returns/{$requested->id}/approve")->assertRedirect();
            $requested->refresh();
            $this->assertSame(ProductReturn::STATUS_APPROVED, $requested->status);
            $order->refresh();
            $this->assertSame(ProductTransaction::STATUS_RETURNED, $order->status);
        }
    }

    public function test_report_export_returns_xlsx_named_laporan_wigati(): void
    {
        $this->actingAs($this->user('owner@mail.com'));

        $response = $this->get('/admin/reports/export?from=2025-01-01&to=2025-12-31');
        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Laporan-Wigati-Buku', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);

        $content = $response->getContent();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
    }

    public function test_cart_rates_ajax_for_buyer(): void
    {
        $this->actingAs($this->user('buyer@mail.com'));

        $buyer = $this->user('buyer@mail.com');
        if ($buyer->carts()->count() === 0) {
            \App\Models\Cart::create([
                'user_id' => $buyer->id,
                'product_id' => \App\Models\Product::first()->id,
                'quantity' => 1,
            ]);
        }

        $this->getJson('/cart/rates?city_id=39')->assertStatus(200);
    }

    public function test_sitemap_robots_and_seo_pages_render(): void
    {
        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk()->assertHeader('Content-Type', 'application/xml');
        $sitemapContent = $sitemap->getContent();
        $this->assertStringContainsString('<urlset', $sitemapContent);

        $robots = $this->get('/robots.txt');
        $robots->assertOk();
        $this->assertStringContainsString('Sitemap:', $robots->getContent());

        $product = \App\Models\Product::firstOrFail();
        $this->get("/product/{$product->slug}")->assertOk();

        $article = \App\Models\Article::where('is_published', true)->firstOrFail();
        $this->get("/article/{$article->slug}")->assertOk();

        $this->get('/search?search=buku')
            ->assertOk()
            ->assertSee('noindex, follow', false);
    }
}