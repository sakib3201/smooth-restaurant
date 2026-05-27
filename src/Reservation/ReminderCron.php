<?php
/**
 * Reservation reminder cron handler.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class ReminderCron
 *
 * Registers and handles the twice-hourly reminder cron job.
 */
class ReminderCron {

	/**
	 * The cron hook name.
	 *
	 * @var string
	 */
	private const HOOK = 'sr_reservation_reminders';

	/**
	 * The cron schedule name.
	 *
	 * @var string
	 */
	private const SCHEDULE = 'sr_twice_hourly';

	/**
	 * The repository instance.
	 *
	 * @var Repository
	 */
	private Repository $repository;

	/**
	 * The email service instance.
	 *
	 * @var EmailService
	 */
	private EmailService $email_service;

	/**
	 * Constructor.
	 *
	 * @param Repository   $repository    Repository instance.
	 * @param EmailService $email_service Email service instance.
	 */
	public function __construct( Repository $repository, EmailService $email_service ) {
		$this->repository    = $repository;
		$this->email_service = $email_service;
	}

	/**
	 * Register cron hooks and schedules.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'cron_schedules', array( $this, 'add_twice_hourly_schedule' ) );
		add_action( self::HOOK, array( $this, 'run' ) );
	}

	/**
	 * Add a twice-hourly cron schedule.
	 *
	 * @param array<string, array<string, int|string>> $schedules Existing cron schedules.
	 * @return array<string, array<string, int|string>>
	 */
	public function add_twice_hourly_schedule( array $schedules ): array {
		$schedules[ self::SCHEDULE ] = array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => __( 'Twice Hourly', 'smooth-restaurant' ),
		);

		return $schedules;
	}

	/**
	 * Schedule the reminder cron event if not already scheduled.
	 *
	 * @return void
	 */
	public static function schedule(): void {
		if ( false === wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), self::SCHEDULE, self::HOOK );
		}
	}

	/**
	 * Unschedule the reminder cron event.
	 *
	 * @return void
	 */
	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}

	/**
	 * Run the reminder job: find upcoming confirmed reservations and send reminders.
	 *
	 * @return void
	 */
	public function run(): void {
		$reservations = $this->repository->find_upcoming_for_reminders( 2 );

		foreach ( $reservations as $reservation ) {
			$this->email_service->send_reminder( $reservation );
			$this->repository->update( (int) $reservation->id, array( 'reminder_sent' => 1 ) );
		}
	}
}
