<?php
/**
 * Does the actual DB operations and is called via AJAX
 *
 *
 * @package   SFN Live Report
 * @author    Hampus Persson <hampus@hampuspersson.se>
 * @license   GPL-2.0+
 * @link      http://
 * @copyright 2013 Swedish Football Network
 */

if ( ! defined( 'WPINC' ) || !current_user_can( 'manage_options' ) ) {
	die;
}

if( $_POST['game'] ) {
	update_post_meta($_POST['game'], 'hemmares', $_POST['home']);
	update_post_meta($_POST['game'], 'bortares', $_POST['away']);
	update_post_meta($_POST['game'], 'matchtid', $_POST['time']);
	die($_POST['time']);
}