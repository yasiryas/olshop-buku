<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_owner_login_redirects_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'owner@mail.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect(url('/dashboard'));
    }

    public function test_role_based_page_access(): void
    {
        $admin = User::where('email', 'admin@mail.com')->firstOrFail();
        $penulis = User::where('email', 'penulis@mail.com')->firstOrFail();
        $owner = User::where('email', 'owner@mail.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($penulis)->get('/admin/products')->assertForbidden();
        $this->actingAs($owner)->get('/admin/products')->assertOk();

        $this->actingAs($admin)->get('/admin/articles')->assertForbidden();
        $this->actingAs($penulis)->get('/admin/articles')->assertOk();
        $this->actingAs($owner)->get('/admin/articles')->assertOk();

        $this->actingAs($penulis)->get('/product_transactions')->assertForbidden();
        $this->actingAs($admin)->get('/product_transactions')->assertOk();
        $this->actingAs($owner)->get('/product_transactions')->assertOk();
    }
}