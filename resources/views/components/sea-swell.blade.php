{{--
    Houle au trait, vue en perspective : des lignes de mer de plus en plus serrées vers l'horizon, les vagues de devant
    cachant celles de derrière, et un petit voilier au loin. Image générée, purement décorative.
    Le dessin (resources/images/sea-swell.svg) prend toute la hauteur de l'élément et se répète sans couture
    sur sa largeur, centré : il n'est jamais rogné en haut, quelle que soit la largeur de l'écran (classe .sea-swell).
    Les traits prennent la couleur du texte et gardent leur épaisseur ; entre eux, on voit le fond de la page.
--}}

<div aria-hidden="true" {{ $attributes->class('sea-swell') }}></div>
