/**
 * Smooth content gate for the frontend bundle.
 *
 * Mirrors the PHP `smooth_should_load()` contract in `AssetsProvider`:
 * the frontend bundle MUST only boot on pages carrying Smooth content.
 * Smooth blocks render with Gutenberg's `wp-block-smooth-*` class
 * convention; `data-smooth` is the explicit escape hatch for shortcode
 * and template output.
 */

export const SMOOTH_MARKER_SELECTOR =
	'[data-smooth], [class*="wp-block-smooth-"]';

/**
 * Whether the given root contains Smooth content.
 *
 * @param root Node to scan. Defaults to the document.
 * @return True when Smooth markers are present.
 */
export function hasSmoothContent( root: ParentNode = document ): boolean {
	return root.querySelector( SMOOTH_MARKER_SELECTOR ) !== null;
}
