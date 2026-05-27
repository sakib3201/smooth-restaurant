import { Component, type ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import {
	Empty,
	EmptyHeader,
	EmptyTitle,
	EmptyDescription,
} from '@/admin/components/ui/empty';
import { Button } from '@/admin/components/ui/button';

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
				<Empty className="sr-min-h-[50vh]">
					<EmptyHeader>
						<EmptyTitle>{ __( 'Something went wrong', 'smooth-restaurant' ) }</EmptyTitle>
						<EmptyDescription>{ __( 'An error occurred while loading this page. Please try reloading.', 'smooth-restaurant' ) }</EmptyDescription>
					</EmptyHeader>
					<Button onClick={ () => window.location.reload() }>
						{ __( 'Reload page', 'smooth-restaurant' ) }
					</Button>
				</Empty>
			);
		}

		return this.props.children;
	}
}
