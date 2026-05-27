import { Suspense } from 'react';
import { Routes, Route, useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { Layout } from './components/Layout';
import { ErrorBoundary } from './components/ErrorBoundary';
import { routes } from './config/routes';

const PageLoader = () => (
	<div className="sr-flex sr-items-center sr-justify-center sr-h-64" role="status" aria-live="polite">
		<div className="sr-w-8 sr-h-8 sr-border-2 sr-border-sr-border sr-border-t-sr-primary sr-rounded-sr-full sr-animate-spin" aria-hidden="true" />
		<span className="sr-sr-only">{ __( 'Loading…', 'smooth-restaurant' ) }</span>
	</div>
);

const NotFound = () => {
	const navigate = useNavigate();
	return (
		<div className="sr-max-w-6xl sr-text-center sr-py-16">
			<h2 className="sr-text-headline sr-text-sr-text sr-mb-3">{ __( 'Page not found', 'smooth-restaurant' ) }</h2>
			<p className="sr-text-body sr-text-sr-text-secondary sr-mb-6">{ __( 'The page you are looking for does not exist.', 'smooth-restaurant' ) }</p>
			<button
				onClick={ () => navigate( '/' ) }
				className="sr-px-4 sr-py-2 sr-bg-sr-primary sr-text-white sr-rounded-sr-md sr-text-label sr-font-medium hover:sr-bg-sr-primary-dark sr-transition-colors sr-duration-150 sr-focus-ring"
				type="button"
			>
				{ __( 'Back to Dashboard', 'smooth-restaurant' ) }
			</button>
		</div>
	);
};

export const App = () => {
	return (
		<Layout>
			<ErrorBoundary>
				<Suspense fallback={ <PageLoader /> }>
					<Routes>
						{ routes.map( ( route ) => (
							<Route
								key={ route.path }
								path={ route.path }
								element={ <route.component /> }
							/>
						) ) }
						<Route path="*" element={ <NotFound /> } />
					</Routes>
				</Suspense>
			</ErrorBoundary>
		</Layout>
	);
};
