import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

const FrontendApp = () => {
	return (
		<div className="smooth-restaurant-frontend">
			<h2 className="text-xl font-semibold text-gray-900">
				Smooth Restaurant Frontend
			</h2>
		</div>
	);
};

describe( 'Frontend App', () => {
	it( 'renders without crashing', () => {
		render( <FrontendApp /> );
		expect(
			screen.getByText( 'Smooth Restaurant Frontend' )
		).toBeInTheDocument();
	} );
} );
