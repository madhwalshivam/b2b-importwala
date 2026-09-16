/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./app/Views/**/*.php",
    "./views/**/*.php",
    "./public/**/*.php",
    "./app/Helpers/**/*.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', '"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
      },
      colors: {
        primary: '#f05a29',
        'primary-dark': '#d8481b',
        brand: {
          red: '#A8111C',
          dark: '#6E0D14'
        },
        theme: {
          primary: 'var(--color-primary, #f05a29)',
          'primary-dark': 'var(--color-primary-dark, #d8481b)',
          secondary: 'var(--color-secondary, #111827)',
          accent: 'var(--color-accent, #f05a29)',
          bg: 'var(--bg-body, #ffffff)',
          'bg-soft': 'var(--bg-body-soft, #f9fafb)',
          card: 'var(--bg-card, #ffffff)',
          text: 'var(--color-text, #111827)',
          'text-muted': 'var(--color-text-muted, #6b7280)',
          success: 'var(--color-success, #10b981)',
          warning: 'var(--color-warning, #f59e0b)',
          danger: 'var(--color-danger, #ef4444)',
          gold: 'var(--color-highlight-gold, #f59e0b)'
        }
      },
      boxShadow: {
        'card': '0 4px 12px rgba(0, 0, 0, 0.05)',
      }
    },
  },
  plugins: [],
}
