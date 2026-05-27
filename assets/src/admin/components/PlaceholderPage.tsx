import { __ } from '@wordpress/i18n';
import {
	Empty,
	EmptyHeader,
	EmptyTitle,
	EmptyDescription,
} from '@/admin/components/ui/empty';

interface PlaceholderPageProps {
	title: string;
	description: string;
}

export const PlaceholderPage = ( { title, description }: PlaceholderPageProps ) => {
	return (
		<Empty className="sr-py-8">
			<EmptyHeader>
				<EmptyTitle>{ title }</EmptyTitle>
				<EmptyDescription>{ description }</EmptyDescription>
			</EmptyHeader>
		</Empty>
	);
};
