<?php
/**
 * The template for displaying the document header.
 *
 * A thin wrapper around get_header().
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

get_header();

/**
 * Fires after the header is loaded before the main content.
 *
 * @since 0.1
 */
do_action('nvis/studyabroad/before_main_content');
