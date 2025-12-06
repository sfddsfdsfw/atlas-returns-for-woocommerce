<?php
/**
 * Pro feature locked partial.
 *
 * @package AtlasReturns\Admin\Views
 *
 * @var string $feature_name Feature name to display.
 * @var string $feature_description Feature description.
 * @var array  $benefits Array of benefit strings.
 */

defined( 'ABSPATH' ) || exit;

$upgrade_url         = function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : '#';
$feature_name        = isset( $feature_name ) ? $feature_name : __( 'Pro Feature', 'atlas-returns' );
$feature_description = isset( $feature_description ) ? $feature_description : __( 'This feature is available with Atlas Returns Pro.', 'atlas-returns' );
$benefits            = isset( $benefits ) ? $benefits : array(
	__( 'Unlimited returns per month', 'atlas-returns' ),
	__( 'All return reasons available', 'atlas-returns' ),
	__( 'Analytics dashboard', 'atlas-returns' ),
	__( 'CSV export', 'atlas-returns' ),
	__( 'Priority support', 'atlas-returns' ),
);
?>

<div class="atlr-pro-locked">
	<div class="atlr-pro-locked-icon">
		<span class="dashicons dashicons-lock"></span>
	</div>
	<h3><?php echo esc_html( $feature_name ); ?></h3>
	<p><?php echo esc_html( $feature_description ); ?></p>

	<div class="atlr-pro-benefits">
		<h4><?php esc_html_e( 'Pro includes:', 'atlas-returns' ); ?></h4>
		<ul>
			<?php foreach ( $benefits as $benefit ) : ?>
				<li>
					<span class="dashicons dashicons-yes-alt"></span>
					<?php echo esc_html( $benefit ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-hero atlr-unlock-button">
		<span class="dashicons dashicons-unlock"></span>
		<?php esc_html_e( 'Unlock Pro Features', 'atlas-returns' ); ?>
	</a>
</div>

<style>
.atlr-pro-locked {
	background: #fff;
	border-radius: 8px;
	padding: 40px;
	text-align: center;
	max-width: 500px;
	margin: 40px auto;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.atlr-pro-locked-icon {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 50%;
	width: 80px;
	height: 80px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 20px;
}

.atlr-pro-locked-icon .dashicons {
	font-size: 40px;
	width: 40px;
	height: 40px;
	color: #fff;
}

.atlr-pro-locked h3 {
	font-size: 24px;
	margin: 0 0 10px;
	color: #333;
}

.atlr-pro-locked > p {
	color: #666;
	font-size: 14px;
	margin-bottom: 30px;
}

.atlr-pro-benefits {
	text-align: left;
	background: #f8f9fa;
	padding: 20px;
	border-radius: 6px;
	margin-bottom: 30px;
}

.atlr-pro-benefits h4 {
	margin: 0 0 15px;
	font-size: 14px;
	color: #333;
}

.atlr-pro-benefits ul {
	margin: 0;
	padding: 0;
	list-style: none;
}

.atlr-pro-benefits li {
	display: flex;
	align-items: center;
	gap: 10px;
	margin-bottom: 10px;
	font-size: 13px;
	color: #444;
}

.atlr-pro-benefits li:last-child {
	margin-bottom: 0;
}

.atlr-pro-benefits .dashicons-yes-alt {
	color: #46b450;
	font-size: 18px;
	width: 18px;
	height: 18px;
}

.atlr-unlock-button {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
	border: none !important;
	display: inline-flex !important;
	align-items: center;
	gap: 8px;
	padding: 12px 30px !important;
	font-size: 16px !important;
	height: auto !important;
}

.atlr-unlock-button:hover {
	opacity: 0.9;
}

.atlr-unlock-button .dashicons {
	font-size: 20px;
	width: 20px;
	height: 20px;
}
</style>
