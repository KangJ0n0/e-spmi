<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        $roles = [
            [
                'user_id' => "728008c9-b151-41c2-a51d-4ace429cbb54",
                'role_id' => '1',

            ]
     
        
    
    ];
    
            \DB::table('role_user')->insert($roles);

}

}