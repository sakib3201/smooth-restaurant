import { Component, type ReactNode } from 'react';
import { __ } from '@wordpress/i18n';

interface Props {
	children: ReactNode;
}

interface State {
	hasError: boolean;
}

export class ErrorBoundary extends Component< Props, State > {
	state: State = { hasError: false };

	static getDerivedStateFromError(): State {
		return { hasError: true };
	}

	render() {
		if ( this.state.hasError ) {
			return (
				<div className="sr-flex sr-flex-col sr-items-center sr-justify-center sr-min-h-[50vh] sr-p-8 sr-text-center">
					<h2 className="sr-text-headline sr-text-sr-text sr-mb-3">
						{ __( 'Something went wrong', 'smooth-restaurant' ) }
					</h2>
					<p className="sr-text-body sr-text-sr-text-secondary sr-mb-6 sr-max-w-md">
						{ __( 'An error occurred while loading this page. Please try reloading.', 'smooth-restaurant' ) }
					</p>
					<button
						className="sr-px-4 sr-py-2 sr-bg-sr-primary sr-text-white sr-rounded-sr-md sr-text-label sr-font-medium hover:sr-bg-sr-primary-dark sr-transition-colors sr-duration-150 sr-focus-ring"
						onClick={ () => window.location.reload() }
						type="button"
					>
						{ __( 'Reload page', 'smooth-restaurant' ) }
					</button>
				</div>
			);
		}

		return this.props.children;
	}
}
