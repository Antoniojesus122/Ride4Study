/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./**/*.html",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'mint': '#6EE7B7',
        'mint-dark': '#10B981',
        'cream': '#F9FAF5',
        'charcoal': '#374151',
        'charcoal-light': '#4B5563',
        'accent': '#3E8E89',
      },

      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
      },

      boxShadow: {
        'soft': '0 4px 12px rgba(0,0,0,0.08)',
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

      animation: {
        'slide-left': 'slideLeft 0.8s ease-in-out forwards',
        'slide-right': 'slideRight 0.8s ease-in-out forwards',
      },
    },
  },
  plugins: [],
}
