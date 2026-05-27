/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./assets/src/**/*.{js,jsx,ts,tsx}',
	],
	theme: {
			extend: {
			colors: {
				// Primary: Deep Navy Blue
				'sr-primary': '#1e40af',
				'sr-primary-dark': '#1e3a8a',
				'sr-primary-light': '#3b82f6',

				// Neutrals: CSS custom properties for theme switching
				'sr-bg': 'var(--sr-bg)',
				'sr-surface': 'var(--sr-surface)',
				'sr-border': 'var(--sr-border)',
				'sr-text': 'var(--sr-text)',
				'sr-text-secondary': 'var(--sr-text-secondary)',

				// Semantic
				'sr-sage': '#15803d',
				'sr-amber': '#b45309',
				'sr-crimson': '#b91c1c',
				'sr-slate': '#475569',

				// State / Interaction
				'sr-hover': 'var(--sr-hover)',
				'sr-active': 'var(--sr-active)',
				'sr-disabled': 'var(--sr-disabled)',
				'sr-focus': 'rgba(30, 64, 175, 0.35)',

				// shadcn semantic tokens (mapped to CSS variables)
				background: 'var(--background)',
				foreground: 'var(--foreground)',
				card: 'var(--card)',
				'card-foreground': 'var(--card-foreground)',
				popover: 'var(--popover)',
				'popover-foreground': 'var(--popover-foreground)',
				primary: 'var(--primary)',
				'primary-foreground': 'var(--primary-foreground)',
				secondary: 'var(--secondary)',
				'secondary-foreground': 'var(--secondary-foreground)',
				muted: 'var(--muted)',
				'muted-foreground': 'var(--muted-foreground)',
				accent: 'var(--accent)',
				'accent-foreground': 'var(--accent-foreground)',
				destructive: 'var(--destructive)',
				'destructive-foreground': 'var(--destructive-foreground)',
				border: 'var(--border)',
				input: 'var(--input)',
				ring: 'var(--ring)',
				sidebar: 'var(--sidebar)',
				'sidebar-foreground': 'var(--sidebar-foreground)',
				'sidebar-primary': 'var(--sidebar-primary)',
				'sidebar-primary-foreground': 'var(--sidebar-primary-foreground)',
				'sidebar-accent': 'var(--sidebar-accent)',
				'sidebar-accent-foreground': 'var(--sidebar-accent-foreground)',
				'sidebar-border': 'var(--sidebar-border)',
				'sidebar-ring': 'var(--sidebar-ring)',
			},
			fontFamily: {
				sans: [
					'-apple-system',
					'BlinkMacSystemFont',
					"'Segoe UI'",
					'Roboto',
					'Oxygen-Sans',
					'Ubuntu',
					'Cantarell',
					"'Helvetica Neue'",
					'sans-serif',
				],
			},
			fontSize: {
				'display': ['1.5rem', { lineHeight: '1.2', letterSpacing: '-0.01em', fontWeight: '600' }],
				'headline': ['1.25rem', { lineHeight: '1.3', letterSpacing: '-0.005em', fontWeight: '600' }],
				'title': ['1.125rem', { lineHeight: '1.4', fontWeight: '600' }],
				'body': ['1rem', { lineHeight: '1.5', fontWeight: '400' }],
				'label': ['0.75rem', { lineHeight: '1.4', letterSpacing: '0.01em', fontWeight: '500' }],
				'caption': ['0.875rem', { lineHeight: '1.5', fontWeight: '400' }],
			},
			borderRadius: {
				'sr-sm': '4px',
				'sr-md': '8px',
				'sr-lg': '12px',
				'sr-full': '9999px',
			},
			boxShadow: {
				'ambient-hover': '0 2px 8px rgba(0,0,0,0.06)',
				'modal': '0 8px 32px rgba(0,0,0,0.12)',
			},
			transitionTimingFunction: {
				'ease-out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
			},
		},
	},
	plugins: [
		require('@tailwindcss/forms'),
	],
	prefix: 'sr-',
};
