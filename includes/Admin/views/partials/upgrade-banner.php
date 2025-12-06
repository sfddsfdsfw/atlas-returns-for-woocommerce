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
			<strong><?php esc_html_e( 'Upgrade to Pro', 'atlas-returns' ); ?></strong>
			<p>
				<?php
				printf(
					/* translators: %s: Number of remaining returns */
					esc_html__( 'You have %s returns remaining this month.', 'atlas-returns' ),
					'<strong>' . esc_html( $remaining ) . '</strong>'
				);
				?>
				<?php esc_html_e( 'Upgrade for unlimited returns, all return reasons, and analytics dashboard.', 'atlas-returns' ); ?>
			</p>
		</div>
		<div class="atlr-upgrade-cta">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary atlr-upgrade-button">
				<?php esc_html_e( 'Upgrade Now', 'atlas-returns' ); ?>
			</a>
		</div>
	</div>
</div>

<style>
.atlr-upgrade-banner {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	color: #fff;
}

.atlr-upgrade-banner-content {
	display: flex;
	align-items: center;
	gap: 20px;
	flex-wrap: wrap;
}

.atlr-upgrade-icon {
	background: rgba(255, 255, 255, 0.2);
	border-radius: 50%;
	width: 50px;
	height: 50px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.atlr-upgrade-icon .dashicons {
	font-size: 24px;
	width: 24px;
	height: 24px;
	color: #fff;
}

.atlr-upgrade-text {
	flex: 1;
	min-width: 200px;
}

.atlr-upgrade-text strong {
	font-size: 16px;
	display: block;
	margin-bottom: 5px;
}

.atlr-upgrade-text p {
	margin: 0;
	opacity: 0.9;
	font-size: 13px;
}

.atlr-upgrade-text p strong {
	display: inline;
	font-size: inherit;
}

.atlr-upgrade-cta .button-primary {
	background: #fff !important;
	color: #667eea !important;
	border: none !important;
	padding: 8px 20px !important;
	font-weight: 600 !important;
	height: auto !important;
}

.atlr-upgrade-cta .button-primary:hover {
	background: #f0f0f0 !important;
}
</style>
