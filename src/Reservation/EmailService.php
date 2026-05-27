<?php
/**
 * Reservation email service.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class EmailService
 *
 * Handles sending reservation emails with template rendering.
 */
class EmailService {

	/**
	 * The repository instance.
	 *
	 * @var Repository
	 */
	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository $repository Repository instance.
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Send new reservation email to customer.
	 *
	 * @param object $reservation Reservation object.
	 * @return void
	 */
	public function send_new( object $reservation ): void {
		if ( empty( $reservation->customer_email ) ) {
			return;
		}

		$subject = $this->render_template(
			Settings::get( 'email_new_subject', 'Your reservation request at {restaurant_name}' ),
			$reservation
		);
		$body    = $this->render_template(
			Settings::get( 'email_new_body', '' ),
			$reservation
		);

		$this->send_email( sanitize_email( $reservation->customer_email ), $subject, $body );
	}

	/**
	 * Send confirmation email to customer and restaurant.
	 *
	 * @param object $reservation Reservation object.
	 * @return void
	 */
	public function send_confirmed( object $reservation ): void {
		$subject = $this->render_template(
			Settings::get( 'email_confirmed_subject', 'Your reservation is confirmed at {restaurant_name}' ),
			$reservation
		);
		$body    = $this->render_template(
			Settings::get( 'email_confirmed_body', '' ),
			$reservation
		);

		if ( ! empty( $reservation->customer_email ) ) {
			$this->send_email( sanitize_email( $reservation->customer_email ), $subject, $body );
		}

		$admin_email = $this->get_admin_email();
		if ( ! empty( $admin_email ) ) {
			$this->send_email( $admin_email, $subject, $body );
		}
	}

	/**
	 * Send reminder email to customer.
	 *
	 * @param object $reservation Reservation object.
	 * @return void
	 */
	public function send_reminder( object $reservation ): void {
		if ( empty( $reservation->customer_email ) ) {
			return;
		}

		$subject = $this->render_template(
			Settings::get( 'email_reminder_subject', 'Reminder: Your reservation at {restaurant_name} today' ),
			$reservation
		);
		$body    = $this->render_template(
			Settings::get( 'email_reminder_body', '' ),
			$reservation
		);

		$this->send_email( sanitize_email( $reservation->customer_email ), $subject, $body );
	}

	/**
	 * Send cancellation email to customer and restaurant.
	 *
	 * @param object $reservation Reservation object.
	 * @return void
	 */
	public function send_cancelled( object $reservation ): void {
		$subject = $this->render_template(
			Settings::get( 'email_cancelled_subject', 'Your reservation at {restaurant_name} has been cancelled' ),
			$reservation
		);
		$body    = $this->render_template(
			Settings::get( 'email_cancelled_body', '' ),
			$reservation
		);

		if ( ! empty( $reservation->customer_email ) ) {
			$this->send_email( sanitize_email( $reservation->customer_email ), $subject, $body );
		}

		$admin_email = $this->get_admin_email();
		if ( ! empty( $admin_email ) ) {
			$this->send_email( $admin_email, $subject, $body );
		}
	}

	/**
	 * Render a template string by replacing variables with reservation data.
	 *
	 * @param string $template    The template string.
	 * @param object $reservation Reservation object.
	 * @return string
	 */
	public function render_template( string $template, object $reservation ): string {
		$replacements = array(
			'{customer_name}'    => sanitize_text_field( $reservation->customer_name ?? '' ),
			'{party_size}'       => (int) ( $reservation->party_size ?? 0 ),
			'{reservation_date}' => sanitize_text_field( $reservation->reservation_date ?? '' ),
			'{reservation_time}' => sanitize_text_field( $reservation->time ?? '' ),
			'{restaurant_name}'  => $this->get_restaurant_name(),
			'{restaurant_phone}' => sanitize_text_field( get_option( 'smooth_restaurant_phone', '' ) ),
			'{status}'           => sanitize_text_field( $reservation->status ?? '' ),
		);

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$template
		);
	}

	/**
	 * Get the restaurant name from settings or blog name.
	 *
	 * @return string
	 */
	public function get_restaurant_name(): string {
		$name = get_option( 'smooth_restaurant_name', '' );
		if ( ! empty( $name ) ) {
			return sanitize_text_field( $name );
		}

		return sanitize_text_field( get_bloginfo( 'name' ) );
	}

	/**
	 * Send an email using wp_mail().
	 *
	 * @param string $to      Recipient email.
	 * @param string $subject Email subject.
	 * @param string $body    Email body.
	 * @return void
	 */
	private function send_email( string $to, string $subject, string $body ): void {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$plain   = wp_strip_all_tags( $body );

		wp_mail( $to, sanitize_text_field( $subject ), $plain, $headers );
	}

	/**
	 * Get the admin email address.
	 *
	 * @return string
	 */
	private function get_admin_email(): string {
		$email = get_option( 'smooth_restaurant_admin_email', '' );
		if ( ! empty( $email ) ) {
			return sanitize_email( $email );
		}

		return sanitize_email( get_option( 'admin_email', '' ) );
	}
}
