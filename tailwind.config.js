import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    // Content: Menentukan file mana yang discan untuk class CSS
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            // Menambahkan Font Family default (biasanya Figtree atau Inter di Laravel baru)
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Contoh: Kalau mau nambah warna custom sendiri
            colors: {
                // 'brand': '#ff0000', 
            }
        },
    },
    plugins: [
        // Jika nanti lu butuh plugin form, uncomment baris bawah ini (perlu install dulu)
        // require('@tailwindcss/forms'),
    ],
};