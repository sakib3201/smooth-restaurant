import { __ } from '@wordpress/i18n';
import { PlaceholderPage } from '../components/PlaceholderPage';

export const Orders = () => (
	<PlaceholderPage
		title={ __( 'Orders', 'smooth-restaurant' ) }
		description={ __( 'View and manage orders here.', 'smooth-restaurant' ) }
	/>
);
