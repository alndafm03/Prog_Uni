<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);
        // غيّر الرقم حسب المستخدم المطلوب
        if (! $user) {
            $this->command->info('User not found.');
            return;
        }
        if ($user->role === 'admin') {
            $this->command->info('User already admin.');
            return;
        }
        $user->role = 'admin';
        $user->save();
        $this->command->info("User {$user->email} is now admin.");
    }
}
