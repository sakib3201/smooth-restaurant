import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from 'react';

type Theme = 'light' | 'dark';

interface ThemeContextValue {
	theme: Theme;
	toggleTheme: () => void;
	setTheme: ( theme: Theme ) => void;
}

const STORAGE_KEY = 'smooth-restaurant-theme';

const ThemeContext = createContext< ThemeContextValue | undefined >( undefined );

function getInitialTheme(): Theme {
	// Check localStorage first
	if ( typeof window !== 'undefined' ) {
		const stored = window.localStorage.getItem( STORAGE_KEY );
		if ( stored === 'light' || stored === 'dark' ) {
			return stored;
		}

		// Fall back to system preference
		if ( window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) {
			return 'dark';
		}
	}

	return 'light';
}

interface ThemeProviderProps {
	children: ReactNode;
}

export const ThemeProvider = ( { children }: ThemeProviderProps ) => {
	const [ theme, setThemeState ] = useState< Theme >( getInitialTheme );
	const [ mounted, setMounted ] = useState( false );

	// Apply theme to DOM
	useEffect( () => {
		const root = document.documentElement;
		root.setAttribute( 'data-theme', theme );
		window.localStorage.setItem( STORAGE_KEY, theme );
	}, [ theme ] );

	// Prevent flash of wrong theme on initial load
	useEffect( () => {
		setMounted( true );
	}, [] );

	const setTheme = useCallback( ( newTheme: Theme ) => {
		setThemeState( newTheme );
	}, [] );

	const toggleTheme = useCallback( () => {
		setThemeState( ( prev ) => ( prev === 'light' ? 'dark' : 'light' ) );
	}, [] );

	// Prevent rendering until mounted to avoid hydration mismatch
	if ( ! mounted ) {
		return <div style={ { visibility: 'hidden' } } />;
	}

	return (
		<ThemeContext.Provider value={ { theme, toggleTheme, setTheme } }>
			{ children }
		</ThemeContext.Provider>
	);
};

export const useTheme = (): ThemeContextValue => {
	const context = useContext( ThemeContext );
	if ( ! context ) {
		throw new Error( 'useTheme must be used within a ThemeProvider' );
	}
	return context;
};
