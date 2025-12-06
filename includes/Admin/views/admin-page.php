<?php
/**
 * Main admin page template.
 *
 * @package AtlasReturns\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$plugin         = \AtlasReturns\Plugin::instance();
$is_pro         = $plugin->is_pro();
$can_create     = $plugin->can_create_return();
$remaining      = $plugin->get_remaining_returns();
$upgrade_url    = function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' );
$return_reasons = \AtlasReturns\Core\CostCalculator::get_return_reasons( $is_pro );
$descriptions   = \AtlasReturns\Core\CostCalculator::get_reason_descriptions();
?>

<div class="wrap atlr-wrap">
	<h1>
		<?php esc_html_e( 'Atlas Returns', 'atlas-returns-for-woocommerce' ); ?>
		<span class="atlr-version">v<?php echo esc_html( ATLR_VERSION ); ?></span>
		<?php if ( $is_pro ) : ?>
			<span class="atlr-pro-badge"><?php esc_html_e( 'Pro', 'atlas-returns-for-woocommerce' ); ?></span>
		<?php else : ?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="atlr-upgrade-btn">
				<?php esc_html_e( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		<?php endif; ?>
	</h1>

	<?php if ( ! $is_pro ) : ?>
		<?php include ATLR_PLUGIN_DIR . 'includes/Admin/views/partials/upgrade-banner.php'; ?>
	<?php endif; ?>

	<?php if ( ! $is_pro && ! $can_create ) : ?>
		<div class="atlr-limit-notice atlr-limit-exceeded">
			<p>
				<strong><?php esc_html_e( 'Monthly limit reached!', 'atlas-returns-for-woocommerce' ); ?></strong>
				<?php
				printf(
					wp_kses(
						/* translators: %s: upgrade URL */
						__( '<a href="%s">Upgrade to Pro</a> for unlimited returns.', 'atlas-returns-for-woocommerce' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( $upgrade_url )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Return Reason Info -->
	<div class="atlr-info-box">
		<h2><?php esc_html_e( 'Return Options', 'atlas-returns-for-woocommerce' ); ?></h2>
		<ul class="atlr-reason-list">
			<?php foreach ( $return_reasons as $reason_key => $reason_label ) : ?>
				<li data-reason="<?php echo esc_attr( $reason_key ); ?>" style="display: none;">
					<strong><?php echo esc_html( $reason_label ); ?>:</strong>
					<span><?php echo esc_html( $descriptions[ $reason_key ] ?? '' ); ?></span>
					<?php if ( ! $is_pro && $reason_key !== \AtlasReturns\Core\CostCalculator::REASON_CUSTOMER_FAULT ) : ?>
						<span class="atlr-pro-badge"><?php esc_html_e( 'Pro', 'atlas-returns-for-woocommerce' ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Return Form -->
	<form id="atlr-return-form" class="atlr-form">
		<input type="hidden" id="atlr_nonce" value="<?php echo esc_attr( wp_create_nonce( 'atlr_nonce' ) ); ?>" />

		<div class="atlr-form-row">
			<div class="atlr-form-field">
				<label for="atlr_order_id"><?php esc_html_e( 'Order ID or Phone:', 'atlas-returns-for-woocommerce' ); ?></label>
				<input type="text" id="atlr_order_id" name="order_id" required placeholder="<?php esc_attr_e( 'Enter order ID or phone number', 'atlas-returns-for-woocommerce' ); ?>" />
			</div>

			<div class="atlr-form-field">
				<label for="atlr_reason"><?php esc_html_e( 'Return Reason:', 'atlas-returns-for-woocommerce' ); ?></label>
				<select id="atlr_reason" name="reason" required>
					<option value=""><?php esc_html_e( '-- Select Reason --', 'atlas-returns-for-woocommerce' ); ?></option>
					<?php foreach ( $return_reasons as $reason_key => $reason_label ) : ?>
						<?php
						$disabled = ! $is_pro && $reason_key !== \AtlasReturns\Core\CostCalculator::REASON_CUSTOMER_FAULT;
						?>
						<option value="<?php echo esc_attr( $reason_key ); ?>" <?php disabled( $disabled ); ?>>
							<?php echo esc_html( $reason_label ); ?>
							<?php echo $disabled ? ' (' . esc_html__( 'Pro', 'atlas-returns-for-woocommerce' ) . ')' : ''; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="atlr-form-row">
			<div class="atlr-form-field">
				<label for="atlr_products_to_replace"><?php esc_html_e( 'Products to Return (SKUs):', 'atlas-returns-for-woocommerce' ); ?></label>
				<input type="text" id="atlr_products_to_replace" name="products_to_replace" placeholder="<?php esc_attr_e( 'Enter SKUs separated by commas', 'atlas-returns-for-woocommerce' ); ?>" />
				<p class="description"><?php esc_html_e( 'Enter product SKUs separated by commas (e.g., SKU001, SKU002)', 'atlas-returns-for-woocommerce' ); ?></p>
			</div>

			<div class="atlr-form-field">
				<label for="atlr_new_products"><?php esc_html_e( 'New Products (SKUs):', 'atlas-returns-for-woocommerce' ); ?></label>
				<input type="text" id="atlr_new_products" name="new_products" placeholder="<?php esc_attr_e( 'Enter SKUs separated by commas', 'atlas-returns-for-woocommerce' ); ?>" />
				<p class="description"><?php esc_html_e( 'Enter product SKUs for replacement products', 'atlas-returns-for-woocommerce' ); ?></p>
			</div>
		</div>

		<div class="atlr-form-actions">
			<button type="submit" id="atlr_submit" class="button button-primary button-large" <?php disabled( ! $can_create ); ?>>
				<?php esc_html_e( 'Create Return Order', 'atlas-returns-for-woocommerce' ); ?>
			</button>
			<span class="atlr-spinner"></span>
		</div>
	</form>

	<!-- Preview Section -->
	<div id="atlr-preview-section" class="atlr-preview" style="display: none;">
		<h2><?php esc_html_e( 'Order Preview', 'atlas-returns-for-woocommerce' ); ?></h2>
		<div id="atlr-preview-details"></div>
		<div id="atlr-calculation-details"></div>
	</div>

	<!-- Loading Overlay -->
	<div id="atlr-loading" class="atlr-loading" style="display: none;">
		<div class="atlr-loading-spinner"></div>
	</div>
</div>
