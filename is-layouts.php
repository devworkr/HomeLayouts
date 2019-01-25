<?php

/**
 * Plugin Name: Home Layouts
 * Plugin URI: https://example.com/
 * Description: A wordpress plugin to display categories with different layouts
 * Version: 1.0.0
 * Author: Dinesh Kumar
 * Author URI: https://dinesh.com
 * Text Domain: is-layouts
 * Domain Path: /i18n/languages/
 *
 * @package is-layouts
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define IS_LAYOUTS_PLUGIN_FILE.
if (!defined('IS_LAYOUTS_PLUGIN_FILE')) {
    define('IS_LAYOUTS_PLUGIN_FILE', __FILE__);
}

// Include the main SeoReview class.
if (!class_exists('IsLayouts')) {
    include_once dirname(__FILE__) . '/inc/class-is-layouts.php';
}

/**
 * Main instance of IsLayouts.
 *
 * Returns the main instance of IsLayouts.
 *
 * @since  1.0.0
 * @return IsLayouts
 */
function IsLayouts() {
    return IsLayouts::instance();
}

// Global for backwards compatibility.
$GLOBALS['seoreview'] = IsLayouts();
