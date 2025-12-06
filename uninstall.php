<?php
/**
 * Plugin uninstall handler.
 *
 * This file is called when the plugin is deleted from WordPress.
 * It removes all plugin data from the database.
 *
 * @package AtlasReturns
 */

// Exit if not called by WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Check if data should be removed on uninstall.
 * Users can set this option to 'no' if they want to keep data.
 */
$remove_data = get_option( 'atlr_remove_data_on_uninstall', 'yes' );

if ( 'yes' !== $remove_data ) {
    return;
}

global $wpdb;

// Remove custom database table.
$table_name = $wpdb->prefix . 'atlr_returns';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall cleanup.
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Remove plugin options.
$options_to_delete = array(
    'atlr_shipping_cost',
    'atlr_cod_fee',
    'atlr_coupon_validity_days',
    'atlr_default_payment_method',
    'atlr_special_handling_note',
    'atlr_enable_email_notifications',
    'atlr_monthly_return_limit',
    'atlr_activated_time',
    'atlr_db_version',
    'atlr_activation_errors',
    'atlr_monthly_returns_count',
    'atlr_monthly_returns_reset_date',
    'atlr_remove_data_on_uninstall',
);

foreach ( $options_to_delete as $option ) {
    delete_option( $option );
}

// Remove capabilities from roles.
$roles_with_cap = array( 'administrator', 'shop_manager', 'cooperator' );

foreach ( $roles_with_cap as $role_name ) {
    $role = get_role( $role_name );
    if ( $role ) {
        $role->remove_cap( 'manage_atlas_returns' );
    }
}

// Clear any transients.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_atlr_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_atlr_%'" );
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

// Clear scheduled cron events.
wp_clear_scheduled_hook( 'atlr_daily_cleanup' );
wp_clear_scheduled_hook( 'atlr_weekly_report' );
