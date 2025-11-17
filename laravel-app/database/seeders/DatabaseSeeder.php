<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // You can uncomment the lines below to seed sample data
        // \App\Models\User::factory(10)->create();

        // Create a test user
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Note: No seed data defined yet. Add seeders in database/seeders/');
    }
}
