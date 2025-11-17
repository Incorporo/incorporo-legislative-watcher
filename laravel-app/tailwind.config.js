import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'SF Pro Display', '-apple-system', 'BlinkMacSystemFont', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'apple-black': {
                    50: '#f8f8f8',
                    100: '#f0f0f0',
                    200: '#e0e0e0',
                    300: '#c0c0c0',
                    400: '#a0a0a0',
                    500: '#6e6e6e',
                    600: '#4a4a4a',
                    700: '#2d2d2d',
                    800: '#1a1a1a',
                    900: '#0a0a0a',
                    950: '#000000',
                },
            },
            borderRadius: {
                'apple': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
