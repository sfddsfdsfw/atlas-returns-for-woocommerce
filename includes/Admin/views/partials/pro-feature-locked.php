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
$feature_name        = isset( $feature_name ) ? $feature_name : __( 'Pro Feature', 'atlas-returns-for-woocommerce' );
$feature_description = isset( $feature_description ) ? $feature_description : __( 'This feature is available with Atlas Returns Pro.', 'atlas-returns-for-woocommerce' );
$benefits            = isset( $benefits ) ? $benefits : array(
	__( 'Unlimited returns per month', 'atlas-returns-for-woocommerce' ),
	__( 'All return reasons available', 'atlas-returns-for-woocommerce' ),
	__( 'Analytics dashboard', 'atlas-returns-for-woocommerce' ),
	__( 'CSV export', 'atlas-returns-for-woocommerce' ),
	__( 'Priority support', 'atlas-returns-for-woocommerce' ),
);
?>

<div class="atlr-pro-locked">
	<div class="atlr-pro-locked-icon">
		<span class="dashicons dashicons-lock"></span>
	</div>
	<h3><?php echo esc_html( $feature_name ); ?></h3>
	<p><?php echo esc_html( $feature_description ); ?></p>

	<div class="atlr-pro-benefits">
		<h4><?php esc_html_e( 'Pro includes:', 'atlas-returns-for-woocommerce' ); ?></h4>
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
		<?php esc_html_e( 'Unlock Pro Features', 'atlas-returns-for-woocommerce' ); ?>
	</a>
</div>
