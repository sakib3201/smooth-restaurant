import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

interface LoadingSpinnerProps {
	size?: number;
	className?: string;
}

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ( {
	size = 24,
	className = '',
} ) => {
	return (
		<div
			className={ `smooth-restaurant-spinner ${ className }` }
			style={ {
				width: size,
				height: size,
			} }
			data-testid="loading-spinner"
		>
			<svg
				className="animate-spin"
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
			>
				<circle
					className="opacity-25"
					cx="12"
					cy="12"
					r="10"
					stroke="currentColor"
					strokeWidth="4"
				/>
				<path
					className="opacity-75"
					fill="currentColor"
					d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
				/>
			</svg>
		</div>
	);
};

describe( 'LoadingSpinner', () => {
	it( 'renders without crashing', () => {
		render( <LoadingSpinner /> );
		expect( screen.getByTestId( 'loading-spinner' ) ).toBeInTheDocument();
	} );

	it( 'applies default size', () => {
		render( <LoadingSpinner /> );
		const spinner = screen.getByTestId( 'loading-spinner' );
		expect( spinner ).toHaveStyle( { width: 24, height: 24 } );
	} );

	it( 'applies custom size and className', () => {
		render( <LoadingSpinner size={ 48 } className="custom-class" /> );
		const spinner = screen.getByTestId( 'loading-spinner' );
		expect( spinner ).toHaveStyle( { width: 48, height: 48 } );
		expect( spinner ).toHaveClass( 'custom-class' );
	} );
} );
