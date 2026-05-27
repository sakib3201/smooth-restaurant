import { __ } from '@wordpress/i18n';

export const Footer = () => {
	return (
		<footer className="sr-h-12 sr-bg-sr-surface sr-border-t sr-border-sr-border sr-flex sr-flex-col sm:sr-flex-row sm:sr-items-center sm:sr-justify-between sr-px-4 sr-py-2 sm:sr-py-0 sr-shrink-0 sr-gap-1 sm:sr-gap-0">
			<div className="sr-flex sr-items-center sr-gap-4">
				<span className="sr-text-label sr-text-sr-text-secondary">
					{ __( 'Smooth Restaurant', 'smooth-restaurant' ) }
				</span>
			</div>
			<div className="sr-flex sr-items-center sr-gap-4">
				<a
					href="https://smoothrestaurant.com/docs"
					target="_blank"
					rel="noopener noreferrer"
					className="sr-text-label sr-text-sr-text-secondary hover:sr-text-sr-primary sr-transition-colors sr-duration-150 sr-focus-ring sr-rounded-sr-sm"
				>
					{ __( 'Help', 'smooth-restaurant' ) }
				</a>
			</div>
		</footer>
	);
};
