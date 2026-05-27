import { __ } from '@wordpress/i18n';

interface PlaceholderPageProps {
	title: string;
	description: string;
}

export const PlaceholderPage = ( { title, description }: PlaceholderPageProps ) => {
	return (
		<div className="sr-py-8 sr-max-w-[65ch]">
			<h2 className="sr-text-headline sr-text-sr-text sr-mb-4">{ title }</h2>
			<p className="sr-text-body sr-text-sr-text-secondary">{ description }</p>
		</div>
	);
};
