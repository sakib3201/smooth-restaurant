<?php
/**
 * Reservation REST API controller.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class RestController
 *
 * Handles REST API endpoints for reservations.
 */
class RestController {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	private const NAMESPACE = 'smooth-restaurant/v1';

	/**
	 * The slot engine.
	 *
	 * @var SlotEngine
	 */
	private SlotEngine $slot_engine;

	/**
	 * The repository.
	 *
	 * @var Repository
	 */
	private Repository $repository;

	/**
	 * The validator.
	 *
	 * @var Validator
	 */
	private Validator $validator;

	/**
	 * Constructor.
	 *
	 * @param SlotEngine $slot_engine Slot engine.
	 * @param Repository $repository  Repository.
	 * @param Validator  $validator   Validator.
	 */
	public function __construct( SlotEngine $slot_engine, Repository $repository, Validator $validator ) {
		$this->slot_engine = $slot_engine;
		$this->repository  = $repository;
		$this->validator   = $validator;
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/reservations/available-slots',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_available_slots' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'date'       => array(
							'required'          => true,
							'type'              => 'string',
							'validate_callback' => function ( $param ) {
								return (bool) \DateTime::createFromFormat( 'Y-m-d', $param );
							},
						),
						'party_size' => array(
							'required' => false,
							'type'     => 'integer',
							'default'  => 1,
						),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/reservations',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_reservations' ),
					'permission_callback' => function () {
						return current_user_can( 'sr_view_reservations' );
					},
					'args'                => array(
						'page'       => array(
							'required' => false,
							'type'     => 'integer',
							'default'  => 1,
						),
						'per_page'   => array(
							'required' => false,
							'type'     => 'integer',
							'default'  => 20,
						),
						'status'     => array(
							'required' => false,
							'type'     => 'string',
							'default'  => '',
						),
						'date_from'  => array(
							'required' => false,
							'type'     => 'string',
							'default'  => '',
						),
						'date_to'    => array(
							'required' => false,
							'type'     => 'string',
							'default'  => '',
						),
						'orderby'    => array(
							'required' => false,
							'type'     => 'string',
							'default'  => 'reservation_date',
						),
						'order'      => array(
							'required' => false,
							'type'     => 'string',
							'default'  => 'ASC',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_reservation' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'reservation_date'     => array(
							'required' => true,
							'type'     => 'string',
						),
						'time'                 => array(
							'required' => true,
							'type'     => 'string',
						),
						'party_size'           => array(
							'required' => true,
							'type'     => 'integer',
						),
						'customer_name'        => array(
							'required' => true,
							'type'     => 'string',
						),
						'customer_phone'       => array(
							'required' => true,
							'type'     => 'string',
						),
						'customer_email'       => array(
							'required' => false,
							'type'     => 'string',
						),
						'special_requests'     => array(
							'required' => false,
							'type'     => 'string',
						),
						'website'              => array(
							'required' => false,
							'type'     => 'string',
						),
						'g-recaptcha-response' => array(
							'required' => false,
							'type'     => 'string',
						),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/reservations/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_reservation' ),
					'permission_callback' => function () {
						return current_user_can( 'sr_view_reservations' );
					},
				),
				array(
					'methods'             => 'PATCH',
					'callback'            => array( $this, 'update_reservation' ),
					'permission_callback' => function () {
						return current_user_can( 'sr_edit_reservations' );
					},
					'args'                => array(
						'status' => array(
							'required' => true,
							'type'     => 'string',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_reservation' ),
					'permission_callback' => function () {
						return current_user_can( 'sr_delete_reservations' );
					},
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/reservations/bulk',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'bulk_reservations' ),
					'permission_callback' => function () {
						return current_user_can( 'sr_edit_reservations' );
					},
					'args'                => array(
						'action' => array(
							'required' => true,
							'type'     => 'string',
							'enum'     => array( 'confirm', 'cancel', 'delete' ),
						),
						'ids'    => array(
							'required' => true,
							'type'     => 'array',
							'items'    => array( 'type' => 'integer' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Get available time slots.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_available_slots( \WP_REST_Request $request ): \WP_REST_Response {
		$date       = sanitize_text_field( $request->get_param( 'date' ) );
		$party_size = (int) $request->get_param( 'party_size' );

		if ( ! $this->slot_engine->is_within_booking_window( $date ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Date is outside the booking window.', 'smooth-restaurant' ),
				),
				400
			);
		}

		$slots = $this->slot_engine->get_available_slots( $date, $party_size );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'slots'   => $slots,
			)
		);
	}

	/**
	 * Get paginated reservations with optional filters.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_reservations( \WP_REST_Request $request ): \WP_REST_Response {
		$page       = (int) $request->get_param( 'page' );
		$per_page   = (int) $request->get_param( 'per_page' );
		$status     = sanitize_text_field( $request->get_param( 'status' ) ?? '' );
		$date_from  = sanitize_text_field( $request->get_param( 'date_from' ) ?? '' );
		$date_to    = sanitize_text_field( $request->get_param( 'date_to' ) ?? '' );
		$orderby    = sanitize_text_field( $request->get_param( 'orderby' ) ?? 'reservation_date' );
		$order      = strtoupper( sanitize_text_field( $request->get_param( 'order' ) ?? 'ASC' ) );

		if ( ! $date_from || ! \DateTime::createFromFormat( 'Y-m-d', $date_from ) ) {
			$date_from = gmdate( 'Y-m-d' );
		}
		if ( ! $date_to || ! \DateTime::createFromFormat( 'Y-m-d', $date_to ) ) {
			$date_to = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		}

		$args = array(
			'per_page' => $per_page,
			'paged'    => $page,
			'orderby'  => $orderby,
			'order'    => $order,
		);

		if ( $status ) {
			$args['status'] = $status;
		}

		$items = $this->repository->find_by_date_range( $date_from, $date_to, $args );
		$total = $this->repository->count_by_date_range( $date_from, $date_to, $status );

		return new \WP_REST_Response(
			array(
				'items'       => $items,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Get a single reservation.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_reservation( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request->get_param( 'id' );
		$reservation = $this->repository->find( $id );

		if ( ! $reservation ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Reservation not found.', 'smooth-restaurant' ) ),
				404
			);
		}

		return new \WP_REST_Response( $reservation );
	}

	/**
	 * Update a reservation (status only for now).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_reservation( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request->get_param( 'id' );
		$status = sanitize_text_field( $request->get_param( 'status' ) ?? '' );

		$reservation = $this->repository->find( $id );
		if ( ! $reservation ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Reservation not found.', 'smooth-restaurant' ) ),
				404
			);
		}

		if ( ! $this->validator->is_valid_transition( $reservation->status, $status ) ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Invalid status transition.', 'smooth-restaurant' ) ),
				400
			);
		}

		$this->repository->update( $id, array( 'status' => $status ) );

		$email_service = new EmailService( $this->repository );
		$reservation   = $this->repository->find( $id );

		if ( 'confirmed' === $status ) {
			$email_service->send_confirmed( $reservation );
		} elseif ( 'cancelled' === $status ) {
			$email_service->send_cancelled( $reservation );
		}

		$this->slot_engine->clear_slot_cache( $reservation->reservation_date );

		return new \WP_REST_Response( array( 'status' => $status ) );
	}

	/**
	 * Delete a reservation.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_reservation( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request->get_param( 'id' );

		$reservation = $this->repository->find( $id );
		if ( ! $reservation ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Reservation not found.', 'smooth-restaurant' ) ),
				404
			);
		}

		$this->repository->delete( $id );

		return new \WP_REST_Response( array( 'deleted' => true ) );
	}

	/**
	 * Bulk update/delete reservations.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function bulk_reservations( \WP_REST_Request $request ): \WP_REST_Response {
		$action = sanitize_text_field( $request->get_param( 'action' ) ?? '' );
		$ids    = array_map( 'intval', (array) $request->get_param( 'ids' ) );

		if ( ! in_array( $action, array( 'confirm', 'cancel', 'delete' ), true ) ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Invalid bulk action.', 'smooth-restaurant' ) ),
				400
			);
		}

		$email_service = new EmailService( $this->repository );

		foreach ( $ids as $id ) {
			$reservation = $this->repository->find( $id );
			if ( ! $reservation ) {
				continue;
			}

			if ( 'delete' === $action ) {
				if ( ! current_user_can( 'sr_delete_reservations' ) ) {
					continue;
				}
				$this->repository->delete( $id );
				continue;
			}

			$new_status = sanitize_text_field( $action );
			if ( ! $this->validator->is_valid_transition( $reservation->status, $new_status ) ) {
				continue;
			}

			$this->repository->update( $id, array( 'status' => $new_status ) );

			$reservation = $this->repository->find( $id );
			if ( 'confirmed' === $new_status ) {
				$email_service->send_confirmed( $reservation );
			} elseif ( 'cancelled' === $new_status ) {
				$email_service->send_cancelled( $reservation );
			}

			$this->slot_engine->clear_slot_cache( $reservation->reservation_date );
		}

		return new \WP_REST_Response( array( 'success' => true ) );
	}

	/**
	 * Create a new reservation.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_reservation( \WP_REST_Request $request ): \WP_REST_Response {
		// Rate limiting.
		if ( ! $this->pass_rate_limit() ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Too many requests. Please try again later.', 'smooth-restaurant' ),
				),
				429
			);
		}

		$data = array(
			'reservation_date' => sanitize_text_field( $request->get_param( 'reservation_date' ) ),
			'time'             => sanitize_text_field( $request->get_param( 'time' ) ),
			'party_size'       => (int) $request->get_param( 'party_size' ),
			'customer_name'    => sanitize_text_field( $request->get_param( 'customer_name' ) ),
			'customer_phone'   => sanitize_text_field( $request->get_param( 'customer_phone' ) ),
			'customer_email'   => sanitize_email( $request->get_param( 'customer_email' ) ?? '' ),
			'special_requests' => sanitize_textarea_field( $request->get_param( 'special_requests' ) ?? '' ),
			'website'          => sanitize_text_field( $request->get_param( 'website' ) ?? '' ),
		);

		// Validate honeypot.
		if ( ! empty( $data['website'] ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Invalid submission.', 'smooth-restaurant' ),
				),
				400
			);
		}

		// Validate reCAPTCHA if enabled.
		$recaptcha_response = sanitize_text_field( $request->get_param( 'g-recaptcha-response' ) ?? '' );
		if ( ! $this->verify_recaptcha( $recaptcha_response ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Security check failed. Please try again.', 'smooth-restaurant' ),
				),
				400
			);
		}

		$errors = $this->validator->validate_create( $data );
		if ( ! empty( $errors ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'errors'  => $errors,
				),
				422
			);
		}

		// Check capacity.
		$available  = $this->slot_engine->get_available_slots( $data['reservation_date'], $data['party_size'] );
		$slot_times = array_column( $available, 'time' );
		if ( ! in_array( $data['time'], $slot_times, true ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Selected time slot is no longer available.', 'smooth-restaurant' ),
				),
				409
			);
		}

		$status         = Settings::is_auto_confirm() ? 'confirmed' : 'pending';
		$data['status'] = $status;

		$id = $this->repository->create( $data );

		if ( false === $id ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Failed to create reservation. Please try again.', 'smooth-restaurant' ),
				),
				500
			);
		}

		$this->slot_engine->clear_slot_cache( $data['reservation_date'] );

		$email_service = new EmailService( $this->repository );
		$email_service->send_new( $this->repository->find( $id ) );

		return new \WP_REST_Response(
			array(
				'success'        => true,
				'reservation_id' => $id,
				'status'         => $status,
				'message'        => 'pending' === $status
					? __( 'Reservation request received. We will confirm shortly.', 'smooth-restaurant' )
					: __( 'Your reservation is confirmed!', 'smooth-restaurant' ),
			),
			201
		);
	}

	/**
	 * Check rate limit for the current IP.
	 *
	 * @return bool
	 */
	private function pass_rate_limit(): bool {
		$ip        = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
		$transient = 'sr_reservation_rate_' . md5( $ip );
		$count     = (int) get_transient( $transient );

		if ( $count >= 5 ) {
			return false;
		}

		set_transient( $transient, $count + 1, HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Verify reCAPTCHA v3 response.
	 *
	 * @param string $response The reCAPTCHA response token.
	 * @return bool
	 */
	private function verify_recaptcha( string $response ): bool {
		if ( 'yes' !== Settings::get( 'recaptcha_enabled', 'no' ) ) {
			return true;
		}

		if ( empty( $response ) ) {
			return false;
		}

		$secret = Settings::get( 'recaptcha_secret_key', '' );
		if ( empty( $secret ) ) {
			return true;
		}

		$verify = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $secret,
					'response' => $response,
					'remoteip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
				),
			)
		);

		if ( is_wp_error( $verify ) ) {
			return true; // Fail open on network errors.
		}

		$body      = json_decode( wp_remote_retrieve_body( $verify ), true );
		$threshold = (float) Settings::get( 'recaptcha_threshold', 0.5 );

		return isset( $body['success'] ) && true === $body['success']
			&& isset( $body['score'] ) && (float) $body['score'] >= $threshold;
	}
}
