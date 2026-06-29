<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser('admin', 'Admin Snack Eclat', 'admin@snack-eclat.local', 'admin');
        $this->seedUser('owner', 'Owner Snack Eclat', 'owner@snack-eclat.local', 'owner');
    }

    private function seedUser(string $username, string $name, string $email, string $level): User
    {
        $user = User::query()
            ->where('username', $username)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = new User();
        }

        $user->forceFill([
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'level' => $level,
        ])->save();

        return $user;
    }
}
