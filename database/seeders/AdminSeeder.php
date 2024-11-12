<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin; // Make sure you import the Admin model

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Admin::where('email', 'admin@example.com')->doesntExist()) {
            Admin::create([
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'), // Static password (hashed)
            ]);
        }
    }
}
