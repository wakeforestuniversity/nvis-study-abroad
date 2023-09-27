<?php
/**
 * A template for displaying the Program archive search and filter form.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 *
 */

defined('ABSPATH') || exit;

$defaults = [
    'post_type' => 'nvis_foreign_program',
    'break_filters_after' => 7,
    'filters'   => [
        'keyword',
        'location',
        'subject',
        'term',
        'sponsor',
    ]
];

$args = nvis_parse_template_args($args, $defaults, $template);

nvis_sap_get_template_part('common/filters', $args);
