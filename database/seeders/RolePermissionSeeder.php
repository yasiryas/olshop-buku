<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    protected const DEFAULT_PASSWORD = '12345678';

    protected function seedRole(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name]);
    }

    protected function seedUser(array $data): User
    {
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => bcrypt($data['password'] ?? self::DEFAULT_PASSWORD),
            ]
        );
        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function run(): void
    {
        $roles = ['owner', 'admin', 'penulis', 'buyer'];
        $roleModels = [];
        foreach ($roles as $role) {
            $roleModels[$role] = $this->seedRole($role);
        }

        $users = [
            ['name' => 'Owner Wigati', 'email' => 'owner@mail.com', 'role' => 'owner'],
            ['name' => 'Admin Wigati', 'email' => 'admin@mail.com', 'role' => 'admin'],
            ['name' => 'Penulis Wigati', 'email' => 'penulis@mail.com', 'role' => 'penulis'],
            ['name' => 'Buyer Wigati', 'email' => 'buyer@mail.com', 'role' => 'buyer'],
            ['name' => 'Danang', 'email' => 'danang@mail.com', 'role' => 'buyer'],
            ['name' => 'Siti Rahma', 'email' => 'rahma@mail.com', 'role' => 'buyer'],
            ['name' => 'Budi Santoso', 'email' => 'budi@mail.com', 'role' => 'buyer'],
        ];

        foreach ($users as $user) {
            $this->seedUser($user);
        }
    }
}