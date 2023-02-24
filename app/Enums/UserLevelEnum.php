<?php

namespace App\Enums;

enum UserLevelEnum: int
{
    case GUEST = 0;
    case MEMBER = 1;
    case CONTRIBUTOR = 10;
    case ADMINISTRATOR = 99;

    public function label(): string
    {
        return match ($this) {
            UserLevelEnum::GUEST => 'Visiteur',
            UserLevelEnum::MEMBER => 'Membre',
            UserLevelEnum::CONTRIBUTOR => 'Contributeur',
            UserLevelEnum::ADMINISTRATOR => 'Administrateur',
        };
    }

    public function description(): string
    {
        return match ($this) {
            UserLevelEnum::GUEST
                => 'Utilisateur n\'ayant pas un compte validé par l\'administrateur. Ne peut pas utiliser le site comme un membre.',
            UserLevelEnum::MEMBER
                => 'Est autorisé à consulter le site de manière complète (photos, commentaires, etc.).',
            UserLevelEnum::CONTRIBUTOR => 'Un contributeur peut écrire des articles.',
            UserLevelEnum::ADMINISTRATOR => 'Le rôle le plus élevé, est autorisé à tout faire.',
        };
    }


}
