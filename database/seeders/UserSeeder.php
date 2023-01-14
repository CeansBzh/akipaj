<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // If the roles are not created yet, we create them.
        $this->call([RoleSeeder::class]);

        $admin = User::factory()->create([
            'name' => 'Michel',
            'email' => 'michel@test.com',
        ]);
        $admin->attachRole('admin');
        $admin->attachRole('member');

        $member = User::factory()->create([
            'name' => 'Henry',
            'email' => 'henry@test.com',
        ]);
        $member->attachRole('member');
    }
}
