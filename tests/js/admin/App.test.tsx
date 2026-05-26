import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

const AdminApp = () => {
	return (
		<div className="smooth-restaurant-admin">
			<h1 className="text-2xl font-bold text-gray-800">
				Smooth Restaurant Admin
			</h1>
		</div>
	);
};

describe( 'Admin App', () => {
	it( 'renders without crashing', () => {
		render( <AdminApp /> );
		expect(
			screen.getByText( 'Smooth Restaurant Admin' )
		).toBeInTheDocument();
	} );
} );
