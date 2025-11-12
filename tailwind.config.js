import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

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
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            "light", "dark", "cupcake", "bumblebee", "emerald", "corporate",
            "synthwave", "retro", "cyberpunk", "valentine", "halloween", "garden",
            "forest", "aqua", "lofi", "pastel", "fantasy", "wireframe", "black",
            "luxury", "dracula", "cmyk", "autumn", "business", "acid", "lemonade",
            "night", "coffee", "winter", "dim", "nord", "sunset"
        ], // 32 tema DaisyUI official
        darkTheme: "dark",
        base: true,
        styled: true,
        utils: true,
        prefix: "",
        logs: true,
        themeRoot: ":root",
    },
};
