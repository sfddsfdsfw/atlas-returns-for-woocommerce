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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_widget_styles' ) );
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
			__( 'Atlas Returns - This Month', 'atlas-returns-for-woocommerce' ),
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
					<span class="atlr-stat-label"><?php esc_html_e( 'Returns', 'atlas-returns-for-woocommerce' ); ?></span>
				</div>
				<div class="atlr-widget-stat">
					<span class="atlr-stat-value"><?php echo esc_html( $summary['return_rate'] ); ?>%</span>
					<span class="atlr-stat-label"><?php esc_html_e( 'Return Rate', 'atlas-returns-for-woocommerce' ); ?></span>
				</div>
				<div class="atlr-widget-stat">
					<span class="atlr-stat-value <?php echo $summary['total_cost_difference'] < 0 ? 'atlr-negative' : 'atlr-positive'; ?>">
						<?php echo wp_kses_post( wc_price( $summary['total_cost_difference'] ) ); ?>
					</span>
					<span class="atlr-stat-label"><?php esc_html_e( 'Net Cost', 'atlas-returns-for-woocommerce' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $by_reason ) ) : ?>
				<div class="atlr-widget-breakdown">
					<h4><?php esc_html_e( 'By Reason', 'atlas-returns-for-woocommerce' ); ?></h4>
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
					<?php esc_html_e( 'View Full Analytics', 'atlas-returns-for-woocommerce' ); ?> &rarr;
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue dashboard widget styles.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_widget_styles( $hook_suffix ) {
		if ( 'index.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'atlr-admin-components',
			ATLR_PLUGIN_URL . 'assets/dist/css/admin-components.min.css',
			array(),
			ATLR_VERSION
		);
	}
}
