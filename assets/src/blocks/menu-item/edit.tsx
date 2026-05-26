import { useBlockProps, RichText } from '@wordpress/block-editor';
import { BlockEditProps } from '@wordpress/blocks';
import './style.scss';

interface MenuItemAttributes {
	title: string;
	price: string;
	description: string;
}

export const Edit: React.FC<BlockEditProps<MenuItemAttributes>> = ( {
	attributes,
	setAttributes,
} ) => {
	const blockProps = useBlockProps( {
		className: 'smooth-restaurant-menu-item',
	} );

	return (
		<div { ...blockProps }>
			<div className="menu-item-header">
				<RichText
					tagName="h3"
					className="menu-item-title"
					value={ attributes.title }
					onChange={ ( title ) => setAttributes( { title } ) }
					placeholder="Item title…"
				/>
				<RichText
					tagName="span"
					className="menu-item-price"
					value={ attributes.price }
					onChange={ ( price ) => setAttributes( { price } ) }
					placeholder="$0.00"
				/>
			</div>
			<RichText
				tagName="p"
				className="menu-item-description"
				value={ attributes.description }
				onChange={ ( description ) => setAttributes( { description } ) }
				placeholder="Description…"
			/>
		</div>
	);
};
