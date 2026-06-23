<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
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
            StoreSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            SystemSettingSeeder::class,
        ]);

        $users = [
            [
                'full_name' => 'SEGreens Superuser',
                'email' => 'superuser@segreens.test',
                'username' => 'segreens.superuser',
                'phone_number' => '081234567890',
                'avatar' => 'https://loremflickr.com/512/512/portrait,admin?lock=7001',
                'fcm_token' => 'fcm-seeder-superuser-demo-token',
                'role_code' => UserRole::Superuser,
                'email_verified_at' => now(),
            ],
            [
                'full_name' => 'SEGreens Admin',
                'email' => 'admin@segreens.test',
                'username' => 'segreens.admin',
                'phone_number' => '081234567891',
                'avatar' => 'https://loremflickr.com/512/512/portrait,admin?lock=7002',
                'fcm_token' => 'fcm-seeder-admin-demo-token',
                'role_code' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
            [
                'full_name' => 'SEGreens User',
                'email' => 'user@segreens.test',
                'username' => 'segreens.user',
                'phone_number' => '081234567892',
                'avatar' => 'https://loremflickr.com/512/512/portrait,user?lock=7003',
                'fcm_token' => 'fcm-seeder-user-demo-token',
                'role_code' => UserRole::User,
                'email_verified_at' => now(),
            ],
            [
                'full_name' => 'SEGreens User Unverified',
                'email' => 'unverified@segreens.test',
                'username' => 'segreens.unverified',
                'phone_number' => '081234567893',
                'avatar' => 'https://loremflickr.com/512/512/portrait,user?lock=7004',
                'fcm_token' => 'fcm-seeder-user-unverified-demo-token',
                'role_code' => UserRole::User,
                'email_verified_at' => null,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'status_code' => UserStatus::Active,
                    'password' => 'password',
                ],
            );
        }
    }
}
