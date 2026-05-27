/**
 * Reservation form frontend logic.
 * Aligned with Smooth Restaurant design system.
 */
import './style.scss';

interface SRLabels {
	selectDate: string;
	selectTime: string;
	noSlots: string;
	submitting: string;
	submit: string;
	successTitle: string;
	successMessage: string;
	errorTitle: string;
	partySize: string;
	name: string;
	phone: string;
	email: string;
	specialRequests: string;
}

interface SRConfig {
	restUrl: string;
	restNonce: string;
	maxBookingDays: number;
	minAdvanceHours: number;
	labels: SRLabels;
}

declare const srReservation: SRConfig;

document.addEventListener( 'DOMContentLoaded', () => {
	const form = document.getElementById( 'sr-reservation-form' ) as HTMLFormElement | null;
	if ( ! form ) {
		return;
	}

	const dateInput = document.getElementById( 'sr-reservation-date' ) as HTMLInputElement;
	const dateTrigger = document.querySelector( '.sr-date-trigger' ) as HTMLButtonElement | null;
	const partyInput = document.getElementById( 'sr-party-size' ) as HTMLSelectElement;
	const timeSelect = document.getElementById( 'sr-time-slot' ) as HTMLSelectElement;
	const timeLoading = document.getElementById( 'sr-time-slot-loading' ) as HTMLElement;
	const timeHelp = document.getElementById( 'sr-time-slot-help' ) as HTMLElement;
	const submitBtn = document.getElementById( 'sr-submit-reservation' ) as HTMLButtonElement;
	const errorsContainer = document.getElementById( 'sr-reservation-errors' ) as HTMLElement;
	const successContainer = document.getElementById( 'sr-reservation-success' ) as HTMLElement;
	const errorsList = errorsContainer?.querySelector( 'ul' ) as HTMLUListElement;

	let recaptchaToken = '';

	async function loadRecaptcha() {
		if ( typeof grecaptcha === 'undefined' ) {
			return;
		}
		try {
			recaptchaToken = await grecaptcha.execute( 'site_key', { action: 'reservation' } );
		} catch {
			recaptchaToken = '';
		}
	}

	function setTimeLoading( isLoading: boolean ) {
		if ( isLoading ) {
			timeSelect.classList.add( 'sr-loading' );
			timeLoading.classList.remove( 'sr-hidden' );
			timeSelect.setAttribute( 'aria-busy', 'true' );
		} else {
			timeSelect.classList.remove( 'sr-loading' );
			timeLoading.classList.add( 'sr-hidden' );
			timeSelect.removeAttribute( 'aria-busy' );
		}
	}

	async function fetchSlots() {
		const date = dateInput.value;
		const partySize = parseInt( partyInput.value, 10 );

		if ( ! date ) {
			timeSelect.innerHTML = `<option value="">${ srReservation.labels.selectDate }</option>`;
			timeSelect.disabled = true;
			timeHelp.classList.remove( 'sr-hidden' );
			return;
		}

		timeSelect.innerHTML = `<option value="">${ srReservation.labels.selectTime }</option>`;
		timeSelect.disabled = true;
		timeHelp.classList.add( 'sr-hidden' );
		setTimeLoading( true );

		try {
			const response = await fetch(
				`${ srReservation.restUrl }/reservations/available-slots?date=${ encodeURIComponent( date ) }&party_size=${ partySize }`,
				{
					headers: {
						'X-WP-Nonce': srReservation.restNonce,
					},
				}
			);

			const data = await response.json();

			if ( data.success && Array.isArray( data.slots ) && data.slots.length > 0 ) {
				timeSelect.innerHTML = `<option value="">${ srReservation.labels.selectTime }</option>` +
					data.slots.map( ( slot: { time: string; remaining_capacity: number } ) =>
						`<option value="${ slot.time }">${ slot.time } (${ slot.remaining_capacity } left)</option>`
					).join( '' );
				timeSelect.disabled = false;
			} else {
				timeSelect.innerHTML = `<option value="">${ srReservation.labels.noSlots }</option>`;
				timeSelect.disabled = true;
			}
		} catch {
			timeSelect.innerHTML = `<option value="">${ srReservation.labels.noSlots }</option>`;
			timeSelect.disabled = true;
		} finally {
			setTimeLoading( false );
		}
	}

	function showErrors( errors: Record< string, string > | string[] | string ) {
		errorsContainer.classList.remove( 'sr-hidden' );
		successContainer.classList.add( 'sr-hidden' );
		errorsList.innerHTML = '';

		if ( typeof errors === 'string' ) {
			errorsList.innerHTML = `<li>${ errors }</li>`;
		} else if ( Array.isArray( errors ) ) {
			errorsList.innerHTML = errors.map( ( e ) => `<li>${ e }</li>` ).join( '' );
		} else {
			errorsList.innerHTML = Object.values( errors ).map( ( e ) => `<li>${ e }</li>` ).join( '' );
		}

		// Focus the error container for screen readers.
		errorsContainer.focus();
	}

	function showSuccess() {
		errorsContainer.classList.add( 'sr-hidden' );
		successContainer.classList.remove( 'sr-hidden' );
		form.reset();
		timeSelect.innerHTML = `<option value="">${ srReservation.labels.selectDate }</option>`;
		timeSelect.disabled = true;
		timeHelp.classList.remove( 'sr-hidden' );

		// Focus the success container for screen readers.
		successContainer.focus();
	}

	function hideMessages() {
		errorsContainer.classList.add( 'sr-hidden' );
		successContainer.classList.add( 'sr-hidden' );
	}

	dateInput.addEventListener( 'change', () => {
		hideMessages();
		fetchSlots();
	} );

	/* Open date picker when clicking the custom trigger button */
	if ( dateTrigger ) {
		dateTrigger.addEventListener( 'click', () => {
			dateInput.showPicker?.();
		} );
	}

	/* Also trigger on input for better mobile date picker support */
	dateInput.addEventListener( 'input', () => {
		hideMessages();
		fetchSlots();
	} );

	partyInput.addEventListener( 'change', () => {
		hideMessages();
		if ( dateInput.value ) {
			fetchSlots();
		}
	} );

	form.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();
		hideMessages();

		const formData = new FormData( form );
		const data: Record< string, string > = {};
		formData.forEach( ( value, key ) => {
			data[ key ] = String( value );
		} );

		if ( recaptchaToken ) {
			data[ 'g-recaptcha-response' ] = recaptchaToken;
		}

		submitBtn.disabled = true;
		submitBtn.textContent = srReservation.labels.submitting;

		try {
			const response = await fetch( `${ srReservation.restUrl }/reservations`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': srReservation.restNonce,
				},
				body: JSON.stringify( data ),
			} );

			const result = await response.json();

			if ( result.success ) {
				showSuccess();
				const wrapper = document.querySelector( '.sr-reservation-form-wrapper' ) as HTMLElement | null;
				const redirect = wrapper?.dataset.redirect;
				if ( redirect ) {
					setTimeout( () => {
						window.location.href = redirect;
					}, 2000 );
				}
			} else if ( result.errors ) {
				showErrors( result.errors );
			} else {
				showErrors( result.message || srReservation.labels.errorTitle );
			}
		} catch {
			showErrors( srReservation.labels.errorTitle );
		} finally {
			submitBtn.disabled = false;
			submitBtn.textContent = srReservation.labels.submit;
			loadRecaptcha();
		}
	} );

	// Initial load.
	fetchSlots();
} );
