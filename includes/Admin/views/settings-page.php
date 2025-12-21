<?php
/**
 * Settings page template.
 *
 * @package AtlasReturns\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$is_pro = defined( 'ATLR_PRO' ) && ATLR_PRO;
?>

<div class="wrap atlr-wrap">
	<h1>
		<?php esc_html_e( 'Atlas Returns Settings', 'atlas-returns-for-woocommerce' ); ?>
		<?php if ( ! $is_pro ) : ?>
			<a href="<?php echo esc_url( function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' ) ); ?>" class="atlr-upgrade-btn">
				<?php esc_html_e( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		<?php endif; ?>
	</h1>

	<?php settings_errors(); ?>

	<form method="post" action="options.php" class="atlr-settings-form">
		<?php
		settings_fields( \AtlasReturns\Admin\Settings::OPTION_GROUP );
		do_settings_sections( 'atlas-returns-settings' );
		submit_button( __( 'Save Settings', 'atlas-returns-for-woocommerce' ) );
		?>
	</form>

	<?php if ( ! $is_pro ) : ?>
		<div class="atlr-pro-features">
			<h2><?php esc_html_e( 'Unlock Pro Features', 'atlas-returns-for-woocommerce' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Unlimited returns per month', 'atlas-returns-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'All return reason types', 'atlas-returns-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Analytics dashboard with charts', 'atlas-returns-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'CSV export functionality', 'atlas-returns-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Custom email templates', 'atlas-returns-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( 'Priority support', 'atlas-returns-for-woocommerce' ); ?></li>
			</ul>
			<a href="<?php echo esc_url( function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Get Pro Now', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
