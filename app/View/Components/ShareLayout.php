<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Mise en page des liens de partage, ouverts sans compte : ni navigation ni pied de page, rien qui mène au reste du
 * site. L'aperçu du lien dans les messageries (Open Graph) montre la photo ou la couverture de l'album.
 */
class ShareLayout extends Component
{
    /**
     * @param  string  $title  Titre de la page, repris dans l'onglet du navigateur et l'aperçu du lien.
     * @param  ?string  $image  Adresse de l'image de l'aperçu du lien.
     * @param  bool  $dark  Fond noir, pour la photo seule dans la visionneuse.
     */
    public function __construct(
        public string $title,
        public ?string $image = null,
        public bool $dark = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('layouts.share');
    }
}
