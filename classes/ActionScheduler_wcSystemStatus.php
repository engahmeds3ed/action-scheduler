<?php

/**
 * Class ActionScheduler_wcSystemStatus
 */
class ActionScheduler_wcSystemStatus {

	/**
	 * The active data stores
	 *
	 * @var ActionScheduler_Store
	 */
	protected $store;

	function __construct( $store ) {
		$this->store = $store;
	}

	public function print() {
		$action_counts     = $this->store->action_counts();
		$status_labels     = $this->store->get_status_labels();
		$oldest_and_newest = $this->get_oldest_and_newest( array_keys( $status_labels ) );

		$this->get_template( $status_labels, $action_counts, $oldest_and_newest );
	}

	/**
	 * Get oldest and newest scheduled dates for a given set of statuses.
	 *
	 * @param array $status_keys Set of statuses to find oldest & newest action for.
	 * @return array
	 */
	protected function get_oldest_and_newest( $status_keys ) {

		$oldest_and_newest = array();

		foreach ( $status_keys as $status ) {
			$oldest_and_newest[ $status ] = array(
				'oldest' => '&ndash;',
				'newest' => '&ndash;',
			);

			if ( 'in-progress' === $status ) {
				continue;
			}

			$oldest_and_newest[ $status ]['oldest'] = $this->get_action_status_date( $status, 'oldest' );
			$oldest_and_newest[ $status ]['newest'] = $this->get_action_status_date( $status, 'newest' );
		}

		return $oldest_and_newest;
	}

	/**
	 * Get oldest or newest scheduled date for a given status.
	 *
	 * @param string $status Action status label/name string.
	 * @param string $date_type Oldest or Newest.
	 * @return DateTime
	 */
	protected function get_action_status_date( $status, $date_type = 'oldest' ) {

		$order = 'oldest' === $date_type ? 'ASC' : 'DESC';

		$action = $this->store->query_actions( array(
			'claimed'  => false,
			'status'   => $status,
			'per_page' => 1,
			'order'    => $order,
		) );

		if ( ! empty( $action ) ) {
			$date_object = $this->store->get_date( $action[0] );
			$action_date = $date_object->format( 'Y-m-d H:i:s O' );
		} else {
			$action_date = '&ndash;';
		}

		return $action_date;
	}

	/**
	 * Get oldest or newest scheduled date for a given status.
	 *
	 * @param array $status_labels Set of statuses to find oldest & newest action for.
	 * @param array $action_counts Number of actions grouped by status.
	 * @param array $oldest_and_newest Date of the oldest and newest action with each status.
	 */
	protected function get_template( $status_labels, $action_counts, $oldest_and_newest ) {
		?>

		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="5" data-export-label="Action Scheduler"><h2><?php esc_html_e( 'Action Scheduler', 'action-scheduler' ); ?><?php echo wc_help_tip( esc_html__( 'This section shows scheduled action counts.', 'action-scheduler' ) ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $action_counts as $status => $count ) {
					printf(
						'<tr><td>%s</td><td class="help">&nbsp;</td><td>%s</td><td>%s</td><td>%s</td></tr>',
						esc_html( $labels[ $status ] ),
						number_format_i18n( $count ),
						$oldest_and_newest[ $status ]['oldest'],
						$oldest_and_newest[ $status ]['newest']
					);
				}
				?>
			</tbody>
		</table>

		<?php
	}

}
