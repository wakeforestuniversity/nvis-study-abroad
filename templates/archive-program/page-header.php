<?php
/**
 * Template for displaying the Program archive page header.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$args['context'] = $template;
nvis_sap_get_template_part('common/post-type-archive-page-header', $args);
