// import _ from 'lodash';
// window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

// import axios from 'axios';
// window.axios = axios;

// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

/**
 * Ouvre la liste des agendas où ajouter un événement, sous le bouton cliqué.
 * La bibliothèque add-to-calendar-button est lourde : elle n'est téléchargée qu'au premier clic,
 * et le bouton reste désactivé pendant ce téléchargement.
 *
 * @param {object} config Options d'add-to-calendar-button décrivant l'événement
 * @param {HTMLButtonElement} button Bouton cliqué, sous lequel la liste s'affiche
 */
window.addToAgenda = async (config, button) => {
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        const [{ atcb_action }] = await Promise.all([
            import('add-to-calendar-button'),
            import('add-to-calendar-button/i18n/fr'),
        ]);
        atcb_action(config, button);
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
};
