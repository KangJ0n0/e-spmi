<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $user = [
            [
                'id' => '728008c9-b151-41c2-a51d-4ace429cbb54',
                'password' => bcrypt(env('DEFAULT_PASSWORD', 'password')),
                'login_name' => 'ADMIN',
                'is_active' => '1',
                'created_at' => new \DateTime,
                'updated_at' => null,
            ]
         
       

        ];

        \DB::table('userlogin')->insert($user);
    }
}
