<?php

namespace App\Enums;

/**
 * Façons de regrouper les photos de la galerie. La valeur est celle écrite dans l'adresse (?vue=album).
 */
enum GalleryGroupingEnum: string
{
    case DAY = 'jour';
    case ALBUM = 'album';
    case NONE = 'toutes';

    /**
     * Libellé du bouton qui choisit ce regroupement.
     */
    public function label(): string
    {
        return match ($this) {
            GalleryGroupingEnum::DAY => 'Par jour',
            GalleryGroupingEnum::ALBUM => 'Par album',
            GalleryGroupingEnum::NONE => 'Toutes',
        };
    }
}
