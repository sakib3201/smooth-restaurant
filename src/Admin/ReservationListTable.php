<?php
/**
 * Reservation admin list table.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Admin;

use SmoothRestaurant\Reservation\Repository;
use SmoothRestaurant\Reservation\Settings;

/**
 * Class ReservationListTable
 *
 * WP_List_Table for managing reservations.
 */
class ReservationListTable extends \WP_List_Table {

	/**
	 * The repository instance.
	 *
	 * @var Repository
	 */
	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository $repository Reservation repository.
	 */
	public function __construct( Repository $repository ) {
		parent::__construct(
			array(
				'singular' => __( 'Reservation', 'smooth-restaurant' ),
				'plural'   => __( 'Reservations', 'smooth-restaurant' ),
				'ajax'     => true,
			)
		);

		$this->repository = $repository;
	}

	/**
	 * Get columns.
	 *
	 * @return array<string, string>
	 */
	public function get_columns(): array {
		return array(
			'cb'               => '<input type="checkbox" />',
			'id'               => __( 'ID', 'smooth-restaurant' ),
			'reservation_date' => __( 'Date', 'smooth-restaurant' ),
			'time'             => __( 'Time', 'smooth-restaurant' ),
			'party_size'       => __( 'Party', 'smooth-restaurant' ),
			'customer_name'    => __( 'Name', 'smooth-restaurant' ),
			'customer_phone'   => __( 'Phone', 'smooth-restaurant' ),
			'status'           => __( 'Status', 'smooth-restaurant' ),
			'created_at'       => __( 'Submitted', 'smooth-restaurant' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array<string, array<string>>
	 */
	public function get_sortable_columns(): array {
		return array(
			'reservation_date' => array( 'reservation_date', true ),
			'time'             => array( 'time', true ),
			'party_size'       => array( 'party_size', false ),
			'customer_name'    => array( 'customer_name', false ),
			'status'           => array( 'status', false ),
			'created_at'       => array( 'created_at', true ),
		);
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array<string, string>
	 */
	public function get_bulk_actions(): array {
		return array(
			'confirm' => __( 'Confirm', 'smooth-restaurant' ),
			'cancel'  => __( 'Cancel', 'smooth-restaurant' ),
			'delete'  => __( 'Delete', 'smooth-restaurant' ),
		);
	}

	/**
	 * Column: checkbox.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="reservation[]" value="%s" />', esc_attr( (string) $item->id ) );
	}

	/**
	 * Column: default.
	 *
	 * @param object $item        Item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		return esc_html( (string) ( $item->$column_name ?? '' ) );
	}

	/**
	 * Column: reservation_date.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_reservation_date( $item ): string {
		$date = mysql2date( get_option( 'date_format' ), $item->reservation_date );
		return esc_html( $date );
	}

	/**
	 * Column: time.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_time( $item ): string {
		return esc_html( mysql2date( get_option( 'time_format' ), $item->time ) );
	}

	/**
	 * Column: status.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_status( $item ): string {
		$statuses = array(
			'pending'   => array(
				'label' => __( 'Pending', 'smooth-restaurant' ),
				'class' => 'sr-bg-yellow-100 sr-text-yellow-800',
			),
			'confirmed' => array(
				'label' => __( 'Confirmed', 'smooth-restaurant' ),
				'class' => 'sr-bg-green-100 sr-text-green-800',
			),
			'cancelled' => array(
				'label' => __( 'Cancelled', 'smooth-restaurant' ),
				'class' => 'sr-bg-red-100 sr-text-red-800',
			),
			'no-show'   => array(
				'label' => __( 'No Show', 'smooth-restaurant' ),
				'class' => 'sr-bg-gray-100 sr-text-gray-800',
			),
		);

		$status = $item->status ?? 'pending';
		$info   = $statuses[ $status ] ?? $statuses['pending'];

		$actions = array();
		if ( 'pending' === $status ) {
			$actions['confirm'] = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?page=smooth-restaurant-reservations&action=confirm&reservation=' . $item->id ), 'sr_reservation_action' ),
				__( 'Confirm', 'smooth-restaurant' )
			);
			$actions['cancel']  = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?page=smooth-restaurant-reservations&action=cancel&reservation=' . $item->id ), 'sr_reservation_action' ),
				__( 'Cancel', 'smooth-restaurant' )
			);
		} elseif ( 'confirmed' === $status ) {
			$actions['cancel']  = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?page=smooth-restaurant-reservations&action=cancel&reservation=' . $item->id ), 'sr_reservation_action' ),
				__( 'Cancel', 'smooth-restaurant' )
			);
			$actions['no_show'] = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?page=smooth-restaurant-reservations&action=no-show&reservation=' . $item->id ), 'sr_reservation_action' ),
				__( 'No Show', 'smooth-restaurant' )
			);
		}

		$actions['view'] = sprintf(
			'<a href="#" class="sr-view-reservation" data-id="%s">%s</a>',
			esc_attr( (string) $item->id ),
			__( 'View', 'smooth-restaurant' )
		);

		return sprintf(
			'<span class="sr-inline-flex sr-rounded-full sr-px-2 sr-text-xs sr-font-semibold %s">%s</span><br>%s',
			esc_attr( $info['class'] ),
			esc_html( $info['label'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Prepare items for display.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$per_page     = 20;
		$current_page = $this->get_pagenum();

		$args = array(
			'per_page' => $per_page,
			'paged'    => $current_page,
			'orderby'  => sanitize_text_field( $_GET['orderby'] ?? 'reservation_date' ),
			'order'    => sanitize_text_field( $_GET['order'] ?? 'ASC' ),
		);

		$status_filter = sanitize_text_field( $_GET['status_filter'] ?? '' );
		if ( $status_filter ) {
			$args['status'] = $status_filter;
		}

		$date_from = sanitize_text_field( $_GET['date_from'] ?? gmdate( 'Y-m-d' ) );
		$date_to   = sanitize_text_field( $_GET['date_to'] ?? gmdate( 'Y-m-d', strtotime( '+30 days' ) ) );

		$this->items = $this->repository->find_by_date_range( $date_from, $date_to, $args );

		$total_items = $this->repository->count_by_date_status( $date_from );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Display the filter form above the table.
	 *
	 * @return void
	 */
	public function display_tablenav( $which ): void {
		if ( 'top' === $which ) {
		?>
		<div class="sr-flex sr-gap-4 sr-mb-4 sr-items-end">
			<div>
				<label class="sr-block sr-text-sm sr-font-medium sr-text-gray-700"><?php esc_html_e( 'From', 'smooth-restaurant' ); ?></label>
					<input type="date" name="date_from" value="<?php echo esc_attr( sanitize_text_field( $_GET['date_from'] ?? gmdate( 'Y-m-d' ) ) ); ?>" class="sr-mt-1 sr-rounded-md sr-border-gray-300 sr-shadow-sm" />
				</div>
				<div>
					<label class="sr-block sr-text-sm sr-font-medium sr-text-gray-700"><?php esc_html_e( 'To', 'smooth-restaurant' ); ?></label>
					<input type="date" name="date_to" value="<?php echo esc_attr( sanitize_text_field( $_GET['date_to'] ?? gmdate( 'Y-m-d', strtotime( '+30 days' ) ) ) ); ?>" class="sr-mt-1 sr-rounded-md sr-border-gray-300 sr-shadow-sm" />
				</div>
				<div>
					<label class="sr-block sr-text-sm sr-font-medium sr-text-gray-700"><?php esc_html_e( 'Status', 'smooth-restaurant' ); ?></label>
					<select name="status_filter" class="sr-mt-1 sr-rounded-md sr-border-gray-300 sr-shadow-sm">
						<option value=""><?php esc_html_e( 'All', 'smooth-restaurant' ); ?></option>
						<option value="pending" <?php selected( sanitize_text_field( $_GET['status_filter'] ?? '' ), 'pending' ); ?>><?php esc_html_e( 'Pending', 'smooth-restaurant' ); ?></option>
						<option value="confirmed" <?php selected( sanitize_text_field( $_GET['status_filter'] ?? '' ), 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'smooth-restaurant' ); ?></option>
						<option value="cancelled" <?php selected( sanitize_text_field( $_GET['status_filter'] ?? '' ), 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'smooth-restaurant' ); ?></option>
						<option value="no-show" <?php selected( sanitize_text_field( $_GET['status_filter'] ?? '' ), 'no-show' ); ?>><?php esc_html_e( 'No Show', 'smooth-restaurant' ); ?></option>
					</select>
				</div>
				<div>
					<?php submit_button( __( 'Filter', 'smooth-restaurant' ), 'secondary', false ); ?>
				</div>
			</div>
			<?php
		}

		parent::display_tablenav( $which );
	}
}
