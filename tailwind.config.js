/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./backend/views/**/*.php",
    "./backend/includes/**/*.php",
    "./frontend/**/*.{html,js}",
  ],
  theme: {
    extend: {
      colors: {
        'cinema': {
          'dark': '#1f2937',      // Gris foncé
          'primary': '#dc2626',   // Rouge cinéma
          'accent': '#f59e0b',    // Or
        }
      }
    },
  },
  plugins: [],
}
