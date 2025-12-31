<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // غيّر 1 إلى الـ id الخاص بالمستخدم الذي تريد تعيينه كـ admin
        $user = User::find(1);

        if (! $user) {
            $this->command->info('User not found.');
            return;
        }

        $user->role = 'admin'; // أو is_admin = 1 حسب جدولك
        $user->save();

        $this->command->info("User {$user->email} is now admin.");
    }
}
