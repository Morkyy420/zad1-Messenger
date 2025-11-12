/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Dark Modern Theme
        dark: {
          bg: '#0f172a',
          surface: '#1e293b',
          card: '#334155',
          primary: '#3b82f6',
          'primary-hover': '#2563eb',
          text: '#f1f5f9',
          'text-muted': '#94a3b8',
          border: '#475569',
        },
        // Light Modern Theme
        light: {
          bg: '#ffffff',
          surface: '#f8fafc',
          card: '#ffffff',
          primary: '#3b82f6',
          'primary-hover': '#2563eb',
          text: '#0f172a',
          'text-muted': '#64748b',
          border: '#e2e8f0',
        },
        // Feminine Theme
        feminine: {
          bg: '#fef3f8',
          surface: '#fce7f3',
          card: '#ffffff',
          primary: '#ec4899',
          'primary-hover': '#db2777',
          text: '#831843',
          'text-muted': '#9f1239',
          border: '#f9a8d4',
          accent: '#f472b6',
        }
      },
      animation: {
        'float': 'float 3s ease-in-out infinite',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-10px)' },
        }
      }
    },
  },
  plugins: [],
}
