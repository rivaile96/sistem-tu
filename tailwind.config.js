import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0B93D8',
                    hover: '#097fb8',
                    light: '#E6F4FA',
                },
                secondary: {
                    DEFAULT: '#178BC1',
                    hover: '#126da3',
                    light: '#EBF6FB',
                },
                brand: '#178BC1',
            },
        },
    },

    plugins: [forms],
};
