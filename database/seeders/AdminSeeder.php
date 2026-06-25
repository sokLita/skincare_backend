<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'rina@gmail.com'],
            [
                'name'     => 'Rina Lin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
    }
}
