import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        // Classes used by the alert component
        {
            pattern: /ring-(red|green|blue|yellow)-400/,
            variants: ['focus'],
        },
        {
            pattern: /text-(red|green|blue|yellow)-(500|700)/,
        },
        {
            pattern: /bg-(red|green|blue|yellow)-(100|200)/,
            variants: ['hover'],
        },
    ],
    theme: {
        screens: {
            'xs': '475px',
            ...defaultTheme.screens,
        },
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                merriweather: ['Merriweather', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
