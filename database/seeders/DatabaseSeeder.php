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
        $this->call([
            RoleSeeder::class,
        ]);

        // Create a test client
        $user = User::factory()->create([
            'nom' => 'Test Client',
            'email' => 'client@example.com',
        ]);
        $user->assignRole('CLIENT');

        // Create a test admin restaurant
        $admin = User::factory()->create([
            'nom' => 'Test Admin Restaurant',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('ADMIN_RESTAURANT');
    }
}
