<?php
/**
 * @package   SFN Live Report
 * @author    Hampus Persson <hampus@hampuspersson.se>
 * @license   GPL-2.0+
 * @link      http://
 * @copyright 2013 Swedish Football Network
 *
 * @wordpress-plugin
 * Plugin Name: SFN Live Report
 * Plugin URI:
 * Description: Simple plugin to enable a shortcode that generates a page for reporting
 * Version:     1.0.0
 * Author:      Hampus Persson
 * Author URI:  http://www.hampuspersson.se
 * Text Domain: sfn-live-report-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-sfn-live-report.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'SFN Live Report', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SFN Live Report', 'deactivate' ) );

SFN_Live_Report::get_instance();