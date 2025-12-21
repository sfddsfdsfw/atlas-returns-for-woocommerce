<?php
/**
 * History page template.
 *
 * @package AtlasReturns\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$is_pro = defined( 'ATLR_PRO' ) && ATLR_PRO;

// Get returns from repository.
$repository  = new \AtlasReturns\Core\ReturnRepository();
$page        = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$per_page    = 20;
$returns     = $repository->get_all( $page, $per_page );
$total       = $repository->count();
$total_pages = ceil( $total / $per_page );
?>

<div class="wrap atlr-wrap">
	<h1>
		<?php esc_html_e( 'Return History', 'atlas-returns-for-woocommerce' ); ?>
		<?php if ( $is_pro ) : ?>
			<a href="#" id="atlr-export-csv" class="page-title-action">
				<?php esc_html_e( 'Export CSV', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		<?php endif; ?>
	</h1>

	<?php if ( empty( $returns ) ) : ?>
		<div class="atlr-no-returns">
			<p><?php esc_html_e( 'No returns have been processed yet.', 'atlas-returns-for-woocommerce' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=atlas-returns-for-woocommerce' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Process a Return', 'atlas-returns-for-woocommerce' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped atlr-history-table">
			<thead>
				<tr>
					<th class="column-id"><?php esc_html_e( 'ID', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-original"><?php esc_html_e( 'Original Order', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-return"><?php esc_html_e( 'Return Order', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-reason"><?php esc_html_e( 'Reason', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-cost"><?php esc_html_e( 'Cost Difference', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-date"><?php esc_html_e( 'Date', 'atlas-returns-for-woocommerce' ); ?></th>
					<th class="column-status"><?php esc_html_e( 'Status', 'atlas-returns-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $returns as $return ) : ?>
					<?php
					$reason_labels = \AtlasReturns\Core\CostCalculator::get_return_reasons();
					$reason_label  = $reason_labels[ $return->reason ] ?? $return->reason;
					$cost_class    = $return->cost_difference < 0 ? 'atlr-negative' : ( $return->cost_difference > 0 ? 'atlr-positive' : '' );
					?>
					<tr>
						<td class="column-id"><?php echo esc_html( $return->id ); ?></td>
						<td class="column-original">
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $return->original_order_id . '&action=edit' ) ); ?>">
								#<?php echo esc_html( $return->original_order_id ); ?>
							</a>
						</td>
						<td class="column-return">
							<?php if ( $return->return_order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $return->return_order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $return->return_order_id ); ?>
								</a>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td class="column-reason"><?php echo esc_html( $reason_label ); ?></td>
						<td class="column-cost <?php echo esc_attr( $cost_class ); ?>">
							<?php echo wp_kses_post( wc_price( $return->cost_difference ) ); ?>
						</td>
						<td class="column-date">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $return->created_at ) ) ); ?>
						</td>
						<td class="column-status">
							<span class="atlr-status atlr-status-<?php echo esc_attr( $return->status ); ?>">
								<?php echo esc_html( ucfirst( $return->status ) ); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $total_pages,
							'current'   => $page,
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
