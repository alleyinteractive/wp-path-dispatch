<?php
/**
 * Helpers for WP Path Dispatch.
 *
 * @package WP_Path_Dispatch
 */

namespace WP_Path_Dispatch;

/**
 * Get the instance of the Path_Dispatch class.
 *
 * @return Path_Dispatch
 */
function Path_Dispatch() {
	return Path_Dispatch::instance();
}
