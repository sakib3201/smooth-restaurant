import { __ } from '@wordpress/i18n';
import { PlaceholderPage } from '../components/PlaceholderPage';

export const Settings = () => (
	<PlaceholderPage
		title={ __( 'Settings', 'smooth-restaurant' ) }
		description={ __( 'Configure your restaurant settings here.', 'smooth-restaurant' ) }
	/>
);
