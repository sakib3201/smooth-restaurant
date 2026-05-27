import { __ } from '@wordpress/i18n';
import { Separator } from '@/admin/components/ui/separator';

export const Footer = () => {
	return (
		<footer className="sr-shrink-0 sr-bg-sr-surface sr-border-t sr-border-sr-border sr-flex sr-flex-col sm:sr-flex-row sm:sr-items-center sm:sr-justify-between sr-px-4 sr-py-2 sr-gap-1 sm:sr-gap-0">
			<span className="sr-text-label sr-text-sr-text-secondary">
				{ __( 'Smooth Restaurant', 'smooth-restaurant' ) }
			</span>
			<Separator orientation="vertical" className="sr-hidden sm:sr-block sr-bg-sr-border" />
			<a
				href="https://smoothrestaurant.com/docs"
				target="_blank"
				rel="noopener noreferrer"
				className="sr-text-label sr-text-sr-text-secondary hover:sr-text-sr-primary sr-transition-colors sr-duration-150 sr-focus-ring sr-rounded-sr-sm"
			>
				{ __( 'Help', 'smooth-restaurant' ) }
			</a>
		</footer>
	);
};
