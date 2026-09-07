<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Buat roles (hindari duplicate)
        $ownerRole   = Role::firstOrCreate(['name' => 'owner']);
        $buyerRole   = Role::firstOrCreate(['name' => 'buyer']);
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $penulisRole = Role::firstOrCreate(['name' => 'penulis']);

        // Buat user default dan assign role
        $owner = User::updateOrCreate(
            ['email' => 'owner@mail.com'],
            ['name' => 'Owner', 'password' => bcrypt('12345678')]
        );
        $owner->syncRoles([$ownerRole]); // pastikan hanya role ini

        $admin = User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            ['name' => 'Admin', 'password' => bcrypt('12345678')]
        );
        $admin->syncRoles([$adminRole]); // pastikan role admin

        $buyer = User::updateOrCreate(
            ['email' => 'buyer@mail.com'],
            ['name' => 'Buyer', 'password' => bcrypt('12345678')]
        );
        $buyer->syncRoles([$buyerRole]);

        $penulis = User::updateOrCreate(
            ['email' => 'penulis@mail.com'],
            ['name' => 'Penulis', 'password' => bcrypt('12345678')]
        );
        $penulis->syncRoles([$penulisRole]);

        $danang = User::updateOrCreate(
            ['email' => 'danang@mail.com'],
            ['name' => 'Danang', 'password' => bcrypt('12345678')]
        );
        $danang->syncRoles([$buyerRole]);
    }
}
