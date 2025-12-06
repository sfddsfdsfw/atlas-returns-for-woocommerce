<?php
/**
 * Analytics page template.
 *
 * @package AtlasReturns\Pro\Views
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap atlr-wrap atlr-analytics-wrap">
	<h1>
		<?php esc_html_e( 'Returns Analytics', 'atlas-returns' ); ?>
		<span class="atlr-pro-badge"><?php esc_html_e( 'Pro', 'atlas-returns' ); ?></span>
	</h1>

	<!-- Period Selector -->
	<div class="atlr-analytics-controls">
		<div class="atlr-period-selector">
			<label for="atlr-period"><?php esc_html_e( 'Period:', 'atlas-returns' ); ?></label>
			<select id="atlr-period">
				<option value="7days"><?php esc_html_e( 'Last 7 Days', 'atlas-returns' ); ?></option>
				<option value="30days" selected><?php esc_html_e( 'Last 30 Days', 'atlas-returns' ); ?></option>
				<option value="90days"><?php esc_html_e( 'Last 90 Days', 'atlas-returns' ); ?></option>
				<option value="365days"><?php esc_html_e( 'Last Year', 'atlas-returns' ); ?></option>
				<option value="month"><?php esc_html_e( 'This Month', 'atlas-returns' ); ?></option>
				<option value="last_month"><?php esc_html_e( 'Last Month', 'atlas-returns' ); ?></option>
				<option value="all"><?php esc_html_e( 'All Time', 'atlas-returns' ); ?></option>
			</select>
		</div>

		<div class="atlr-export-controls">
			<button type="button" id="atlr-export-csv" class="button">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export CSV', 'atlas-returns' ); ?>
			</button>
			<button type="button" id="atlr-refresh" class="button">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh', 'atlas-returns' ); ?>
			</button>
		</div>
	</div>

	<!-- Summary Cards -->
	<div class="atlr-summary-cards">
		<div class="atlr-card atlr-card-returns">
			<div class="atlr-card-icon">
				<span class="dashicons dashicons-image-rotate"></span>
			</div>
			<div class="atlr-card-content">
				<span class="atlr-card-value" id="atlr-total-returns">-</span>
				<span class="atlr-card-label"><?php esc_html_e( 'Total Returns', 'atlas-returns' ); ?></span>
			</div>
		</div>

		<div class="atlr-card atlr-card-rate">
			<div class="atlr-card-icon">
				<span class="dashicons dashicons-chart-pie"></span>
			</div>
			<div class="atlr-card-content">
				<span class="atlr-card-value" id="atlr-return-rate">-</span>
				<span class="atlr-card-label"><?php esc_html_e( 'Return Rate', 'atlas-returns' ); ?></span>
			</div>
		</div>

		<div class="atlr-card atlr-card-refunded">
			<div class="atlr-card-icon">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="atlr-card-content">
				<span class="atlr-card-value" id="atlr-total-refunded">-</span>
				<span class="atlr-card-label"><?php esc_html_e( 'Total Refunded', 'atlas-returns' ); ?></span>
			</div>
		</div>

		<div class="atlr-card atlr-card-charged">
			<div class="atlr-card-icon">
				<span class="dashicons dashicons-plus-alt"></span>
			</div>
			<div class="atlr-card-content">
				<span class="atlr-card-value" id="atlr-total-charged">-</span>
				<span class="atlr-card-label"><?php esc_html_e( 'Total Charged', 'atlas-returns' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="atlr-charts-row">
		<!-- Returns Trend Chart -->
		<div class="atlr-chart-container atlr-chart-large">
			<h3><?php esc_html_e( 'Returns Over Time', 'atlas-returns' ); ?></h3>
			<div class="atlr-chart-wrapper">
				<canvas id="atlr-trend-chart"></canvas>
			</div>
		</div>

		<!-- Returns by Reason Chart -->
		<div class="atlr-chart-container atlr-chart-small">
			<h3><?php esc_html_e( 'Returns by Reason', 'atlas-returns' ); ?></h3>
			<div class="atlr-chart-wrapper">
				<canvas id="atlr-reasons-chart"></canvas>
			</div>
		</div>
	</div>

	<!-- Second Charts Row -->
	<div class="atlr-charts-row">
		<!-- Cost Breakdown Chart -->
		<div class="atlr-chart-container atlr-chart-small">
			<h3><?php esc_html_e( 'Cost Breakdown', 'atlas-returns' ); ?></h3>
			<div class="atlr-chart-wrapper">
				<canvas id="atlr-costs-chart"></canvas>
			</div>
		</div>

		<!-- Top Returned Products -->
		<div class="atlr-chart-container atlr-chart-large">
			<h3><?php esc_html_e( 'Top Returned Products', 'atlas-returns' ); ?></h3>
			<div class="atlr-table-wrapper">
				<table class="wp-list-table widefat fixed striped" id="atlr-top-products-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'atlas-returns' ); ?></th>
							<th class="atlr-col-sku"><?php esc_html_e( 'SKU', 'atlas-returns' ); ?></th>
							<th class="atlr-col-count"><?php esc_html_e( 'Returns', 'atlas-returns' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="3" class="atlr-loading-cell">
								<?php esc_html_e( 'Loading...', 'atlas-returns' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Recent Returns -->
	<div class="atlr-recent-returns">
		<h3><?php esc_html_e( 'Recent Returns', 'atlas-returns' ); ?></h3>
		<table class="wp-list-table widefat fixed striped" id="atlr-recent-returns-table">
			<thead>
				<tr>
					<th class="atlr-col-id"><?php esc_html_e( 'ID', 'atlas-returns' ); ?></th>
					<th><?php esc_html_e( 'Original Order', 'atlas-returns' ); ?></th>
					<th><?php esc_html_e( 'Return Order', 'atlas-returns' ); ?></th>
					<th><?php esc_html_e( 'Reason', 'atlas-returns' ); ?></th>
					<th class="atlr-col-cost"><?php esc_html_e( 'Cost Difference', 'atlas-returns' ); ?></th>
					<th><?php esc_html_e( 'Date', 'atlas-returns' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="6" class="atlr-loading-cell">
						<?php esc_html_e( 'Loading...', 'atlas-returns' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- Loading Overlay -->
	<div id="atlr-analytics-loading" class="atlr-analytics-loading" style="display: none;">
		<div class="atlr-loading-spinner"></div>
	</div>
</div>
