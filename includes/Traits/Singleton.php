<?php
/**
 * Singleton trait.
 *
 * @package AtlasReturns\Traits
 */

namespace AtlasReturns\Traits;

/**
 * Trait Singleton
 *
 * Implements the singleton pattern for classes.
 */
trait Singleton {

	/**
	 * The single instance of the class.
	 *
	 * @var static|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return static The singleton instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @throws \Exception When attempting to unserialize.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
