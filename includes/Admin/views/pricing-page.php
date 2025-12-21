<?php
/**
 * Pricing page template.
 *
 * This page is shown when Freemius SDK is not installed or as a fallback.
 * When Freemius is properly configured, it will use its own pricing page.
 *
 * @package AtlasReturns\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$plugin    = \AtlasReturns\Plugin::instance();
$is_pro    = $plugin->is_pro();
$remaining = $plugin->get_remaining_returns();
?>

<div class="wrap atlr-wrap atlr-pricing-wrap">
	<h1><?php esc_html_e( 'Atlas Returns for WooCommerce', 'atlas-returns-for-woocommerce' ); ?></h1>

	<?php if ( $is_pro ) : ?>
		<div class="atlr-pro-active-notice">
			<span class="dashicons dashicons-yes-alt"></span>
			<?php esc_html_e( 'You are using Atlas Returns Pro! Thank you for your support.', 'atlas-returns-for-woocommerce' ); ?>
		</div>
	<?php endif; ?>

	<div class="atlr-pricing-header">
		<h2><?php esc_html_e( 'Choose Your Plan', 'atlas-returns-for-woocommerce' ); ?></h2>
		<p><?php esc_html_e( 'Streamline your WooCommerce returns with the right plan for your business.', 'atlas-returns-for-woocommerce' ); ?></p>
	</div>

	<div class="atlr-pricing-plans">
		<!-- Free Plan -->
		<div class="atlr-plan atlr-plan-free <?php echo ! $is_pro ? 'atlr-plan-current' : ''; ?>">
			<div class="atlr-plan-header">
				<h3><?php esc_html_e( 'Free', 'atlas-returns-for-woocommerce' ); ?></h3>
				<div class="atlr-plan-price">
					<span class="atlr-price">$0</span>
					<span class="atlr-period"><?php esc_html_e( 'forever', 'atlas-returns-for-woocommerce' ); ?></span>
				</div>
			</div>
			<div class="atlr-plan-features">
				<ul>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( '20 returns per month', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Customer fault returns only', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Cost calculation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Replacement order creation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Coupon generation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Company fault returns', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Analytics dashboard', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'CSV export', 'atlas-returns-for-woocommerce' ); ?>
					</li>
				</ul>
			</div>
			<div class="atlr-plan-cta">
				<?php if ( ! $is_pro ) : ?>
					<span class="atlr-current-plan"><?php esc_html_e( 'Current Plan', 'atlas-returns-for-woocommerce' ); ?></span>
					<p class="atlr-remaining">
						<?php
						printf(
							/* translators: %d: Number of returns remaining */
							esc_html__( '%d returns remaining this month', 'atlas-returns-for-woocommerce' ),
							intval( $remaining )
						);
						?>
					</p>
				<?php else : ?>
					<span class="atlr-plan-badge"><?php esc_html_e( 'Basic', 'atlas-returns-for-woocommerce' ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Pro Plan -->
		<div class="atlr-plan atlr-plan-pro <?php echo $is_pro ? 'atlr-plan-current' : ''; ?>">
			<div class="atlr-plan-badge-popular"><?php esc_html_e( 'Most Popular', 'atlas-returns-for-woocommerce' ); ?></div>
			<div class="atlr-plan-header">
				<h3><?php esc_html_e( 'Pro', 'atlas-returns-for-woocommerce' ); ?></h3>
				<div class="atlr-plan-price">
					<span class="atlr-price">$49</span>
					<span class="atlr-period"><?php esc_html_e( '/ year', 'atlas-returns-for-woocommerce' ); ?></span>
				</div>
			</div>
			<div class="atlr-plan-features">
				<ul>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'Unlimited returns', 'atlas-returns-for-woocommerce' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'All return reasons', 'atlas-returns-for-woocommerce' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Cost calculation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Replacement order creation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Coupon generation', 'atlas-returns-for-woocommerce' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'Analytics dashboard', 'atlas-returns-for-woocommerce' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'CSV export', 'atlas-returns-for-woocommerce' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Priority email support', 'atlas-returns-for-woocommerce' ); ?>
					</li>
				</ul>
			</div>
			<div class="atlr-plan-cta">
				<?php if ( $is_pro ) : ?>
					<span class="atlr-current-plan"><?php esc_html_e( 'Current Plan', 'atlas-returns-for-woocommerce' ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' ) ); ?>" class="button button-primary button-hero">
						<?php esc_html_e( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ); ?>
					</a>
					<p class="atlr-guarantee"><?php esc_html_e( '30-day money-back guarantee', 'atlas-returns-for-woocommerce' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="atlr-pricing-faq">
		<h3><?php esc_html_e( 'Frequently Asked Questions', 'atlas-returns-for-woocommerce' ); ?></h3>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'What happens when I reach the free limit?', 'atlas-returns-for-woocommerce' ); ?></h4>
			<p><?php esc_html_e( 'You can still view and manage existing returns, but you won\'t be able to create new returns until the next month or until you upgrade to Pro.', 'atlas-returns-for-woocommerce' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'Can I upgrade at any time?', 'atlas-returns-for-woocommerce' ); ?></h4>
			<p><?php esc_html_e( 'Yes! You can upgrade to Pro at any time. Your Pro features will be activated immediately after purchase.', 'atlas-returns-for-woocommerce' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'What payment methods do you accept?', 'atlas-returns-for-woocommerce' ); ?></h4>
			<p><?php esc_html_e( 'We accept all major credit cards, PayPal, and bank transfers through our secure payment provider.', 'atlas-returns-for-woocommerce' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'Is there a refund policy?', 'atlas-returns-for-woocommerce' ); ?></h4>
			<p><?php esc_html_e( 'Yes, we offer a 30-day money-back guarantee. If you\'re not satisfied with Pro, contact us for a full refund.', 'atlas-returns-for-woocommerce' ); ?></p>
		</div>
	</div>
</div>
