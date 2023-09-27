<?php
/**
 * Template tags specifically for this plugin.
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */

use \InvisibleUs\StudyAbroad\Plugin;
use \InvisibleUs\StudyAbroad\TemplateManager;
use \InvisibleUs\StudyAbroad\Program;
use \InvisibleUs\StudyAbroad\Location;
use \InvisibleUs\StudyAbroad\MapImage;

function nvis_sap_get_template_part(string $template, array $args = []) {
    TemplateManager::load_template($template, $args);
}

function nvis_sap_get_url($subpath='') {
    $url = trailingslashit(Plugin::$url);

    if ($subpath) {
        $url .= str_replace('../', '', $subpath);
    }

    return $url;
}

function nvis_sap_get_path($subpath='') {
    $path = trailingslashit(Plugin::$path);

    if ($subpath) {
        $path .= str_replace('../', '', $subpath);
    }

    return $path;
}

function nvis_sap_get_label(string $label): string {
    return Plugin::get_label($label);
}

function nvis_sap_get_map_style() {
    return MapImage::get_map_style();
}

function nvis_sap_program_is_featured($post=null) {
    return Program::is_featured($post);
}

function nvis_sap_get_program_action_url($action, $post=null) {
    return Program::get_action_url($action, $post);
}

function nvis_sap_get_program_dates($post=null, $format=null, $hide_past=false) {
    return Program::get_dates($post, $format, $hide_past);
}

function nvis_sap_get_program_brochure_sections($post=null) {
    return Program::get_brochure_sections($post);
}

function nvis_sap_get_program_locations($post) {
    return Program::get_locations($post);
}

function nvis_sap_get_locations_list($post) {
    return Program::get_locations_list($post);
}

function nvis_sap_get_location_label($post) {
    return Program::get_location_label($post);
}

function nvis_sap_get_locations_alt_text(array $locations) {
    $locations_str = implode(
        ', ',
        wp_list_pluck( $locations, 'full_name')
    );
    
    return sprintf(
        __('Map of: %s', 'nvis-study-abroad'),
        $locations_str
    );
}

function nvis_sap_get_program_key_params($post) {
    return Program::get_key_params($post);
}

function nvis_sap_filter_key_params($params) {
    $key_param_ids = get_field('nvis_sap_key_parameters_list', 'option');
    
    if (empty($key_param_ids) || !is_array($key_param_ids)) {
        return $params;
    }

    $key_param_ids = wp_list_pluck($key_param_ids, 'id');

    return array_filter($params, function($param) use($key_param_ids) {
        return !in_array($param['td_param_id'], $key_param_ids);
    });
}

function nvis_sap_get_sponsor_program_content($post) {
    return Program::get_sponsor_program_content($post);
}

function nvis_sap_get_program_images($post) {
    return Program::get_images($post);
}


function nvis_sap_program_has_video($post) {
    return get_field('video_url', $post);
}

function nvis_sap_contact_section_slug($post) {
    return Program::get_contact_section_slug($post);
}

function nvis_sap_get_truncated_term_list($post, $taxonomy, $args=[]) {
    $terms = get_the_terms($post, $taxonomy);

    if (is_wp_error($terms)) {
        return $terms;
    }

    if (empty($terms)) {
        return nvis_get_taxonomy_label($taxonomy, 'no_terms');
    }
    
    $defaults = [
        'before' => '',
        'sep' => ', ',
        'after' => '',
        'link_terms' => false,
        'max' => 5,
        'max_after' => ', …'
    ];

    $args = wp_parse_args($args, $defaults);

    $num_terms = count($terms);
    $max = max($args['max'], 1);
    $terms = array_slice($terms, 0, $max);
    
    if ($args['link_terms']) {
        $items = [];

        foreach ($terms as $term) {
            $items[] = sprintf(
                '<a href="%s">%s</a>',
                get_term_link($term, $taxonomy),
                esc_html($term->name)
            );
        }
    } else {
        $items = wp_list_pluck($terms, 'name');
    }

    $list = $args['before'] . implode($args['sep'], $items) . $args['after'];

     if ($num_terms > $max) {
         $list .= sprintf(
            $args['max_after'], 
            $num_terms,
            nvis_get_taxonomy_label($taxonomy, 'name')
        );
     } 
 
    return $list;
}

function nvis_sap_get_icon(string $icon, array $args=null) {
    $defaults = [
        'style' => 'solid',
        'size'  => 24,
        'class' => null
    ];

    $args = wp_parse_args($args, $defaults);

    $style = sanitize_file_name($args['style']);
    $size = (int) $args['size'];

    $icon = sanitize_file_name($icon);
    $file =  sprintf(
        '%sassets/img/heroicons/%d/%s/%s.svg',
        plugin_dir_path( __DIR__ ),
        $size,
        $style,
        $icon
    );

    $classes = [
        'nvis-icon',
        'nvis-icon-' . $icon,
        'nvis-icon-' . $style,
        sanitize_html_class($args['class'])
    ];

    if (file_exists($file)) {
        return sprintf(
            '<span class="%s">%s</span>',
            implode(' ', $classes),
            file_get_contents($file)
        );
    }

    return false;
}

function nvis_sap_get_scale_label($number, $scale) {
    arsort($scale);

    foreach ($scale as $label => $min) {
        if ($number >= $min) {
            return $label;
        }
    }

    return false;
}


function nvis_sap_get_map_image_url(?array $terms, array $args = []) {
    if (empty($terms)) {
        return false;
    }

    $coords = [];

    foreach ($terms as $term) {
        $c = Location::get_coords($term);

        if (!is_wp_error($c) && !empty($c['lat']) && !empty($c['long'])) {
            $coords[] = $c;
        }
    }

    return MapImage::get_url($coords, $args);
}

if (!function_exists('nvis_sap_get_post_types')) :
/**
 * Gets the list of post types registered by this plugin.
 * 
 * @since 0.1.0
 *
 * @return array An array of post type keys.
 */
function nvis_sap_get_post_types(): array {
    return Plugin::post_types();
}

endif;


if (!function_exists('nvis_sap_get_archive_title')):
/**
 * Gets the current archive title. 
 * 
 * This function merely subverts `get_the_archive_title`  when it is a post
 * type archive to prevent "Archives:" from being prepended to the title.
 *
 * @return string
 */
function nvis_sap_get_archive_title(): string {
    $title = '';

    if (is_post_type_archive(Plugin::post_types())) {
        $title = post_type_archive_title('', false);
    } else {
        $title = get_the_archive_title();
    }

    return $title;
}

endif;

