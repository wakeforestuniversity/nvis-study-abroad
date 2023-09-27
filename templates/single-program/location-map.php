<?php
/**
 * The template for displaying the Program's location(s) on a map.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'width' => $GLOBALS['content_width'],
    'height' => ceil($GLOBALS['content_width'] / 2),
    'alt' => sprintf(nvis_sap_get_label('program_map'), nvis_sap_get_location_label($post)),
    'zoom' => 4,
    'show_markers' => true,
    'hd' => true,
    'lazy' => true,
    'style' => nvis_sap_get_map_style()
];

$args = nvis_parse_template_args($args, $defaults, $template);

$locations = nvis_sap_get_program_locations($post);
$map_src = is_array($locations) && count($locations) ? 
    nvis_sap_get_map_image_url($locations, $args) :
    false;

if ($map_src) {
    $size = [
        $args['width'],
        $args['height'],
    ];

    $attrs = [
        'loading' => $args['lazy'] ? 'lazy' : false,
        'class' => 'map-image remote-image',
        'alt' => $args['alt']
    ];

    $img_tag = nvis_get_remote_image_tag($map_src, $size, $attrs);

    printf('<span class="location-map">%s</span>', $img_tag);
}
