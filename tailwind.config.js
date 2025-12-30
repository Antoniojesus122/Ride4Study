/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./**/*.html",
    "./assets/js/**/*.js",
    "./public/js/**/*.js",
    "./views/**/*.php"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#6EE7B7',
        'primary-dark': '#059669',
        secondary: '#111827',
        surface: '#1F2937',
        'surface-light': '#374151',
        text: '#F3F4F6',
        'text-muted': '#9CA3AF',
        accent: '#3E8E89',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      animation: {
        'slide-left': 'slideLeft 0.8s ease-in-out forwards',
        'slide-right': 'slideRight 0.8s ease-in-out forwards',
      },
      keyframes: {
        slideLeft: {
          '0%': { transform: 'translateX(100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        slideRight: {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(0)' },
        },
      },
    },
  },
  plugins: [],
}