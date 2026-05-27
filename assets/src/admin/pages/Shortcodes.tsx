import { useState, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@/admin/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/admin/components/ui/card';
import { Badge } from '@/admin/components/ui/badge';
import { Separator } from '@/admin/components/ui/separator';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/admin/components/ui/table';
import { Copy, Check } from 'lucide-react';

interface ShortcodeAttribute {
	name: string;
	default: string;
	description: string;
}

interface ShortcodeDef {
	name: string;
	tag: string;
	description: string;
	attributes: ShortcodeAttribute[];
}

const shortcodes: ShortcodeDef[] = [
	{
		name: __( 'Reservation Form', 'smooth-restaurant' ),
		tag: '[smooth_reservation]',
		description: __( 'Display the reservation form on any page or post.', 'smooth-restaurant' ),
		attributes: [
			{ name: 'style', default: 'full', description: __( 'Form style: full or compact', 'smooth-restaurant' ) },
			{ name: 'redirect', default: '', description: __( 'Optional URL to redirect after submission', 'smooth-restaurant' ) },
		],
	},
	{
		name: __( 'Full Menu', 'smooth-restaurant' ),
		tag: '[smooth_menu]',
		description: __( 'Display the full restaurant menu.', 'smooth-restaurant' ),
		attributes: [
			{ name: 'category', default: '', description: __( 'Filter by category slug', 'smooth-restaurant' ) },
			{ name: 'layout', default: 'grid', description: __( 'Layout: grid, list, or compact', 'smooth-restaurant' ) },
			{ name: 'columns', default: '2', description: __( 'Columns: 1-4', 'smooth-restaurant' ) },
		],
	},
];

export const Shortcodes = () => {
	const [ copiedTag, setCopiedTag ] = useState<string | null>( null );

	const copyToClipboard = useCallback( ( text: string ) => {
		const doCopy = () => {
			setCopiedTag( text );
			setTimeout( () => setCopiedTag( null ), 2000 );
		};

		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( text ).then( doCopy ).catch( () => {
				// Fallback for non-secure contexts.
				const textarea = document.createElement( 'textarea' );
				textarea.value = text;
				textarea.style.position = 'fixed';
				textarea.style.left = '-9999px';
				document.body.appendChild( textarea );
				textarea.focus();
				textarea.select();
				try {
					document.execCommand( 'copy' );
					doCopy();
				} catch {
					// Silently fail.
				}
				document.body.removeChild( textarea );
			} );
		} else {
			// Fallback for browsers without clipboard API.
			const textarea = document.createElement( 'textarea' );
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.left = '-9999px';
			document.body.appendChild( textarea );
			textarea.focus();
			textarea.select();
			try {
				document.execCommand( 'copy' );
				doCopy();
			} catch {
				// Silently fail.
			}
			document.body.removeChild( textarea );
		}
	}, [] );

	return (
		<div className="sr-flex sr-flex-col sr-gap-6">
			<div className="sr-flex sr-items-center sr-justify-between">
				<h1 className="sr-text-2xl sr-font-bold sr-text-sr-text">{ __( 'Shortcodes', 'smooth-restaurant' ) }</h1>
				{ copiedTag && (
					<Badge variant="default">
						<Check className="sr-size-3" />
						{ __( 'Copied!', 'smooth-restaurant' ) }
					</Badge>
				) }
			</div>

			<div className="sr-flex sr-flex-col sr-gap-4">
				{ shortcodes.map( ( shortcode ) => (
					<Card key={ shortcode.tag }>
						<CardHeader>
							<div className="sr-flex sr-items-center sr-justify-between">
								<div>
									<CardTitle>{ shortcode.name }</CardTitle>
									<CardDescription>{ shortcode.description }</CardDescription>
								</div>
								<Button
									onClick={ () => copyToClipboard( shortcode.tag ) }
									size="sm"
								>
									<Copy className="sr-size-4" />
									{ __( 'Copy', 'smooth-restaurant' ) }
								</Button>
							</div>
						</CardHeader>
						<CardContent>
							<div className="sr-rounded-sr-md sr-bg-muted sr-p-4 sr-font-mono sr-text-sm sr-text-sr-text">
								{ shortcode.tag }
							</div>
						</CardContent>
						{ shortcode.attributes.length > 0 && (
							<>
								<Separator />
								<CardFooter className="sr-flex-col sr-items-start sr-gap-4">
									<h4 className="sr-text-sm sr-font-semibold sr-uppercase sr-tracking-wider sr-text-sr-text-secondary">
										{ __( 'Attributes', 'smooth-restaurant' ) }
									</h4>
									<Table>
										<TableHeader>
											<TableRow>
												<TableHead>{ __( 'Name', 'smooth-restaurant' ) }</TableHead>
												<TableHead>{ __( 'Default', 'smooth-restaurant' ) }</TableHead>
												<TableHead>{ __( 'Description', 'smooth-restaurant' ) }</TableHead>
											</TableRow>
										</TableHeader>
										<TableBody>
											{ shortcode.attributes.map( ( attr ) => (
												<TableRow key={ attr.name }>
													<TableCell className="sr-font-mono sr-text-sr-primary">{ attr.name }</TableCell>
													<TableCell className="sr-text-sr-text-secondary">{ attr.default || '—' }</TableCell>
													<TableCell className="sr-text-sr-text-secondary">{ attr.description }</TableCell>
												</TableRow>
											) ) }
										</TableBody>
									</Table>
								</CardFooter>
							</>
						) }
					</Card>
				) ) }
			</div>
		</div>
	);
};

export default Shortcodes;
