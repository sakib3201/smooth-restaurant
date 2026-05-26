import { createRoot } from 'react-dom/client';
import './style.scss';

const FrontendApp = () => {
	return (
		<div className="smooth-restaurant-frontend">
			<h2 className="text-xl font-semibold text-gray-900">
				Smooth Restaurant Frontend
			</h2>
		</div>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
	const containers = document.querySelectorAll( '.smooth-restaurant-frontend-root' );
	containers.forEach( ( container ) => {
		const root = createRoot( container );
		root.render( <FrontendApp /> );
	} );
} );
