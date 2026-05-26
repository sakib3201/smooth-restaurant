import { useBlockProps, RichText } from '@wordpress/block-editor';
import { BlockSaveProps } from '@wordpress/blocks';

interface MenuItemAttributes {
	title: string;
	price: string;
	description: string;
}

export const Save: React.FC<BlockSaveProps<MenuItemAttributes>> = ( {
	attributes,
} ) => {
	const blockProps = useBlockProps.save( {
		className: 'smooth-restaurant-menu-item',
	} );

	return (
		<div { ...blockProps }>
			<div className="menu-item-header">
				<RichText.Content
					tagName="h3"
					className="menu-item-title"
					value={ attributes.title }
				/>
				<RichText.Content
					tagName="span"
					className="menu-item-price"
					value={ attributes.price }
				/>
			</div>
			<RichText.Content
				tagName="p"
				className="menu-item-description"
				value={ attributes.description }
			/>
		</div>
	);
};
