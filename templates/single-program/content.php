<?php
/**
 * The template for displaying the Program's content.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'content-templates' => [
        'single-program/description',
        'single-program/media-gallery',
        'single-program/brochure-sections',
        'single-program/sponsor-content',
        'single-program/program-dates',
        'single-program/additional-details'
    ]
];

$args = nvis_parse_template_args($args, $defaults, $template);

foreach ($args['content-templates'] as $template) {
    nvis_sap_get_template_part($template, compact('post'));
}
