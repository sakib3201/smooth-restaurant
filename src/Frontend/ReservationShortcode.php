<?php
/**
 * Reservation shortcode.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Frontend;

/**
 * Class ReservationShortcode
 *
 * Registers and renders the [smooth_reservation] shortcode.
 */
class ReservationShortcode {

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'smooth_reservation', array( $this, 'render' ) );
	}

	/**
	 * Render the reservation form.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render( array $atts ): string {
		$atts = shortcode_atts( array(
			'style'    => 'full',
			'redirect' => '',
		), $atts, 'smooth_reservation' );

		$asset_file = SR_PLUGIN_DIR . 'assets/build/frontend/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => SR_VERSION,
			);

		wp_enqueue_script(
			'smooth-restaurant-reservation-form',
			SR_PLUGIN_URL . 'assets/build/frontend/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'smooth-restaurant-reservation-form',
			SR_PLUGIN_URL . 'assets/build/frontend/style-index.css',
			array(),
			$asset['version']
		);

		wp_localize_script( 'smooth-restaurant-reservation-form', 'srReservation', array(
			'restUrl'         => esc_url_raw( rest_url( 'smooth-restaurant/v1' ) ),
			'restNonce'       => wp_create_nonce( 'wp_rest' ),
			'maxBookingDays'  => (int) \SmoothRestaurant\Reservation\Settings::get_max_booking_window(),
			'minAdvanceHours' => \SmoothRestaurant\Reservation\Settings::get_min_advance(),
			'labels'          => array(
				'selectDate'       => __( 'Select a date', 'smooth-restaurant' ),
				'selectTime'       => __( 'Select a time', 'smooth-restaurant' ),
				'noSlots'          => __( 'No available slots for this date.', 'smooth-restaurant' ),
				'submitting'       => __( 'Submitting...', 'smooth-restaurant' ),
				'submit'           => __( 'Request Reservation', 'smooth-restaurant' ),
				'successTitle'     => __( 'Reservation Received', 'smooth-restaurant' ),
				'successMessage'   => __( 'Thank you! We will confirm your reservation shortly.', 'smooth-restaurant' ),
				'errorTitle'       => __( 'Something went wrong', 'smooth-restaurant' ),
				'partySize'        => __( 'Party Size', 'smooth-restaurant' ),
				'name'             => __( 'Name', 'smooth-restaurant' ),
				'phone'            => __( 'Phone', 'smooth-restaurant' ),
				'email'            => __( 'Email (optional)', 'smooth-restaurant' ),
				'specialRequests'  => __( 'Special Requests', 'smooth-restaurant' ),
			),
		) );

		ob_start();
		?>
		<div class="sr-reservation-form-wrapper" data-redirect="<?php echo esc_attr( $atts['redirect'] ); ?>">
			<div class="sr-form-card">
				<div class="sr-form-header">
					<h2><?php esc_html_e( 'Book a Table', 'smooth-restaurant' ); ?></h2>
					<p><?php esc_html_e( 'Reserve your spot and we will confirm shortly.', 'smooth-restaurant' ); ?></p>
				</div>

				<div class="sr-form-body">
					<form id="sr-reservation-form" novalidate>
						<input type="hidden" name="website" value="" tabindex="-1" autocomplete="off" />

						<div class="sr-form-grid">
							<div class="sr-form-field sr-date-field">
								<label for="sr-reservation-date">
									<?php esc_html_e( 'Date', 'smooth-restaurant' ); ?>
									<span class="sr-required" aria-hidden="true">*</span>
								</label>
								<div class="sr-date-input-wrapper">
									<input
										type="date"
										id="sr-reservation-date"
										name="reservation_date"
										required
										min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>"
										aria-required="true"
									/>
									<button type="button" class="sr-date-trigger" aria-label="<?php esc_attr_e( 'Open date picker', 'smooth-restaurant' ); ?>">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
											<path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
										</svg>
									</button>
								</div>
							</div>

							<div class="sr-form-field">
								<label for="sr-party-size">
									<?php esc_html_e( 'Party Size', 'smooth-restaurant' ); ?>
									<span class="sr-required" aria-hidden="true">*</span>
								</label>
								<select id="sr-party-size" name="party_size" required aria-required="true">
									<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
										<option value="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( (string) $i ); ?></option>
									<?php endfor; ?>
								</select>
							</div>

							<div class="sr-form-field sr-full-width">
								<label for="sr-time-slot">
									<?php esc_html_e( 'Time', 'smooth-restaurant' ); ?>
									<span class="sr-required" aria-hidden="true">*</span>
								</label>
								<select id="sr-time-slot" name="time" required disabled aria-required="true" aria-describedby="sr-time-slot-help">
									<option value=""><?php esc_html_e( 'Select a date first', 'smooth-restaurant' ); ?></option>
								</select>
								<div id="sr-time-slot-loading" class="sr-loading-indicator sr-hidden" aria-live="polite" aria-busy="true">
									<span class="sr-spinner" aria-hidden="true"></span>
									<span><?php esc_html_e( 'Loading available times...', 'smooth-restaurant' ); ?></span>
								</div>
								<span id="sr-time-slot-help" class="sr-field-help sr-hidden">
									<?php esc_html_e( 'Choose a date and party size to see available times.', 'smooth-restaurant' ); ?>
								</span>
							</div>

							<div class="sr-form-field sr-full-width">
								<label for="sr-customer-name">
									<?php esc_html_e( 'Name', 'smooth-restaurant' ); ?>
									<span class="sr-required" aria-hidden="true">*</span>
								</label>
								<input
									type="text"
									id="sr-customer-name"
									name="customer_name"
									required
									placeholder="<?php esc_attr_e( 'Your full name', 'smooth-restaurant' ); ?>"
									aria-required="true"
								/>
							</div>

							<div class="sr-form-field">
								<label for="sr-customer-phone">
									<?php esc_html_e( 'Phone', 'smooth-restaurant' ); ?>
									<span class="sr-required" aria-hidden="true">*</span>
								</label>
								<input
									type="tel"
									id="sr-customer-phone"
									name="customer_phone"
									required
									placeholder="<?php esc_attr_e( 'Phone number', 'smooth-restaurant' ); ?>"
									aria-required="true"
								/>
							</div>

							<div class="sr-form-field">
								<label for="sr-customer-email">
									<?php esc_html_e( 'Email', 'smooth-restaurant' ); ?>
								</label>
								<input
									type="email"
									id="sr-customer-email"
									name="customer_email"
									placeholder="<?php esc_attr_e( 'Optional email', 'smooth-restaurant' ); ?>"
								/>
							</div>

							<div class="sr-form-field sr-full-width">
								<label for="sr-special-requests">
									<?php esc_html_e( 'Special Requests', 'smooth-restaurant' ); ?>
								</label>
								<textarea
									id="sr-special-requests"
									name="special_requests"
									rows="3"
									placeholder="<?php esc_attr_e( 'Dietary restrictions, seating preferences, etc.', 'smooth-restaurant' ); ?>"
								></textarea>
							</div>
						</div>

						<div id="sr-reservation-errors" class="sr-alert sr-alert-error sr-hidden sr-mt-5" role="alert" aria-live="assertive">
							<svg class="sr-alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
							</svg>
							<div class="sr-alert-content">
								<h3><?php esc_html_e( 'Please fix the following errors:', 'smooth-restaurant' ); ?></h3>
								<ul role="list"></ul>
							</div>
						</div>

						<div id="sr-reservation-success" class="sr-alert sr-alert-success sr-hidden sr-mt-5" role="status" aria-live="polite">
							<svg class="sr-alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
							</svg>
							<div class="sr-alert-content">
								<h3><?php esc_html_e( 'Reservation Received', 'smooth-restaurant' ); ?></h3>
								<p><?php esc_html_e( 'Thank you! We will confirm your reservation shortly.', 'smooth-restaurant' ); ?></p>
							</div>
						</div>

						<div class="sr-mt-6">
							<button type="submit" id="sr-submit-reservation" class="sr-submit-btn">
								<?php esc_html_e( 'Request Reservation', 'smooth-restaurant' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
