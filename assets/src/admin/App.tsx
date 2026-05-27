import { Suspense } from 'react';
import { Routes, Route, useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { Layout } from './components/Layout';
import { ErrorBoundary } from './components/ErrorBoundary';
import { routes } from './config/routes';
import { Skeleton } from '@/admin/components/ui/skeleton';
import { Button } from '@/admin/components/ui/button';
import {
	Empty,
	EmptyHeader,
	EmptyTitle,
	EmptyDescription,
} from '@/admin/components/ui/empty';

const PageLoader = () => (
	<div className="flex min-h-[16rem] items-center justify-center" role="status" aria-live="polite">
		<Skeleton className="size-8 rounded-full" />
		<span className="sr-only">{ __( 'Loading…', 'smooth-restaurant' ) }</span>
	</div>
);

const NotFound = () => {
	const navigate = useNavigate();
	return (
		<Empty className="mx-auto max-w-2xl py-16">
			<EmptyHeader>
				<EmptyTitle>{ __( 'Page not found', 'smooth-restaurant' ) }</EmptyTitle>
				<EmptyDescription>{ __( 'The page you are looking for does not exist.', 'smooth-restaurant' ) }</EmptyDescription>
			</EmptyHeader>
			<Button onClick={ () => navigate( '/' ) }>
				{ __( 'Back to Dashboard', 'smooth-restaurant' ) }
			</Button>
		</Empty>
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
