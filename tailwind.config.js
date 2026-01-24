/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: ["./src/**/*.{astro,html,js,jsx,ts,tsx}"],
    theme: {
        extend: {
            animation: {
                carousel: "carousel 12s linear infinite",
            },
            keyframes: {
                carousel: {
                    "0%, 25%": { transform: "translateX(0%)" },
                    "30%, 50%": { transform: "translateX(-100%)" },
                    "55%, 75%": { transform: "translateX(-200%)" },
                    "80%, 100%": { transform: "translateX(-300%)" },
                },
            },
        },
    },
    plugins: [],
};
