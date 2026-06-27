/** @type {import('tailwindcss').Config} */

// Design tokens preserve the reference Apps Script application's navy identity.
// Colors are exposed as CSS variables (see src/styles/index.css) so the Branding
// capability can override theme color / dark mode at runtime without a rebuild.
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        navy: {
          primary: 'var(--navy-primary)',
          dark: 'var(--navy-dark)',
          light: 'var(--navy-light)',
          accent: 'var(--navy-accent)',
          hover: 'var(--navy-hover)',
        },
        brand: {
          text: 'var(--text-primary)',
          success: 'var(--success)',
          warning: 'var(--warning)',
          danger: 'var(--danger)',
        },
      },
      fontFamily: {
        sans: [
          '-apple-system',
          'BlinkMacSystemFont',
          'Segoe UI',
          'Roboto',
          'sans-serif',
        ],
      },
      spacing: {
        sidebar: '260px',
        'sidebar-collapsed': '72px',
        header: '64px',
        'bottom-nav': 'var(--bottom-nav-height)',
      },
      boxShadow: {
        card: '0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04)',
      },
    },
  },
  plugins: [],
};
