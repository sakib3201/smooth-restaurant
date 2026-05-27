import { useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { routes } from '../config/routes';
import { useTheme } from '../hooks/useTheme';
import { HamburgerIcon, ChevronLeftIcon, BellIcon, SunIcon, MoonIcon } from './icons';

interface HeaderProps {
	sidebarCollapsed: boolean;
	onSidebarToggle: () => void;
	onMobileMenuToggle: () => void;
}

export const Header = ( { sidebarCollapsed, onSidebarToggle, onMobileMenuToggle }: HeaderProps ) => {
	const location = useLocation();
	const { theme, toggleTheme } = useTheme();
	const isDark = theme === 'dark';
	const pageTitle = routes.find( r => r.path === location.pathname )?.label || __( 'Smooth Restaurant', 'smooth-restaurant' );

	return (
		<header className="sr-h-14 sr-bg-sr-surface sr-border-b sr-border-sr-border sr-flex sr-items-center sr-justify-between sr-px-4 sr-shrink-0">
			<div className="sr-flex sr-items-center sr-gap-3">
				{/* Mobile hamburger */}
				<button
					onClick={ onMobileMenuToggle }
					className="sr-min-w-[44px] sr-min-h-[44px] sr-flex sr-items-center sr-justify-center sr-rounded-sr-md sr-text-sr-text-secondary hover:sr-bg-sr-border sr-transition-colors sr-duration-150 sr-focus-ring lg:sr-hidden"
					aria-label={ __( 'Toggle navigation menu', 'smooth-restaurant' ) }
					type="button"
				>
					<HamburgerIcon />
				</button>

				{/* Desktop sidebar toggle */}
				<button
					onClick={ onSidebarToggle }
					className="sr-min-w-[44px] sr-min-h-[44px] sr-flex sr-items-center sr-justify-center sr-rounded-sr-md sr-text-sr-text-secondary hover:sr-bg-sr-border sr-transition-colors sr-duration-150 sr-focus-ring sr-hidden lg:sr-block"
					aria-label={ sidebarCollapsed ? __( 'Expand sidebar', 'smooth-restaurant' ) : __( 'Collapse sidebar', 'smooth-restaurant' ) }
					aria-expanded={ ! sidebarCollapsed }
					type="button"
				>
					<ChevronLeftIcon
						className={ sidebarCollapsed ? '' : 'sr-rotate-180' }
						style={ { transition: 'transform 300ms cubic-bezier(0.16, 1, 0.3, 1)' } }
					/>
				</button>

				<h1 className="sr-text-display sr-text-sr-text sr-truncate sr-max-w-[12rem] sm:sr-max-w-xs md:sr-max-w-sm">{ pageTitle }</h1>
			</div>

			<div className="sr-flex sr-items-center sr-gap-2">
				{/* Dark mode toggle */}
				<button
					onClick={ toggleTheme }
					className="sr-min-w-[44px] sr-min-h-[44px] sr-flex sr-items-center sr-justify-center sr-rounded-sr-md sr-text-sr-text-secondary hover:sr-bg-sr-border sr-transition-colors sr-duration-150 sr-focus-ring"
					aria-label={ isDark ? __( 'Switch to light mode', 'smooth-restaurant' ) : __( 'Switch to dark mode', 'smooth-restaurant' ) }
					title={ isDark ? __( 'Switch to light mode', 'smooth-restaurant' ) : __( 'Switch to dark mode', 'smooth-restaurant' ) }
					type="button"
				>
					{ isDark ? <MoonIcon /> : <SunIcon /> }
				</button>

				{/* Notifications — visual placeholder for future API integration */}
				<button
					className="sr-min-w-[44px] sr-min-h-[44px] sr-flex sr-items-center sr-justify-center sr-rounded-sr-md sr-text-sr-text-secondary hover:sr-bg-sr-border sr-transition-colors sr-duration-150 sr-focus-ring sr-relative"
					aria-label={ __( 'Notifications', 'smooth-restaurant' ) }
					title={ __( 'Notifications — coming soon', 'smooth-restaurant' ) }
					type="button"
				>
					<BellIcon />
					{/* TODO: Show unread badge count from API */}
					<span className="sr-absolute sr-top-1.5 sr-right-1.5 sr-w-2 sr-h-2 sr-bg-sr-crimson sr-rounded-sr-full" aria-hidden="true" />
				</button>
			</div>
		</header>
	);
};
