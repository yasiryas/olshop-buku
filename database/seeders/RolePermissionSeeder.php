<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    protected const DEFAULT_PASSWORD = '12345678';

    private const PERMISSIONS = [
        'view dashboard',
        'manage products',
        'manage categories',
        'manage articles',
        'manage stocks',
        'process orders',
        'manage customers',
        'manage staff',
        'view reports',
        'manage settings',
        'process returns',
    ];

    private const ROLE_PERMISSIONS = [
        'owner' => [
            'view dashboard',
            'manage products',
            'manage categories',
            'manage articles',
            'manage stocks',
            'process orders',
            'view reports',
            'manage staff',
            'manage settings',
            'process returns',
        ],
        'admin' => [
            'view dashboard',
            'manage products',
            'manage categories',
            'manage stocks',
            'process orders',
            'view reports',
            'manage customers',
            'process returns',
        ],
        'penulis' => [
            'view dashboard',
            'manage articles',
        ],
        'buyer' => [],
    ];

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
                'is_active' => $data['is_active'] ?? true,
            ]
        );
        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roleModels = [];
        foreach (array_keys(self::ROLE_PERMISSIONS) as $role) {
            $roleModel = $this->seedRole($role);
            $roleModel->syncPermissions(self::ROLE_PERMISSIONS[$role]);
            $roleModels[$role] = $roleModel;
        }

        $users = [
            ['name' => 'Owner Wigati', 'email' => 'owner@mail.com', 'role' => 'owner'],
            ['name' => 'Admin Wigati', 'email' => 'admin@mail.com', 'role' => 'admin'],
            ['name' => 'Penulis Wigati', 'email' => 'penulis@mail.com', 'role' => 'penulis'],
            ['name' => 'Buyer Wigati', 'email' => 'buyer@mail.com', 'role' => 'buyer'],
            ['name' => 'Danang', 'email' => 'danang@mail.com', 'role' => 'buyer'],
            ['name' => 'Siti Rahma', 'email' => 'rahma@mail.com', 'role' => 'buyer'],
            ['name' => 'Budi Santoso', 'email' => 'budi@mail.com', 'role' => 'buyer'],
            ['name' => 'Ria Anggraini', 'email' => 'ria@mail.com', 'role' => 'buyer'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi@mail.com', 'role' => 'buyer'],
            ['name' => 'Maya Lestari', 'email' => 'maya@mail.com', 'role' => 'buyer'],
            ['name' => 'Joko Susilo', 'email' => 'joko@mail.com', 'role' => 'buyer'],
            ['name' => 'Nina Wulandari', 'email' => 'nina@mail.com', 'role' => 'buyer'],
        ];

        foreach ($users as $user) {
            $this->seedUser($user);
        }
    }
}