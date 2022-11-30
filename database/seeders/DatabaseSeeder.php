<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            'first_name' => 'John Doe',
            'last_name' => 'john@gmail.com',
            'user_name' => 'john@gmail.com',
            'current_balance' => '0.0',
            'current_commission' => '0.0',
            'commission_percent' => '0.0',
            'email' => 'john@gmail.com',
            'phone_number' => '09519555366',
            'agent_code' => 'A1B2C3',
            'player_code' => 'A1B2C3',
            'status' => 'A1B2C3',
            'role_type' => 'A1B2C3',
            'avatar' => 'A1B2C3',
        ]);
        
        //  \App\Models\User::factory(10)->create();
        \App\Models\Event::factory(20)->create([
            'user_id' => $user->id
        ]);
    }
}
