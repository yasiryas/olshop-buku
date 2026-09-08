<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DebugLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_contains_logout_confirm(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $html = $response->getContent();
        echo "STATUS OK, logout-confirm count: " . substr_count($html, 'x-logout-confirm') . "\n";
        echo "x-cloak present: " . (strpos($html, 'x-cloak') !== false ? 'yes' : 'no') . "\n";
    }
}