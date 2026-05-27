import { createRoot } from 'react-dom/client';
import { HashRouter } from 'react-router-dom';
import { App } from './App';
import { ThemeProvider } from './hooks/useTheme';
import './style.scss';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'smooth-restaurant-admin' );
	if ( ! container ) {
		// eslint-disable-next-line no-console
		console.warn( 'Smooth Restaurant: mount node #smooth-restaurant-admin not found.' );
		return;
	}
	const root = createRoot( container );
	root.render(
		<HashRouter>
			<ThemeProvider>
				<App />
			</ThemeProvider>
		</HashRouter>
	);
} );
