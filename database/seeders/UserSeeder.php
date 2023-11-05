<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'John Drexler',
            'email' => 'john@email.gov',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Daniel Coulbourne',
            'email' => 'd@coulb.com',
            'password' => bcrypt('password'),
        ]);
    }
}
