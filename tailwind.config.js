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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0F4CBA',
                    50: '#EAF1FC',
                    100: '#D5E3F9',
                    400: '#3E75D1',
                    500: '#0F4CBA',
                    600: '#0D3F9C',
                    700: '#0A317A',
                    900: '#081F4D',
                },
                secondary: {
                    DEFAULT: '#00A65A',
                    50: '#E5F8EE',
                    100: '#CCF1DD',
                    500: '#00A65A',
                    600: '#008C4C',
                    700: '#00713D',
                },
                accent: {
                    DEFAULT: '#F68B1F',
                    50: '#FFF2E3',
                    100: '#FEE3C2',
                    500: '#F68B1F',
                    600: '#DB7610',
                },
                'app-bg': '#F6FAFF',
            },
            boxShadow: {
                soft: '0 10px 30px -12px rgba(15, 76, 186, 0.18)',
                'soft-lg': '0 20px 45px -15px rgba(15, 76, 186, 0.25)',
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.94)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                fadeInRight: {
                    '0%': { opacity: '0', transform: 'translateX(40px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.5s ease-out both',
                'fade-in': 'fadeIn 0.4s ease-out both',
                'scale-in': 'scaleIn 0.35s ease-out both',
                float: 'float 4s ease-in-out infinite',
                'fade-in-right': 'fadeInRight 0.5s ease-out both',
            },
        },
    },

    plugins: [forms],
};
