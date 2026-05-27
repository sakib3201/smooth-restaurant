<?php
/**
 * Reservation admin page controller.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Admin;

use SmoothRestaurant\Reservation\Repository;
use SmoothRestaurant\Reservation\Validator;
use SmoothRestaurant\Reservation\EmailService;
use SmoothRestaurant\Reservation\SlotEngine;

/**
 * Class ReservationAdmin
 *
 * Handles the admin reservation management page.
 */
class ReservationAdmin {

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
	 * The slot engine.
	 *
	 * @var SlotEngine
	 */
	private SlotEngine $slot_engine;

	/**
	 * Constructor.
	 *
	 * @param Repository $repository  Repository.
	 * @param Validator  $validator   Validator.
	 * @param SlotEngine $slot_engine Slot engine.
	 */
	public function __construct( Repository $repository, Validator $validator, SlotEngine $slot_engine ) {
		$this->repository  = $repository;
		$this->validator   = $validator;
		$this->slot_engine = $slot_engine;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add the reservations submenu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			'smooth-restaurant',
			__( 'Reservations', 'smooth-restaurant' ),
			__( 'Reservations', 'smooth-restaurant' ),
			'sr_view_reservations',
			'smooth-restaurant-reservations',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'smooth-restaurant_page_smooth-restaurant-reservations' !== $hook ) {
			return;
		}

		$asset_file = SR_PLUGIN_DIR . 'assets/build/admin/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => SR_VERSION,
			);

		wp_enqueue_style(
			'smooth-restaurant-admin',
			SR_PLUGIN_URL . 'assets/build/admin/style-index.css',
			array(),
			$asset['version']
		);

		wp_enqueue_script(
			'smooth-restaurant-admin',
			SR_PLUGIN_URL . 'assets/build/admin/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'smooth-restaurant-admin',
			'smoothRestaurantAdmin',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'smooth-restaurant-admin',
				'smooth-restaurant',
				SR_PLUGIN_DIR . 'languages'
			);
		}
	}

	/**
	 * Handle bulk and single actions.
	 *
	 * @return void
	 */
	public function handle_actions(): void {
		if ( ! current_user_can( 'sr_edit_reservations' ) ) {
			return;
		}

		$action = sanitize_text_field( $_GET['action'] ?? '' );
		if ( ! $action ) {
			$action = sanitize_text_field( $_GET['action2'] ?? '' );
		}

		if ( ! in_array( $action, array( 'confirm', 'cancel', 'no-show', 'delete' ), true ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sr_reservation_action' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'smooth-restaurant' ) );
		}

		$ids = array();
		if ( isset( $_GET['reservation'] ) ) {
			$ids = is_array( $_GET['reservation'] )
				? array_map( 'intval', $_GET['reservation'] )
				: array( (int) $_GET['reservation'] );
		}

		if ( empty( $ids ) ) {
			return;
		}

		$email_service = new EmailService();

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

		wp_safe_redirect( admin_url( 'admin.php?page=smooth-restaurant-reservations' ) );
		exit;
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'sr_view_reservations' ) ) {
			wp_die( esc_html__( 'You do not have permission to view reservations.', 'smooth-restaurant' ) );
		}
		echo '<div id="sr-reservations-root"></div>';
	}

}
