import { useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { routes } from '../config/routes';
import { useTheme } from '../hooks/useTheme';
import { Button } from '@/admin/components/ui/button';
import {
	Tooltip,
	TooltipContent,
	TooltipProvider,
	TooltipTrigger,
} from '@/admin/components/ui/tooltip';
import { Menu, PanelLeftOpen, PanelLeftClose, Bell, Sun, Moon } from 'lucide-react';

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
				<Button
					variant="ghost"
					size="icon"
					className="lg:sr-hidden"
					onClick={ onMobileMenuToggle }
					aria-label={ __( 'Toggle navigation menu', 'smooth-restaurant' ) }
				>
					<Menu />
				</Button>

				{/* Desktop sidebar toggle */}
				<Button
					variant="ghost"
					size="icon"
					className="sr-hidden lg:sr-flex"
					onClick={ onSidebarToggle }
					aria-label={ sidebarCollapsed ? __( 'Expand sidebar', 'smooth-restaurant' ) : __( 'Collapse sidebar', 'smooth-restaurant' ) }
					aria-expanded={ ! sidebarCollapsed }
				>
					{ sidebarCollapsed ? <PanelLeftOpen /> : <PanelLeftClose /> }
				</Button>

				<h1 className="sr-text-display sr-text-sr-text sr-truncate sr-max-w-[12rem] sm:sr-max-w-xs md:sr-max-w-sm">{ pageTitle }</h1>
			</div>

			<TooltipProvider delay={ 100 }>
				<div className="sr-flex sr-items-center sr-gap-2">
					{/* Dark mode toggle */}
					<Tooltip>
						<TooltipTrigger asChild>
							<Button
								variant="ghost"
								size="icon"
								onClick={ toggleTheme }
								aria-label={ isDark ? __( 'Switch to light mode', 'smooth-restaurant' ) : __( 'Switch to dark mode', 'smooth-restaurant' ) }
							>
								{ isDark ? <Moon /> : <Sun /> }
							</Button>
						</TooltipTrigger>
						<TooltipContent>
							{ isDark ? __( 'Switch to light mode', 'smooth-restaurant' ) : __( 'Switch to dark mode', 'smooth-restaurant' ) }
						</TooltipContent>
					</Tooltip>

					{/* Notifications — visual placeholder for future API integration */}
					<Tooltip>
						<TooltipTrigger asChild>
							<Button
								variant="ghost"
								size="icon"
								className="sr-relative"
								aria-label={ __( 'Notifications', 'smooth-restaurant' ) }
							>
								<Bell />
								{/* TODO: Show unread badge count from API */}
								<span className="sr-absolute sr-top-1.5 sr-right-1.5 sr-size-2 sr-bg-sr-crimson sr-rounded-sr-full" aria-hidden="true" />
							</Button>
						</TooltipTrigger>
						<TooltipContent>{ __( 'Notifications — coming soon', 'smooth-restaurant' ) }</TooltipContent>
					</Tooltip>
				</div>
			</TooltipProvider>
		</header>
	);
};
