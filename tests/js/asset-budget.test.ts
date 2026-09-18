/**
 * Asset budget: zero Smooth boot on non-Smooth pages.
 *
 * The frontend bundle gates on `hasSmoothContent()` (see
 * `assets/src/shared/content-gate.ts`, mirroring PHP
 * `smooth_should_load()`). This suite fails the build if that contract
 * ever admits non-Smooth markup — the 0KB leak tolerance for SMO-104.
 * Numeric KB caps land with real assets in SMO-104.
 */
import { hasSmoothContent } from '@/shared/content-gate';

describe( 'asset budget (leak check)', () => {
	it( 'stays dormant on plain non-Smooth markup', () => {
		document.body.innerHTML =
			'<article><h1>Hello</h1><p>Just a post.</p></article>';

		expect( hasSmoothContent() ).toBe( false );
	} );

	it( 'boots on Gutenberg Smooth block output', () => {
		document.body.innerHTML =
			'<div class="wp-block-smooth-menu-grid"><p>Menu</p></div>';

		expect( hasSmoothContent() ).toBe( true );
	} );

	it( 'boots on the data-smooth escape hatch', () => {
		document.body.innerHTML =
			'<div data-smooth="checkout"><p>Checkout</p></div>';

		expect( hasSmoothContent() ).toBe( true );
	} );

	it( 'ignores lookalike class names without the block prefix', () => {
		document.body.innerHTML =
			'<div class="my-smooth-section"><p>Not ours.</p></div>';

		expect( hasSmoothContent() ).toBe( false );
	} );
} );
