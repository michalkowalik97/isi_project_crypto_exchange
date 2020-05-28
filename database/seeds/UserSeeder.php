<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => '44kowal@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin'
        ]);

        DB::table('users')->insert([
            'name' => 'tester',
            'email' => 'test@test.com',
            'password' => Hash::make('guest123'),
            'role' => 'user'
        ]);
    }
}
