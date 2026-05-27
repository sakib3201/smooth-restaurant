import { useState, useCallback } from 'react';
import { Sidebar } from './Sidebar';
import { Header } from './Header';
import { Footer } from './Footer';

interface LayoutProps {
	children: React.ReactNode;
}

export const Layout = ( { children }: LayoutProps ) => {
	const [ sidebarCollapsed, setSidebarCollapsed ] = useState( false );
	const [ mobileMenuOpen, setMobileMenuOpen ] = useState( false );

	const handleSidebarToggle = useCallback( () => {
		setSidebarCollapsed( ( prev ) => ! prev );
	}, [] );

	const handleMobileMenuToggle = useCallback( () => {
		setMobileMenuOpen( ( prev ) => ! prev );
	}, [] );

	const handleMobileClose = useCallback( () => {
		setMobileMenuOpen( false );
	}, [] );

	return (
		<div className="sr-flex sr-min-h-screen sr-bg-sr-bg">
			{/* Skip to content link */}
			<a
				href="#smooth-restaurant-main"
				className="sr-sr-only sr-focus:not-sr-only sr-focus:sr-absolute sr-focus:sr-top-2 sr-focus:sr-left-2 sr-focus:sr-z-[60] sr-focus:sr-px-4 sr-focus:sr-py-2 sr-focus:sr-bg-sr-primary sr-focus:sr-text-white sr-focus:sr-rounded-sr-md sr-focus:sr-text-label"
			>
				Skip to content
			</a>

			{/* Sidebar */}
			<Sidebar
				collapsed={ sidebarCollapsed }
				mobileOpen={ mobileMenuOpen }
				onMobileClose={ handleMobileClose }
			/>

			{/* Main content area */}
			<div
				className={ `sr-flex-1 sr-flex sr-flex-col sr-min-h-screen sr-transition-[margin-left] sr-duration-300 sr-ease-out-expo md:sr-ml-16 ${ sidebarCollapsed ? 'lg:sr-ml-16' : 'lg:sr-ml-60' }` }
			>
				<Header
					sidebarCollapsed={ sidebarCollapsed }
					onSidebarToggle={ handleSidebarToggle }
					onMobileMenuToggle={ handleMobileMenuToggle }
				/>

				<main id="smooth-restaurant-main" className="sr-flex-1 sr-overflow-y-auto sr-scrollbar-thin" tabIndex={ -1 }>
					<div className="sr-p-4 lg:sr-p-8 sr-pb-12 sr-max-w-7xl sr-mx-auto">
						{ children }
					</div>
				</main>

				<Footer />
			</div>
		</div>
	);
};
