<?php
/**
 * Displays a Sponsor taxonomy dropdown filter.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$defaults = [
    'taxonomy'  => 'nvis_sponsor',
    'query_var' => 'spnsr',
];

$args = nvis_parse_template_args($args, $defaults, $template);

nvis_sap_get_template_part('common/filters/taxonomy', $args);
