<?php
/**
 * The template for displaying the document footer.
 *
 * A thin wrapper around get_footer().
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

/**
 * Fires just before the footer is loaded after the main content.
 *
 * @since 0.1
 */
do_action('nvis/studyabroad/after_main_content');

get_footer();
