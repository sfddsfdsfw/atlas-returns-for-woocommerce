<?php
/**
 * Upgrade banner partial.
 *
 * @package AtlasReturns\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$plugin    = \AtlasReturns\Plugin::instance();
$remaining = $plugin->get_remaining_returns();
$is_pro    = $plugin->is_pro();

// Don't show banner to Pro users.
if ( $is_pro ) {
	return;
}

$upgrade_url = function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : '#';
?>

<div class="atlr-upgrade-banner">
	<div class="atlr-upgrade-banner-content">
		<div class="atlr-upgrade-icon">
			<span class="dashicons dashicons-star-filled"></span>
		</div>
		<div class="atlr-upgrade-text">
			<strong><?php esc_html_e( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ); ?></strong>
			<p>
				<?php
				printf(
					/* translators: %s: Number of remaining returns */
					esc_html__( 'You have %s returns remaining this month.', 'atlas-returns-for-woocommerce' ),
					'<strong>' . esc_html( $remaining ) . '</strong>'
				);
				?>
				<?php esc_html_e( 'Upgrade for unlimited returns, all return reasons, and analytics dashboard.', 'atlas-returns-for-woocommerce' ); ?>
			</p>
		</div>
		<div class="atlr-upgrade-cta">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary atlr-upgrade-button">
				<?php esc_html_e( 'Upgrade Now', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		</div>
	</div>
</div>
