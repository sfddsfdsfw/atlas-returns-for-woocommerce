<?php
/**
 * Settings class.
 *
 * @package AtlasReturns\Admin
 */

namespace AtlasReturns\Admin;

/**
 * Class Settings
 *
 * Handles plugin settings using WordPress Settings API.
 */
class Settings {

	/**
	 * Settings option group.
	 */
	const OPTION_GROUP = 'atlr_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		// Register settings.
		register_setting(
			self::OPTION_GROUP,
			'atlr_shipping_cost',
			array(
				'type'              => 'number',
				'sanitize_callback' => array( $this, 'sanitize_float' ),
				'default'           => 2.00,
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_cod_fee',
			array(
				'type'              => 'number',
				'sanitize_callback' => array( $this, 'sanitize_float' ),
				'default'           => 1.00,
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_coupon_validity_days',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 180,
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_default_payment_method',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'cod',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_special_handling_note',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => __( 'SPECIAL HANDLING - PICKUP ON DELIVERY', 'atlas-returns' ),
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_enable_email_notifications',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_yes_no' ),
				'default'           => 'yes',
			)
		);

		register_setting(
			self::OPTION_GROUP,
			'atlr_remove_data_on_uninstall',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_yes_no' ),
				'default'           => 'yes',
			)
		);

		// Add settings sections.
		add_settings_section(
			'atlr_general_section',
			__( 'General Settings', 'atlas-returns' ),
			array( $this, 'render_general_section' ),
			'atlas-returns-settings'
		);

		add_settings_section(
			'atlr_costs_section',
			__( 'Cost Settings', 'atlas-returns' ),
			array( $this, 'render_costs_section' ),
			'atlas-returns-settings'
		);

		add_settings_section(
			'atlr_notifications_section',
			__( 'Notification Settings', 'atlas-returns' ),
			array( $this, 'render_notifications_section' ),
			'atlas-returns-settings'
		);

		add_settings_section(
			'atlr_advanced_section',
			__( 'Advanced Settings', 'atlas-returns' ),
			array( $this, 'render_advanced_section' ),
			'atlas-returns-settings'
		);

		// Add settings fields.
		// Costs section.
		add_settings_field(
			'atlr_shipping_cost',
			__( 'Shipping Cost', 'atlas-returns' ),
			array( $this, 'render_number_field' ),
			'atlas-returns-settings',
			'atlr_costs_section',
			array(
				'id'          => 'atlr_shipping_cost',
				'description' => __( 'Shipping cost charged for customer-fault returns.', 'atlas-returns' ),
				'step'        => '0.01',
				'min'         => '0',
			)
		);

		add_settings_field(
			'atlr_cod_fee',
			__( 'COD Fee', 'atlas-returns' ),
			array( $this, 'render_number_field' ),
			'atlas-returns-settings',
			'atlr_costs_section',
			array(
				'id'          => 'atlr_cod_fee',
				'description' => __( 'Cash on Delivery fee charged for customer-fault returns.', 'atlas-returns' ),
				'step'        => '0.01',
				'min'         => '0',
			)
		);

		// General section.
		add_settings_field(
			'atlr_coupon_validity_days',
			__( 'Coupon Validity (Days)', 'atlas-returns' ),
			array( $this, 'render_number_field' ),
			'atlas-returns-settings',
			'atlr_general_section',
			array(
				'id'          => 'atlr_coupon_validity_days',
				'description' => __( 'Number of days before credit coupons expire.', 'atlas-returns' ),
				'step'        => '1',
				'min'         => '1',
			)
		);

		add_settings_field(
			'atlr_default_payment_method',
			__( 'Default Payment Method', 'atlas-returns' ),
			array( $this, 'render_payment_method_field' ),
			'atlas-returns-settings',
			'atlr_general_section',
			array(
				'id'          => 'atlr_default_payment_method',
				'description' => __( 'Default payment method for replacement orders.', 'atlas-returns' ),
			)
		);

		add_settings_field(
			'atlr_special_handling_note',
			__( 'Special Handling Note', 'atlas-returns' ),
			array( $this, 'render_textarea_field' ),
			'atlas-returns-settings',
			'atlr_general_section',
			array(
				'id'          => 'atlr_special_handling_note',
				'description' => __( 'Note added to orders requiring special handling (pickup on delivery).', 'atlas-returns' ),
			)
		);

		// Notifications section.
		add_settings_field(
			'atlr_enable_email_notifications',
			__( 'Email Notifications', 'atlas-returns' ),
			array( $this, 'render_checkbox_field' ),
			'atlas-returns-settings',
			'atlr_notifications_section',
			array(
				'id'    => 'atlr_enable_email_notifications',
				'label' => __( 'Enable email notifications for coupon creation.', 'atlas-returns' ),
			)
		);

		// Advanced section.
		add_settings_field(
			'atlr_remove_data_on_uninstall',
			__( 'Remove Data on Uninstall', 'atlas-returns' ),
			array( $this, 'render_checkbox_field' ),
			'atlas-returns-settings',
			'atlr_advanced_section',
			array(
				'id'    => 'atlr_remove_data_on_uninstall',
				'label' => __( 'Delete all plugin data when uninstalling.', 'atlas-returns' ),
			)
		);
	}

	/**
	 * Render general section description.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure general return processing settings.', 'atlas-returns' ) . '</p>';
	}

	/**
	 * Render costs section description.
	 */
	public function render_costs_section() {
		echo '<p>' . esc_html__( 'Configure costs charged for customer-fault returns.', 'atlas-returns' ) . '</p>';
	}

	/**
	 * Render notifications section description.
	 */
	public function render_notifications_section() {
		echo '<p>' . esc_html__( 'Configure email notification settings.', 'atlas-returns' ) . '</p>';
	}

	/**
	 * Render advanced section description.
	 */
	public function render_advanced_section() {
		echo '<p>' . esc_html__( 'Advanced plugin settings.', 'atlas-returns' ) . '</p>';
	}

	/**
	 * Render a number input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_number_field( $args ) {
		$value = get_option( $args['id'], '' );
		$step  = isset( $args['step'] ) ? $args['step'] : '1';
		$min   = isset( $args['min'] ) ? $args['min'] : '0';

		printf(
			'<input type="number" id="%1$s" name="%1$s" value="%2$s" step="%3$s" min="%4$s" class="regular-text" />',
			esc_attr( $args['id'] ),
			esc_attr( $value ),
			esc_attr( $step ),
			esc_attr( $min )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_textarea_field( $args ) {
		$value = get_option( $args['id'], '' );

		printf(
			'<textarea id="%1$s" name="%1$s" rows="3" class="large-text">%2$s</textarea>',
			esc_attr( $args['id'] ),
			esc_textarea( $value )
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$value = get_option( $args['id'], 'yes' );

		printf(
			'<label><input type="checkbox" id="%1$s" name="%1$s" value="yes" %2$s /> %3$s</label>',
			esc_attr( $args['id'] ),
			checked( $value, 'yes', false ),
			esc_html( $args['label'] )
		);
	}

	/**
	 * Render payment method dropdown.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_payment_method_field( $args ) {
		$value = get_option( $args['id'], 'cod' );

		// Get available payment gateways.
		$gateways = array(
			'cod'  => __( 'Cash on Delivery', 'atlas-returns' ),
			'bacs' => __( 'Bank Transfer', 'atlas-returns' ),
		);

		// Add WooCommerce payment gateways if available.
		if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			foreach ( $available_gateways as $gateway_id => $gateway ) {
				$gateways[ $gateway_id ] = $gateway->get_title();
			}
		}

		echo '<select id="' . esc_attr( $args['id'] ) . '" name="' . esc_attr( $args['id'] ) . '">';
		foreach ( $gateways as $gateway_id => $gateway_title ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $gateway_id ),
				selected( $value, $gateway_id, false ),
				esc_html( $gateway_title )
			);
		}
		echo '</select>';

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Sanitize float value.
	 *
	 * @param mixed $value Input value.
	 * @return float Sanitized value.
	 */
	public function sanitize_float( $value ) {
		return (float) $value;
	}

	/**
	 * Sanitize yes/no value.
	 *
	 * @param mixed $value Input value.
	 * @return string 'yes' or 'no'.
	 */
	public function sanitize_yes_no( $value ) {
		return ( 'yes' === $value ) ? 'yes' : 'no';
	}
}
