import { __ } from '@wordpress/i18n';
import { PlaceholderPage } from '../components/PlaceholderPage';

export const Reservations = () => (
	<PlaceholderPage
		title={ __( 'Reservations', 'smooth-restaurant' ) }
		description={ __( 'Manage table reservations here.', 'smooth-restaurant' ) }
	/>
);
