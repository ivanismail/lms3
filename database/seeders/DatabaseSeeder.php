<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $roles = [
            [
                'id' => Role::ADMIN,
                'name' => 'Admin',
            ],
            [
                'id' => Role::USER,
                'name' => 'User',
            ],
        ];

        foreach ($roles as $role) {

            Role::createOrFirst([
                'id' => $role['id'],
            ], [
                'name' => $role['name'],
            ]);
        }

        User::createOrFirst([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('adminadmin'),
            'role_id' => Role::ADMIN
        ]);
    }
}
