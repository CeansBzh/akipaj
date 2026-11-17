<?php

namespace App\Enums;

enum BoatTypeEnum: string
{
    case MONOHULL = 'monohull';
    case KETCH = 'ketch';
    case CATAMARAN = 'catamaran';

    /**
     * Donne le nom du type, tel qu'il est écrit dans l'interface.
     */
    public function label(): string
    {
        return match ($this) {
            BoatTypeEnum::MONOHULL => 'Monocoque',
            BoatTypeEnum::KETCH => 'Monocoque à deux mâts (ketch)',
            BoatTypeEnum::CATAMARAN => 'Catamaran',
        };
    }

    /**
     * Donne le plan au trait qui représente ce type (composant `x-boat-drawing.*`).
     */
    public function drawing(): string
    {
        return match ($this) {
            BoatTypeEnum::MONOHULL => 'sloop',
            BoatTypeEnum::KETCH => 'ketch',
            BoatTypeEnum::CATAMARAN => 'catamaran',
        };
    }
}
