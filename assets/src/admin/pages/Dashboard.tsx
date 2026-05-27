import { __ } from '@wordpress/i18n';
import { PlaceholderPage } from '../components/PlaceholderPage';

export const Dashboard = () => (
	<PlaceholderPage
		title={ __( 'Welcome to Smooth Restaurant', 'smooth-restaurant' ) }
		description={ __( 'Your restaurant management dashboard is being prepared.', 'smooth-restaurant' ) }
	/>
);
