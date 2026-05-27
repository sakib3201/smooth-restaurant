import { useEffect, useRef } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { cn } from '../lib/utils';
import { routes } from '../config/routes';
import {
	DashboardIcon,
	MenuItemsIcon,
	OrdersIcon,
	ReservationsIcon,
	SettingsIcon,
	LogoIcon,
	WordPressIcon,
} from './icons';

interface SidebarProps {
	collapsed: boolean;
	mobileOpen: boolean;
	onMobileClose: () => void;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
	'/': DashboardIcon,
	'/menu-items': MenuItemsIcon,
	'/orders': OrdersIcon,
	'/reservations': ReservationsIcon,
	'/settings': SettingsIcon,
};

export const Sidebar = ( { collapsed, mobileOpen, onMobileClose }: SidebarProps ) => {
	const location = useLocation();
	const navigate = useNavigate();
	const sidebarRef = useRef<HTMLElement>( null );
	const firstItemRef = useRef<HTMLButtonElement>( null );

	// Focus trap for mobile drawer
	useEffect( () => {
		if ( ! mobileOpen ) return;

		const sidebar = sidebarRef.current;
		if ( ! sidebar ) return;

		// Focus first item when opened
		firstItemRef.current?.focus();

		const handleKeyDown = ( e: KeyboardEvent ) => {
			if ( e.key !== 'Tab' ) return;

			const focusable = sidebar.querySelectorAll<HTMLElement>(
				'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);
			const first = focusable[ 0 ];
			const last = focusable[ focusable.length - 1 ];

			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last?.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first?.focus();
			}
		};

		document.addEventListener( 'keydown', handleKeyDown );
		return () => document.removeEventListener( 'keydown', handleKeyDown );
	}, [ mobileOpen ] );

	// Close mobile menu on Escape
	useEffect( () => {
		const handleEscape = ( e: KeyboardEvent ) => {
			if ( e.key === 'Escape' && mobileOpen ) {
				onMobileClose();
			}
		};
		document.addEventListener( 'keydown', handleEscape );
		return () => document.removeEventListener( 'keydown', handleEscape );
	}, [ mobileOpen, onMobileClose ] );

	const handleNavClick = ( path: string ) => {
		navigate( path );
		onMobileClose();
	};

	return (
		<>
			{/* Mobile overlay */}
			{ mobileOpen && (
				<div
					className="sr-fixed sr-inset-0 sr-bg-black/30 sr-z-40 lg:sr-hidden sr-transition-opacity sr-duration-200 sr-ease-out-expo"
					onClick={ onMobileClose }
					aria-hidden="true"
				/>
			) }

			{/* Sidebar */}
			<aside
				ref={ sidebarRef }
				className={ cn(
					'sr-fixed sr-top-0 sr-left-0 sr-h-full sr-bg-sr-surface sr-border-r sr-border-sr-border sr-z-50 sr-flex sr-flex-col sr-transition-all sr-duration-300 sr-ease-out-expo',
					collapsed ? 'lg:sr-w-16' : 'lg:sr-w-60',
					mobileOpen ? 'sr-translate-x-0' : '-sr-translate-x-full md:sr-translate-x-0',
					mobileOpen && ! collapsed ? 'sr-w-60' : '',
					mobileOpen && collapsed ? 'sr-w-16' : ''
				) }
				aria-label={ __( 'Main navigation', 'smooth-restaurant' ) }
			>
				{/* Logo */}
				<div className={ cn(
					'sr-flex sr-items-center sr-border-b sr-border-sr-border sr-shrink-0',
					collapsed ? 'lg:sr-justify-center sr-h-14 sr-px-2' : 'sr-h-14 sr-px-4'
				) }>
					<div className="sr-flex sr-items-center sr-gap-2 sr-min-w-0">
						<div className="sr-w-8 sr-h-8 sr-rounded-sr-md sr-bg-sr-primary sr-flex sr-items-center sr-justify-center sr-shrink-0">
							<LogoIcon />
						</div>
						{ ! collapsed && (
							<span className="sr-font-semibold sr-text-sr-text sr-text-body sr-truncate sr-whitespace-nowrap sr-transition-opacity sr-duration-200 sr-ease-out-expo">
								{ __( 'Smooth Restaurant', 'smooth-restaurant' ) }
							</span>
						) }
					</div>
				</div>

				{/* Navigation */}
				<nav className="sr-flex-1 sr-py-4 sr-px-2 sr-overflow-y-auto sr-scrollbar-thin">
					<ul className="sr-space-y-1">
						{ routes.map( ( route, index ) => {
							const isActive = location.pathname === route.path;
							const Icon = iconMap[ route.path ];
							return (
								<li key={ route.path }>
									<button
										ref={ index === 0 ? firstItemRef : undefined }
										onClick={ () => handleNavClick( route.path ) }
										className={ cn(
											'sr-w-full sr-flex sr-items-center sr-gap-3 sr-px-3 sr-py-2.5 sr-rounded-sr-md sr-text-label sr-font-medium sr-transition-all sr-duration-150 sr-ease-out-expo sr-focus-ring sr-min-h-[44px]',
											isActive
												? 'sr-bg-sr-primary/10 sr-text-sr-primary'
												: 'sr-text-sr-text-secondary hover:sr-bg-sr-bg hover:sr-text-sr-text'
										) }
										aria-current={ isActive ? 'page' : undefined }
									>
										<span className="sr-shrink-0 sr-flex sr-items-center sr-justify-center" aria-hidden="true">
											{ Icon ? <Icon /> : <span className="sr-w-5 sr-h-5" /> }
										</span>
										{ ! collapsed && (
											<span className="sr-truncate sr-whitespace-nowrap sr-transition-opacity sr-duration-200 sr-ease-out-expo">{ route.label }</span>
										) }
									</button>
								</li>
							);
						} ) }
					</ul>
				</nav>

				{/* Back to WordPress */}
				<div className="sr-shrink-0 sr-py-3 sr-px-2 sr-border-t sr-border-sr-border">
					<a
						href="/wp-admin"
						className={ cn(
							'sr-flex sr-items-center sr-gap-3 sr-px-3 sr-py-2.5 sr-rounded-sr-md sr-text-label sr-text-sr-text-secondary hover:sr-bg-sr-bg hover:sr-text-sr-text sr-transition-colors sr-duration-150 sr-ease-out-expo sr-focus-ring sr-min-h-[44px]',
							collapsed ? 'lg:sr-justify-center' : ''
						) }
						aria-label={ __( 'Back to WordPress Dashboard', 'smooth-restaurant' ) }
					>
						<span className="sr-shrink-0 sr-flex sr-items-center sr-justify-center sr-text-sr-text-secondary" aria-hidden="true">
							<WordPressIcon />
						</span>
						{ ! collapsed && (
							<span className="sr-truncate sr-whitespace-nowrap sr-transition-opacity sr-duration-200 sr-ease-out-expo">{ __( 'Back to WordPress', 'smooth-restaurant' ) }</span>
						) }
					</a>
				</div>
			</aside>
		</>
	);
};
