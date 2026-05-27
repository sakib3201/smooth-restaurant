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

		wp_enqueue_script(
			'smooth-restaurant-reservation-form',
			SR_PLUGIN_URL . 'assets/build/frontend/reservation-form.js',
			array(),
			SR_VERSION,
			true
		);

		wp_enqueue_style(
			'smooth-restaurant-reservation-form',
			SR_PLUGIN_URL . 'assets/build/frontend/reservation-form.css',
			array(),
			SR_VERSION
		);

		wp_localize_script( 'smooth-restaurant-reservation-form', 'srReservation', array(
			'restUrl'       => esc_url_raw( rest_url( 'smooth-restaurant/v1' ) ),
			'restNonce'     => wp_create_nonce( 'wp_rest' ),
			'maxBookingDays' => (int) \SmoothRestaurant\Reservation\Settings::get_max_booking_window(),
			'minAdvanceHours' => \SmoothRestaurant\Reservation\Settings::get_min_advance(),
			'labels'        => array(
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
		<div class="sr-reservation-form-wrapper sr-max-w-lg sr-mx-auto sr-p-6" data-redirect="<?php echo esc_attr( $atts['redirect'] ); ?>">
			<form id="sr-reservation-form" class="sr-space-y-4" novalidate>
				<input type="hidden" name="website" value="" tabindex="-1" autocomplete="off" />

				<div class="sr-grid sr-grid-cols-2 sr-gap-4">
					<div>
						<label for="sr-reservation-date" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
							<?php esc_html_e( 'Date', 'smooth-restaurant' ); ?> *
						</label>
						<input type="date" id="sr-reservation-date" name="reservation_date" required
							class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm"
							min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" />
					</div>
					<div>
						<label for="sr-party-size" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
							<?php esc_html_e( 'Party Size', 'smooth-restaurant' ); ?> *
						</label>
						<select id="sr-party-size" name="party_size" required
							class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm">
							<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
								<option value="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( (string) $i ); ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>

				<div>
					<label for="sr-time-slot" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
						<?php esc_html_e( 'Time', 'smooth-restaurant' ); ?> *
					</label>
					<select id="sr-time-slot" name="time" required disabled
						class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm">
						<option value=""><?php esc_html_e( 'Select a date first', 'smooth-restaurant' ); ?></option>
					</select>
				</div>

				<div>
					<label for="sr-customer-name" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
						<?php esc_html_e( 'Name', 'smooth-restaurant' ); ?> *
					</label>
					<input type="text" id="sr-customer-name" name="customer_name" required
						class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm" />
				</div>

				<div>
					<label for="sr-customer-phone" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
						<?php esc_html_e( 'Phone', 'smooth-restaurant' ); ?> *
					</label>
					<input type="tel" id="sr-customer-phone" name="customer_phone" required
						class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm" />
				</div>

				<div>
					<label for="sr-customer-email" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
						<?php esc_html_e( 'Email (optional)', 'smooth-restaurant' ); ?>
					</label>
					<input type="email" id="sr-customer-email" name="customer_email"
						class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm" />
				</div>

				<div>
					<label for="sr-special-requests" class="sr-block sr-text-sm sr-font-medium sr-text-gray-700">
						<?php esc_html_e( 'Special Requests', 'smooth-restaurant' ); ?>
					</label>
					<textarea id="sr-special-requests" name="special_requests" rows="3"
						class="sr-mt-1 sr-block sr-w-full sr-rounded-md sr-border-gray-300 sr-shadow-sm focus:sr-border-indigo-500 focus:sr-ring-indigo-500 sm:sr-text-sm"></textarea>
				</div>

				<div id="sr-reservation-errors" class="sr-hidden sr-rounded-md sr-bg-red-50 sr-p-4">
					<div class="sr-flex">
						<div class="sr-ml-3">
							<h3 class="sr-text-sm sr-font-medium sr-text-red-800"><?php esc_html_e( 'Please fix the following errors:', 'smooth-restaurant' ); ?></h3>
							<ul class="sr-mt-2 sr-text-sm sr-text-red-700" role="list"></ul>
						</div>
					</div>
				</div>

				<div id="sr-reservation-success" class="sr-hidden sr-rounded-md sr-bg-green-50 sr-p-4">
					<div class="sr-flex">
						<div class="sr-ml-3">
							<h3 class="sr-text-sm sr-font-medium sr-text-green-800"><?php esc_html_e( 'Reservation Received', 'smooth-restaurant' ); ?></h3>
							<p class="sr-mt-2 sr-text-sm sr-text-green-700"><?php esc_html_e( 'Thank you! We will confirm your reservation shortly.', 'smooth-restaurant' ); ?></p>
						</div>
					</div>
				</div>

				<button type="submit" id="sr-submit-reservation"
					class="sr-inline-flex sr-justify-center sr-rounded-md sr-border sr-border-transparent sr-bg-indigo-600 sr-py-2 sr-px-4 sr-text-sm sr-font-medium sr-text-white sr-shadow-sm hover:sr-bg-indigo-700 focus:sr-outline-none focus:sr-ring-2 focus:sr-ring-indigo-500 focus:sr-ring-offset-2">
					<?php esc_html_e( 'Request Reservation', 'smooth-restaurant' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}
