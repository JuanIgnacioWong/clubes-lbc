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
            colors: {
                brand: {
                    50: '#eef8ff',
                    100: '#d9efff',
                    200: '#bce3ff',
                    300: '#8ed2ff',
                    400: '#59b6ff',
                    500: '#2e95f5',
                    600: '#1a76d9',
                    700: '#145fb0',
                    800: '#144f90',
                    900: '#154376',
                },
            },
            fontFamily: {
                sans: ['Barlow', ...defaultTheme.fontFamily.sans],
                body: ['Barlow', ...defaultTheme.fontFamily.sans],
                title: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
