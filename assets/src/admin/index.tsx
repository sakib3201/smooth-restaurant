import { createRoot } from 'react-dom/client';
import './style.scss';

const AdminApp = () => {
	return (
		<div className="smooth-restaurant-admin">
			<h1 className="text-2xl font-bold text-gray-800">
				Smooth Restaurant Admin
			</h1>
		</div>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'smooth-restaurant-admin' );
	if ( container ) {
		const root = createRoot( container );
		root.render( <AdminApp /> );
	}
} );
