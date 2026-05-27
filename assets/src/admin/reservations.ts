/**
 * Admin reservations list table interactions and modal.
 *
 * @package SmoothRestaurant
 */

import './reservations.scss';

document.addEventListener( 'DOMContentLoaded', () => {
	const modal = document.getElementById( 'sr-reservation-modal' );
	const modalTitle = document.getElementById( 'sr-modal-title' );
	const modalContent = document.getElementById( 'sr-modal-content' );
	const modalClose = document.getElementById( 'sr-modal-close' );

	if ( ! modal || ! modalTitle || ! modalContent || ! modalClose ) {
		return;
	}

	/**
	 * Open the modal.
	 */
	function openModal(): void {
		modal.classList.remove( 'sr-hidden' );
		document.body.style.overflow = 'hidden';
	}

	/**
	 * Close the modal.
	 */
	function closeModal(): void {
		modal.classList.add( 'sr-hidden' );
		document.body.style.overflow = '';
		modalContent.innerHTML = '';
	}

	modalClose.addEventListener( 'click', closeModal );

	modal.addEventListener( 'click', ( e ) => {
		if ( e.target === modal ) {
			closeModal();
		}
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && ! modal.classList.contains( 'sr-hidden' ) ) {
			closeModal();
		}
	} );

	/**
	 * Fetch reservation details via AJAX.
	 *
	 * @param {number} id Reservation ID.
	 */
	function fetchReservation( id: number ): void {
		const data = new URLSearchParams();
		data.append( 'action', 'sr_get_reservation' );
		data.append( 'id', String( id ) );
		data.append( 'nonce', ( window as any ).srAdminReservations.nonce );

		fetch( ( window as any ).srAdminReservations.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: data.toString(),
		} )
			.then( ( response ) => response.json() )
			.then( ( result ) => {
				if ( result.success ) {
					renderReservation( result.data );
					openModal();
				} else {
					// eslint-disable-next-line no-alert
					alert( result.data || 'Error loading reservation.' );
				}
			} )
			.catch( () => {
				// eslint-disable-next-line no-alert
				alert( 'Network error.' );
			} );
	}

	/**
	 * Render reservation data into the modal.
	 *
	 * @param {any} data Reservation object.
	 */
	function renderReservation( data: any ): void {
		modalTitle.textContent = `${ data.customer_name || '' } — ${ __( 'Reservation', 'smooth-restaurant' ) } #${ data.id }`;

		const statusBadges: Record<string, string> = {
			pending: 'sr-badge-pending',
			confirmed: 'sr-badge-confirmed',
			cancelled: 'sr-badge-cancelled',
			'no-show': 'sr-badge-no-show',
		};

		const statusClass = statusBadges[ data.status ] || statusBadges.pending;

		modalContent.innerHTML = `
			<div class="sr-space-y-3">
				<div class="sr-flex sr-justify-between sr-items-center">
					<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Status', 'smooth-restaurant' ) }</span>
					<span class="sr-badge ${ statusClass }">${ escapeHtml( data.status ) }</span>
				</div>
				<div class="sr-grid sr-grid-cols-2 sr-gap-4">
					<div>
						<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Date', 'smooth-restaurant' ) }</span>
						<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( data.reservation_date ) }</p>
					</div>
					<div>
						<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Time', 'smooth-restaurant' ) }</span>
						<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( data.time ) }</p>
					</div>
					<div>
						<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Party Size', 'smooth-restaurant' ) }</span>
						<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( String( data.party_size ) ) }</p>
					</div>
					<div>
						<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Phone', 'smooth-restaurant' ) }</span>
						<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( data.customer_phone || '' ) }</p>
					</div>
				</div>
				<div>
					<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Email', 'smooth-restaurant' ) }</span>
					<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( data.customer_email || '' ) }</p>
				</div>
				<div>
					<span class="sr-text-sm sr-font-medium sr-text-gray-500">${ __( 'Special Requests', 'smooth-restaurant' ) }</span>
					<p class="sr-text-sm sr-text-gray-900">${ escapeHtml( data.special_requests || __( 'None', 'smooth-restaurant' ) ) }</p>
				</div>
				<div class="sr-flex sr-gap-2 sr-pt-2" id="sr-modal-actions"></div>
			</div>
		`;

		const actionsContainer = document.getElementById( 'sr-modal-actions' );
		if ( ! actionsContainer ) {
			return;
		}

		const transitions: Record<string, string[]> = {
			pending: [ 'confirmed', 'cancelled' ],
			confirmed: [ 'cancelled', 'no-show' ],
		};

		const labels: Record<string, string> = {
			confirmed: __( 'Confirm', 'smooth-restaurant' ),
			cancelled: __( 'Cancel', 'smooth-restaurant' ),
			'no-show': __( 'No Show', 'smooth-restaurant' ),
		};

		const nextStatuses = transitions[ data.status ] || [];

		nextStatuses.forEach( ( status: string ) => {
			const btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = `sr-inline-flex sr-justify-center sr-rounded-md sr-px-3 sr-py-2 sr-text-sm sr-font-semibold sr-text-white sr-shadow-sm ${ getStatusButtonClass( status ) }`;
			btn.textContent = labels[ status ] || status;
			btn.addEventListener( 'click', () => updateStatus( data.id, status ) );
			actionsContainer.appendChild( btn );
		} );
	}

	/**
	 * Get button color class for a status action.
	 *
	 * @param {string} status
	 * @return {string}
	 */
	function getStatusButtonClass( status: string ): string {
		switch ( status ) {
			case 'confirmed':
				return 'sr-bg-green-600 hover:sr-bg-green-500';
			case 'cancelled':
				return 'sr-bg-red-600 hover:sr-bg-red-500';
			case 'no-show':
				return 'sr-bg-gray-600 hover:sr-bg-gray-500';
			default:
				return 'sr-bg-blue-600 hover:sr-bg-blue-500';
		}
	}

	/**
	 * Update reservation status via AJAX.
	 *
	 * @param {number} id     Reservation ID.
	 * @param {string} status New status.
	 */
	function updateStatus( id: number, status: string ): void {
		const data = new URLSearchParams();
		data.append( 'action', 'sr_update_reservation_status' );
		data.append( 'id', String( id ) );
		data.append( 'status', status );
		data.append( 'nonce', ( window as any ).srAdminReservations.nonce );

		fetch( ( window as any ).srAdminReservations.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: data.toString(),
		} )
			.then( ( response ) => response.json() )
			.then( ( result ) => {
				if ( result.success ) {
					window.location.reload();
				} else {
					// eslint-disable-next-line no-alert
					alert( result.data || 'Error updating status.' );
				}
			} )
			.catch( () => {
				// eslint-disable-next-line no-alert
				alert( 'Network error.' );
			} );
	}

	/**
	 * Escape HTML entities.
	 *
	 * @param {string} text
	 * @return {string}
	 */
	function escapeHtml( text: string ): string {
		const div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Simple i18n helper — falls back to the text itself.
	 * WordPress will provide the real __() via wp-i18n if loaded,
	 * but for safety we define a minimal fallback here.
	 *
	 * @param {string} text
	 * @return {string}
	 */
	function __( text: string, _domain?: string ): string {
		if ( typeof ( window as any ).wp !== 'undefined' && ( window as any ).wp.i18n && ( window as any ).wp.i18n.__( text ) ) {
			return ( window as any ).wp.i18n.__( text );
		}
		return text;
	}

	// Delegate clicks on "View" row action links.
	document.addEventListener( 'click', ( e ) => {
		const target = e.target as HTMLElement;
		const link = target.closest( '.sr-view-reservation' ) as HTMLElement | null;
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		const id = parseInt( link.dataset.id || '0', 10 );
		if ( id ) {
			fetchReservation( id );
		}
	} );
} );
