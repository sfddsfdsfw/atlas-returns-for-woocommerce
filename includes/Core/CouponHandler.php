<?php
/**
 * Coupon handler class.
 *
 * @package AtlasReturns\Core
 */

namespace AtlasReturns\Core;

/**
 * Class CouponHandler
 *
 * Handles coupon creation and email notifications.
 */
class CouponHandler {

	/**
	 * Create a coupon for remaining credit.
	 *
	 * @param \WC_Order $order Original order.
	 * @param float     $amount Coupon amount.
	 * @return int|false Coupon ID or false on failure.
	 */
	public function create_coupon( \WC_Order $order, $amount ) {
		// Use phone number as coupon code for easy reference.
		$phone       = $order->get_billing_phone();
		$coupon_code = $this->generate_coupon_code( $phone );

		// Check if coupon already exists.
		$existing = wc_get_coupon_id_by_code( $coupon_code );
		if ( $existing ) {
			// Append timestamp to make unique.
			$coupon_code = $coupon_code . '-' . time();
		}

		// Get validity days from settings.
		$validity_days = (int) get_option( 'atlr_coupon_validity_days', 180 );
		$expiry_date   = gmdate( 'Y-m-d', strtotime( '+' . $validity_days . ' days' ) );

		// Create the coupon.
		$coupon = new \WC_Coupon();
		$coupon->set_code( $coupon_code );
		$coupon->set_discount_type( 'fixed_cart' );
		$coupon->set_amount( number_format( $amount, 2, '.', '' ) );
		$coupon->set_individual_use( false );
		$coupon->set_usage_limit( 1 );
		$coupon->set_date_expires( $expiry_date );
		$coupon->set_free_shipping( false );
		$coupon->set_exclude_sale_items( false );

		// Add meta data.
		$coupon->update_meta_data( '_atlr_generated', 'yes' );
		$coupon->update_meta_data( '_atlr_original_order_id', $order->get_id() );

		// Set description.
		$coupon->set_description(
			sprintf(
				/* translators: %d: original order ID */
				__( 'Credit coupon from return order #%d - Atlas Returns', 'atlas-returns' ),
				$order->get_id()
			)
		);

		$coupon_id = $coupon->save();

		/**
		 * Action fired after a coupon is created.
		 *
		 * @param int       $coupon_id Coupon ID.
		 * @param \WC_Order $order Original order.
		 * @param float     $amount Coupon amount.
		 */
		do_action( 'atlr_coupon_created', $coupon_id, $order, $amount );

		return $coupon_id;
	}

	/**
	 * Send coupon email to customer.
	 *
	 * @param \WC_Order $order Original order.
	 * @param int       $coupon_id Coupon ID.
	 * @return bool True if sent successfully.
	 */
	public function send_coupon_email( \WC_Order $order, $coupon_id ) {
		$coupon      = new \WC_Coupon( $coupon_id );
		$coupon_code = $coupon->get_code();
		$amount      = $coupon->get_amount();
		$expiry      = $coupon->get_date_expires();
		$expiry_date = $expiry ? $expiry->date_i18n( get_option( 'date_format' ) ) : __( 'No expiry', 'atlas-returns' );

		$to      = $order->get_billing_email();
		$subject = $this->get_email_subject();
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Build email content.
		$message = $this->get_email_content( $order, $coupon_code, $amount, $expiry_date );

		// Apply filters for customization.
		$message = apply_filters( 'atlr_coupon_email_content', $message, $order, $coupon );
		$subject = apply_filters( 'atlr_coupon_email_subject', $subject, $order, $coupon );
		$headers = apply_filters( 'atlr_coupon_email_headers', $headers, $order, $coupon );

		$sent = wp_mail( $to, $subject, $message, $headers );

		/**
		 * Action fired after coupon email is sent.
		 *
		 * @param bool      $sent Whether email was sent.
		 * @param \WC_Order $order Order object.
		 * @param int       $coupon_id Coupon ID.
		 */
		do_action( 'atlr_coupon_email_sent', $sent, $order, $coupon_id );

		return $sent;
	}

	/**
	 * Generate coupon code from phone number.
	 *
	 * @param string $phone Phone number.
	 * @return string Coupon code.
	 */
	private function generate_coupon_code( $phone ) {
		// Clean phone number - keep only digits.
		$clean = preg_replace( '/[^0-9]/', '', $phone );

		// Use last 10 digits if longer.
		if ( strlen( $clean ) > 10 ) {
			$clean = substr( $clean, -10 );
		}

		return $clean;
	}

	/**
	 * Get email subject.
	 *
	 * @return string Email subject.
	 */
	private function get_email_subject() {
		$subject = get_option( 'atlr_coupon_email_subject', '' );

		if ( empty( $subject ) ) {
			$subject = sprintf(
				/* translators: %s: site name */
				__( 'Your Credit Coupon from %s', 'atlas-returns' ),
				get_bloginfo( 'name' )
			);
		}

		return $subject;
	}

	/**
	 * Get email content.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $coupon_code Coupon code.
	 * @param float     $amount Coupon amount.
	 * @param string    $expiry_date Expiry date string.
	 * @return string Email HTML content.
	 */
	private function get_email_content( \WC_Order $order, $coupon_code, $amount, $expiry_date ) {
		// Check for custom template.
		$template_path = ATLR_PLUGIN_DIR . 'templates/emails/coupon-created.php';

		if ( file_exists( $template_path ) ) {
			ob_start();
			include $template_path;
			return ob_get_clean();
		}

		// Default email content.
		$site_name  = get_bloginfo( 'name' );
		$first_name = $order->get_billing_first_name();

		$html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';

		$html .= sprintf(
			'<p>%s</p>',
			sprintf(
				/* translators: %s: customer first name */
				esc_html__( 'Dear %s,', 'atlas-returns' ),
				esc_html( $first_name )
			)
		);

		$html .= sprintf(
			'<p>%s</p>',
			esc_html__( 'We have created a coupon for you as part of your return order. You can use this coupon for your next purchase.', 'atlas-returns' )
		);

		$html .= '<div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">';
		$html .= sprintf(
			'<p><strong>%s</strong> %s</p>',
			esc_html__( 'Coupon Code:', 'atlas-returns' ),
			'<code style="background: #fff; padding: 5px 10px; font-size: 18px;">' . esc_html( $coupon_code ) . '</code>'
		);
		$html .= sprintf(
			'<p><strong>%s</strong> %s</p>',
			esc_html__( 'Amount:', 'atlas-returns' ),
			wp_kses_post( wc_price( $amount ) )
		);
		$html .= sprintf(
			'<p><strong>%s</strong> %s</p>',
			esc_html__( 'Valid Until:', 'atlas-returns' ),
			esc_html( $expiry_date )
		);
		$html .= '</div>';

		$html .= sprintf(
			'<p>%s</p>',
			sprintf(
				/* translators: %s: site name */
				esc_html__( 'Thank you for shopping with %s!', 'atlas-returns' ),
				esc_html( $site_name )
			)
		);

		$html .= '</div>';

		return $html;
	}
}
