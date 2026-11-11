<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Mise en page des formulaires d'accès au compte (connexion, inscription, mot de passe oublié…) : le formulaire seul
 * au centre, sous le logo et le titre de la page, avec un lien de retour à l'accueil et une côte au trait en décor.
 */
class AuthLayout extends Component
{
    /**
     * @param  string  $title  Titre de la page, repris dans l'onglet du navigateur.
     */
    public function __construct(public string $title) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('layouts.auth');
    }
}
