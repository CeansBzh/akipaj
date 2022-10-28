<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'description' => 'Le rôle le plus élevé, est autorisé à tout faire.',
        ]);

        Role::create([
            'name' => 'member',
            'display_name' => 'Membre',
            'description' => 'Est autorisé à consulter le site de manière complète (photos, commentaires, etc.).',
        ]);

        Role::create([
            'name' => 'guest',
            'display_name' => 'Visiteur',
            'description' => 'Utilisateur n\'ayant pas un compte validé par l\'administrateur. Ne peut pas utiliser le site comme un membre.',
        ]);

        // TODO Enum rôles (si possible) et ajout rôle bloqué (les guests refusés par l'admin)
    }
}
