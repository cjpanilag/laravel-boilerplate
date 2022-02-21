<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'super admin',
            'first_name' => 'super admin',
            'last_name' => 'admin',
            'username' => 'superadmin',
            'user_type' => 'admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('adminpassword'),
        ]);
        // \App\Models\User::factory(10)->create();
    }
}
