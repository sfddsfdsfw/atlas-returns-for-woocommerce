<?php
/**
 * Plugin deactivator.
 *
 * @package AtlasReturns
 */

namespace AtlasReturns;

/**
 * Class Deactivator
 *
 * Handles plugin deactivation tasks.
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Note: This does NOT remove data. Data is only removed via uninstall.php
	 * when the plugin is deleted.
	 */
	public static function deactivate() {
		// Clear any scheduled cron events.
		self::clear_scheduled_events();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Clear scheduled cron events.
	 */
	private static function clear_scheduled_events() {
		// Clear any plugin-specific cron jobs.
		wp_clear_scheduled_hook( 'atlr_daily_cleanup' );
		wp_clear_scheduled_hook( 'atlr_weekly_report' );
	}
}
