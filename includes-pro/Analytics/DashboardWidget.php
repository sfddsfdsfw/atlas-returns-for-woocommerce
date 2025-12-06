<?php
/**
 * Dashboard widget class.
 *
 * @package AtlasReturns\Pro\Analytics
 */

namespace AtlasReturns\Pro\Analytics;

/**
 * Class DashboardWidget
 *
 * Adds a return statistics widget to the WordPress dashboard.
 */
class DashboardWidget {

	/**
	 * Widget ID.
	 */
	const WIDGET_ID = 'atlr_returns_widget';

	/**
	 * Reports instance.
	 *
	 * @var Reports
	 */
	private $reports;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->reports = new Reports();
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 */
	public function register_widget() {
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			self::WIDGET_ID,
			__( 'Atlas Returns - This Month', 'atlas-returns' ),
			array( $this, 'render_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the widget content.
	 */
	public function render_widget() {
		$date_from = gmdate( 'Y-m-01' );
		$date_to   = gmdate( 'Y-m-d' );

		$summary   = $this->reports->get_summary( $date_from, $date_to );
		$by_reason = $this->reports->get_by_reason( $date_from, $date_to );
		?>
		<div class="atlr-dashboard-widget">
			<div class="atlr-widget-stats">
				<div class="atlr-widget-stat">
					<span class="atlr-stat-value"><?php echo esc_html( $summary['total_returns'] ); ?></span>
					<span class="atlr-stat-label"><?php esc_html_e( 'Returns', 'atlas-returns' ); ?></span>
				</div>
				<div class="atlr-widget-stat">
					<span class="atlr-stat-value"><?php echo esc_html( $summary['return_rate'] ); ?>%</span>
					<span class="atlr-stat-label"><?php esc_html_e( 'Return Rate', 'atlas-returns' ); ?></span>
				</div>
				<div class="atlr-widget-stat">
					<span class="atlr-stat-value <?php echo $summary['total_cost_difference'] < 0 ? 'atlr-negative' : 'atlr-positive'; ?>">
						<?php echo wp_kses_post( wc_price( $summary['total_cost_difference'] ) ); ?>
					</span>
					<span class="atlr-stat-label"><?php esc_html_e( 'Net Cost', 'atlas-returns' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $by_reason ) ) : ?>
				<div class="atlr-widget-breakdown">
					<h4><?php esc_html_e( 'By Reason', 'atlas-returns' ); ?></h4>
					<ul>
						<?php foreach ( $by_reason as $reason ) : ?>
							<li>
								<span class="atlr-reason-name"><?php echo esc_html( $reason['label'] ); ?></span>
								<span class="atlr-reason-count"><?php echo esc_html( $reason['count'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="atlr-widget-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=atlas-returns-analytics' ) ); ?>">
					<?php esc_html_e( 'View Full Analytics', 'atlas-returns' ); ?> &rarr;
				</a>
			</div>
		</div>

		<style>
			.atlr-dashboard-widget {
				margin: -12px;
			}
			.atlr-widget-stats {
				display: flex;
				border-bottom: 1px solid #eee;
			}
			.atlr-widget-stat {
				flex: 1;
				text-align: center;
				padding: 15px 10px;
				border-right: 1px solid #eee;
			}
			.atlr-widget-stat:last-child {
				border-right: none;
			}
			.atlr-stat-value {
				display: block;
				font-size: 24px;
				font-weight: 600;
				color: #333;
			}
			.atlr-stat-value.atlr-positive {
				color: #46b450;
			}
			.atlr-stat-value.atlr-negative {
				color: #dc3232;
			}
			.atlr-stat-label {
				display: block;
				font-size: 12px;
				color: #666;
				margin-top: 5px;
			}
			.atlr-widget-breakdown {
				padding: 15px;
				border-bottom: 1px solid #eee;
			}
			.atlr-widget-breakdown h4 {
				margin: 0 0 10px;
				font-size: 13px;
				color: #333;
			}
			.atlr-widget-breakdown ul {
				margin: 0;
				padding: 0;
				list-style: none;
			}
			.atlr-widget-breakdown li {
				display: flex;
				justify-content: space-between;
				padding: 5px 0;
				font-size: 13px;
			}
			.atlr-reason-count {
				font-weight: 600;
				background: #f0f0f0;
				padding: 2px 8px;
				border-radius: 3px;
			}
			.atlr-widget-footer {
				padding: 12px 15px;
				text-align: right;
			}
			.atlr-widget-footer a {
				font-size: 13px;
				text-decoration: none;
			}
		</style>
		<?php
	}
}
