<?php
/**
 * Displays a Semester taxonomy dropdown filter.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$defaults = [
    'taxonomy'  => 'nvis_location',
    'query_var' => 'loc',
    'hierarchical' => false,
    'hide_empty' => false,
    'meta_key' => 'location_type',
    'meta_value' => 'country'
];

$args = nvis_parse_template_args($args, $defaults, $template);

nvis_sap_get_template_part('common/filters/taxonomy', $args);
