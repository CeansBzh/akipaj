{{--
    Guirlande de pavillons de signalisation dessinée au trait, en haut de la liste des matelots.
    Deux festons de cinq pavillons forment un motif répété sur toute la largeur, centré sur le milieu de l'écran.
    Purement décorative.
--}}

<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class(['h-24 w-full']) }}>
    <defs>
        <pattern id="signal-flags" patternUnits="userSpaceOnUse" width="720" height="96" x="50%"
            patternTransform="translate(-180 0)">
            <g class="stroke-primary-800" stroke-width="1.25" stroke-linejoin="round">
                <path class="fill-none" d="M0 8Q180 64 360 8"/>
                <path class="fill-white stroke-none" d="M28.8 16.2L72 25.9L72 59.9L28.8 50.2Z"/>
                <path class="fill-primary-300 stroke-none" d="M46.1 20.1L54.7 22L54.7 56L46.1 54.1Z"/>
                <path class="fill-primary-300 stroke-none" d="M28.8 29.2L72 38.8L72 47L28.8 37.3Z"/>
                <path class="fill-none" d="M28.8 16.2L72 25.9L72 59.9L28.8 50.2Z"/>
                <path class="fill-white stroke-none" d="M93.6 29.5L136.8 34.4L115.2 76Z"/>
                <circle class="fill-primary-300 stroke-none" cx="115.2" cy="45.2" r="5.6"/>
                <path class="fill-none" d="M93.6 29.5L136.8 34.4L115.2 76Z"/>
                <path class="fill-white stroke-none" d="M158.4 35.6L201.6 35.6L201.6 69.6L158.4 69.6Z"/>
                <path class="fill-primary-300 stroke-none" d="M201.6 35.6L201.6 69.6L158.4 69.6Z"/>
                <path class="fill-none" d="M158.4 35.6L201.6 35.6L201.6 69.6L158.4 69.6Z"/>
                <path class="fill-white stroke-none" d="M223.2 34.4L266.4 29.5L266.4 69.5L244.8 60.8L223.2 74.4Z"/>
                <path class="fill-primary-300 stroke-none" d="M223.2 34.4L244.8 32L244.8 66.4L223.2 74.4Z"/>
                <path class="fill-none" d="M223.2 34.4L266.4 29.5L266.4 69.5L244.8 60.8L223.2 74.4Z"/>
                <path class="fill-white stroke-none" d="M288 25.9L331.2 16.2L331.2 50.2L288 59.9Z"/>
                <path class="fill-primary-300 stroke-none" d="M301 33.2L318.2 29.3L318.2 42.9L301 46.8Z"/>
                <path class="fill-none" d="M288 25.9L331.2 16.2L331.2 50.2L288 59.9Z"/>
                <path class="fill-none" d="M360 8Q540 64 720 8"/>
                <path class="fill-white stroke-none" d="M388.8 16.2L432 25.9L432 59.9L388.8 50.2Z"/>
                <path class="fill-primary-300 stroke-none" d="M396 17.9L403.2 19.5L403.2 53.5L396 51.9Z"/>
                <path class="fill-primary-300 stroke-none" d="M410.4 21.1L417.6 22.7L417.6 56.7L410.4 55.1Z"/>
                <path class="fill-primary-300 stroke-none" d="M424.8 24.3L432 25.9L432 59.9L424.8 58.3Z"/>
                <path class="fill-none" d="M388.8 16.2L432 25.9L432 59.9L388.8 50.2Z"/>
                <path class="fill-white stroke-none" d="M453.6 29.5L496.8 34.4L475.2 76Z"/>
                <path class="fill-primary-300 stroke-none" d="M460.1 43.5L490.3 46.9L486 55.2L464.4 52.8Z"/>
                <path class="fill-none" d="M453.6 29.5L496.8 34.4L475.2 76Z"/>
                <path class="fill-white stroke-none" d="M518.4 35.6L561.6 35.6L561.6 69.6L518.4 69.6Z"/>
                <path class="fill-primary-300 stroke-none" d="M518.4 35.6L540 35.6L540 52.6L518.4 52.6Z"/>
                <path class="fill-primary-300 stroke-none" d="M540 52.6L561.6 52.6L561.6 69.6L540 69.6Z"/>
                <path class="fill-none" d="M518.4 35.6L561.6 35.6L561.6 69.6L518.4 69.6Z"/>
                <path class="fill-white stroke-none" d="M583.2 34.4L626.4 29.5L626.4 63.5L583.2 68.4Z"/>
                <path class="fill-primary-300 stroke-none" d="M583.2 34.4L590.1 33.6L626.4 58.1L626.4 63.5L619.5 64.3L583.2 39.8Z"/>
                <path class="fill-primary-300 stroke-none" d="M626.4 29.5L626.4 35L590.1 67.6L583.2 68.4L583.2 62.9L619.5 30.3Z"/>
                <path class="fill-none" d="M583.2 34.4L626.4 29.5L626.4 63.5L583.2 68.4Z"/>
                <path class="fill-white stroke-none" d="M648 25.9L691.2 16.2L691.2 56.2L669.6 49.9L648 65.9Z"/>
                <path class="fill-primary-300 stroke-none" d="M648 25.9L691.2 16.2L691.2 32.2L648 41.9Z"/>
                <path class="fill-none" d="M648 25.9L691.2 16.2L691.2 56.2L669.6 49.9L648 65.9Z"/>
                <circle class="fill-primary-50" cx="0" cy="8" r="3"/>
                <circle class="fill-primary-50" cx="360" cy="8" r="3"/>
                <circle class="fill-primary-50" cx="720" cy="8" r="3"/>
            </g>
        </pattern>
    </defs>
    <rect width="100%" height="100%" fill="url(#signal-flags)" />
</svg>
