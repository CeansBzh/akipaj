<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $memberRole = Role::where('name', 'member')->first();

        $admin = User::factory()->create([
            'name' => 'Michel',
            'email' => 'michel@test.com',
        ]);
        $admin->roles()->attach($memberRole);
        $admin->roles()->attach($adminRole);

        $member = User::factory()->create([
            'name' => 'Henry',
            'email' => 'henry@test.com',
        ]);
        $member->roles()->attach($memberRole);
    }
}
