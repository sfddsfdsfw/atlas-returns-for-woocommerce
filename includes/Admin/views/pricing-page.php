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
	<h1><?php esc_html_e( 'Atlas Returns for WooCommerce', 'atlas-returns' ); ?></h1>

	<?php if ( $is_pro ) : ?>
		<div class="atlr-pro-active-notice">
			<span class="dashicons dashicons-yes-alt"></span>
			<?php esc_html_e( 'You are using Atlas Returns Pro! Thank you for your support.', 'atlas-returns' ); ?>
		</div>
	<?php endif; ?>

	<div class="atlr-pricing-header">
		<h2><?php esc_html_e( 'Choose Your Plan', 'atlas-returns' ); ?></h2>
		<p><?php esc_html_e( 'Streamline your WooCommerce returns with the right plan for your business.', 'atlas-returns' ); ?></p>
	</div>

	<div class="atlr-pricing-plans">
		<!-- Free Plan -->
		<div class="atlr-plan atlr-plan-free <?php echo ! $is_pro ? 'atlr-plan-current' : ''; ?>">
			<div class="atlr-plan-header">
				<h3><?php esc_html_e( 'Free', 'atlas-returns' ); ?></h3>
				<div class="atlr-plan-price">
					<span class="atlr-price">$0</span>
					<span class="atlr-period"><?php esc_html_e( 'forever', 'atlas-returns' ); ?></span>
				</div>
			</div>
			<div class="atlr-plan-features">
				<ul>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( '20 returns per month', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Customer fault returns only', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Cost calculation', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Replacement order creation', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Coupon generation', 'atlas-returns' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Company fault returns', 'atlas-returns' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Analytics dashboard', 'atlas-returns' ); ?>
					</li>
					<li class="atlr-feature-disabled">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'CSV export', 'atlas-returns' ); ?>
					</li>
				</ul>
			</div>
			<div class="atlr-plan-cta">
				<?php if ( ! $is_pro ) : ?>
					<span class="atlr-current-plan"><?php esc_html_e( 'Current Plan', 'atlas-returns' ); ?></span>
					<p class="atlr-remaining">
						<?php
						printf(
							/* translators: %d: Number of returns remaining */
							esc_html__( '%d returns remaining this month', 'atlas-returns' ),
							$remaining
						);
						?>
					</p>
				<?php else : ?>
					<span class="atlr-plan-badge"><?php esc_html_e( 'Basic', 'atlas-returns' ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Pro Plan -->
		<div class="atlr-plan atlr-plan-pro <?php echo $is_pro ? 'atlr-plan-current' : ''; ?>">
			<div class="atlr-plan-badge-popular"><?php esc_html_e( 'Most Popular', 'atlas-returns' ); ?></div>
			<div class="atlr-plan-header">
				<h3><?php esc_html_e( 'Pro', 'atlas-returns' ); ?></h3>
				<div class="atlr-plan-price">
					<span class="atlr-price">$49</span>
					<span class="atlr-period"><?php esc_html_e( '/ year', 'atlas-returns' ); ?></span>
				</div>
			</div>
			<div class="atlr-plan-features">
				<ul>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'Unlimited returns', 'atlas-returns' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'All return reasons', 'atlas-returns' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Cost calculation', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Replacement order creation', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Coupon generation', 'atlas-returns' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'Analytics dashboard', 'atlas-returns' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<strong><?php esc_html_e( 'CSV export', 'atlas-returns' ); ?></strong>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Priority email support', 'atlas-returns' ); ?>
					</li>
				</ul>
			</div>
			<div class="atlr-plan-cta">
				<?php if ( $is_pro ) : ?>
					<span class="atlr-current-plan"><?php esc_html_e( 'Current Plan', 'atlas-returns' ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : 'https://pluginatlas.com/atlas-returns' ); ?>" class="button button-primary button-hero">
						<?php esc_html_e( 'Upgrade to Pro', 'atlas-returns' ); ?>
					</a>
					<p class="atlr-guarantee"><?php esc_html_e( '30-day money-back guarantee', 'atlas-returns' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="atlr-pricing-faq">
		<h3><?php esc_html_e( 'Frequently Asked Questions', 'atlas-returns' ); ?></h3>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'What happens when I reach the free limit?', 'atlas-returns' ); ?></h4>
			<p><?php esc_html_e( 'You can still view and manage existing returns, but you won\'t be able to create new returns until the next month or until you upgrade to Pro.', 'atlas-returns' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'Can I upgrade at any time?', 'atlas-returns' ); ?></h4>
			<p><?php esc_html_e( 'Yes! You can upgrade to Pro at any time. Your Pro features will be activated immediately after purchase.', 'atlas-returns' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'What payment methods do you accept?', 'atlas-returns' ); ?></h4>
			<p><?php esc_html_e( 'We accept all major credit cards, PayPal, and bank transfers through our secure payment provider.', 'atlas-returns' ); ?></p>
		</div>

		<div class="atlr-faq-item">
			<h4><?php esc_html_e( 'Is there a refund policy?', 'atlas-returns' ); ?></h4>
			<p><?php esc_html_e( 'Yes, we offer a 30-day money-back guarantee. If you\'re not satisfied with Pro, contact us for a full refund.', 'atlas-returns' ); ?></p>
		</div>
	</div>
</div>

<style>
.atlr-pricing-wrap {
	max-width: 900px;
	margin: 20px auto;
}

.atlr-pro-active-notice {
	background: #d4edda;
	border: 1px solid #c3e6cb;
	color: #155724;
	padding: 15px 20px;
	border-radius: 6px;
	margin-bottom: 30px;
	display: flex;
	align-items: center;
	gap: 10px;
}

.atlr-pro-active-notice .dashicons {
	color: #28a745;
}

.atlr-pricing-header {
	text-align: center;
	margin-bottom: 40px;
}

.atlr-pricing-header h2 {
	font-size: 28px;
	margin-bottom: 10px;
}

.atlr-pricing-header p {
	color: #666;
	font-size: 16px;
}

.atlr-pricing-plans {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 30px;
	margin-bottom: 50px;
}

@media (max-width: 768px) {
	.atlr-pricing-plans {
		grid-template-columns: 1fr;
	}
}

.atlr-plan {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
	padding: 30px;
	position: relative;
	transition: transform 0.2s, box-shadow 0.2s;
}

.atlr-plan:hover {
	transform: translateY(-5px);
	box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.atlr-plan-pro {
	border: 2px solid #667eea;
}

.atlr-plan-badge-popular {
	position: absolute;
	top: -12px;
	left: 50%;
	transform: translateX(-50%);
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 5px 15px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.atlr-plan-header {
	text-align: center;
	margin-bottom: 25px;
	padding-bottom: 25px;
	border-bottom: 1px solid #eee;
}

.atlr-plan-header h3 {
	font-size: 22px;
	margin: 0 0 15px;
}

.atlr-plan-price .atlr-price {
	font-size: 48px;
	font-weight: 700;
	color: #333;
}

.atlr-plan-price .atlr-period {
	font-size: 16px;
	color: #666;
}

.atlr-plan-features ul {
	list-style: none;
	padding: 0;
	margin: 0 0 25px;
}

.atlr-plan-features li {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 8px 0;
	font-size: 14px;
}

.atlr-plan-features .dashicons-yes {
	color: #46b450;
}

.atlr-plan-features .dashicons-no {
	color: #ccc;
}

.atlr-feature-disabled {
	color: #999;
}

.atlr-plan-cta {
	text-align: center;
}

.atlr-current-plan {
	display: inline-block;
	background: #f0f0f0;
	padding: 10px 25px;
	border-radius: 5px;
	font-weight: 600;
	color: #666;
}

.atlr-plan-current .atlr-current-plan {
	background: #d4edda;
	color: #155724;
}

.atlr-remaining {
	margin-top: 10px;
	font-size: 13px;
	color: #666;
}

.atlr-guarantee {
	margin-top: 10px;
	font-size: 12px;
	color: #666;
}

.atlr-plan-cta .button-hero {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
	border: none !important;
	padding: 12px 40px !important;
	font-size: 16px !important;
	height: auto !important;
}

.atlr-pricing-faq {
	background: #fff;
	border-radius: 12px;
	padding: 30px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.atlr-pricing-faq h3 {
	font-size: 20px;
	margin: 0 0 25px;
	text-align: center;
}

.atlr-faq-item {
	margin-bottom: 20px;
	padding-bottom: 20px;
	border-bottom: 1px solid #eee;
}

.atlr-faq-item:last-child {
	margin-bottom: 0;
	padding-bottom: 0;
	border-bottom: none;
}

.atlr-faq-item h4 {
	font-size: 15px;
	margin: 0 0 10px;
	color: #333;
}

.atlr-faq-item p {
	margin: 0;
	color: #666;
	font-size: 14px;
	line-height: 1.6;
}
</style>
