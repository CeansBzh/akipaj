<?php

namespace App\Enums;

/**
 * Ordres des photos de la galerie, selon la date de prise de vue. La valeur est celle écrite dans l'adresse (?tri=anciennes).
 */
enum GallerySortEnum: string
{
    case NEWEST = 'recentes';
    case OLDEST = 'anciennes';

    /**
     * Libellé de l'ordre dans la liste « Trier ».
     */
    public function label(): string
    {
        return match ($this) {
            GallerySortEnum::NEWEST => 'Plus récentes',
            GallerySortEnum::OLDEST => 'Plus anciennes',
        };
    }

    /**
     * Sens du tri SQL.
     */
    public function direction(): string
    {
        return match ($this) {
            GallerySortEnum::NEWEST => 'desc',
            GallerySortEnum::OLDEST => 'asc',
        };
    }
}
