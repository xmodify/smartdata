<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'นายศิริฤกษ์ คณาดี',
            'username' => '1341800003078',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'active' => 'Y',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'name' => 'User Test',
            'username' => 'user01',
            'password' => bcrypt('12345678'),
            'role' => 'user',
            'active' => 'Y',
            'email' => 'user@example.com',
        ]);
    }
}
