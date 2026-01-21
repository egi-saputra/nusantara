<<<<<<< HEAD
/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: ["./src/**/*.{astro,html,js,jsx,ts,tsx}"],
  theme: {
    extend: {},
  },
  plugins: [],
=======
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    // content: [
    //     "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    //     "./storage/framework/views/*.php",
    //     "./resources/views/**/*.blade.php",
    //     "./resources/js/**/*.vue",
    // ],
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/js/**/*.js",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                // Modern Minimalist
                montserrat: ["Montserrat", "sans-serif"],
                poppins: ["Poppins", "sans-serif"],
                raleway: ["Raleway", "sans-serif"],
                inter: ["Inter", "sans-serif"],
                nunitoSans: ['"Nunito Sans"', "sans-serif"],
                urbanist: ["Urbanist", "sans-serif"],
                manrope: ["Manrope", "sans-serif"],
                outfit: ["Outfit", "sans-serif"],

                // Elegant & Luxury
                playfair: ['"Playfair Display"', "serif"],
                cormorant: ['"Cormorant Garamond"', "serif"],
                bodoni: ['"Bodoni Moda"', "serif"],
                cinzel: ["Cinzel", "serif"],
                marcellus: ["Marcellus", "serif"],

                // Futuristic
                orbitron: ["Orbitron", "sans-serif"],
                exo: ['"Exo 2"', "sans-serif"],
                rajdhani: ["Rajdhani", "sans-serif"],
                audiowide: ["Audiowide", "sans-serif"],
                quantico: ["Quantico", "sans-serif"],
                sarpanch: ["Sarpanch", "sans-serif"],

                // Friendly / Rounded
                quicksand: ["Quicksand", "sans-serif"],
                nunito: ["Nunito", "sans-serif"],
                kumbh: ['"Kumbh Sans"', "sans-serif"],
                mulish: ["Mulish", "sans-serif"],
                rubik: ["Rubik", "sans-serif"],

                // Bold & Strong
                anton: ["Anton", "sans-serif"],
                oswald: ["Oswald", "sans-serif"],
                bebas: ['"Bebas Neue"', "sans-serif"],
                worksans: ['"Work Sans"', "sans-serif"],
                barlow: ['"Barlow Condensed"', "sans-serif"],
            },
        },
    },

    plugins: [forms, require("@tailwindcss/typography")],
>>>>>>> b5e2a3cad44a6e56c1ab72be7454475665580f0a
};
