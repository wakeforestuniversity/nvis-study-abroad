<?php

namespace InvisibleUs\StudyAbroad;

add_action('init', __NAMESPACE__ . '\add_shortcodes');

function add_shortcodes() {
    add_shortcode('nvis_locations', __NAMESPACE__ . '\render_locations_shortcode');
}

function render_locations_shortcode($atts) {
    $term_name_str = '{$term_name}';
    $atts = shortcode_atts(
        [
            'type'               => 'region',
            'parent'             => null,
            'title_tag'          => 'h2',
            'show_image'         => true,
            'show_description'   => true,
            'show_num_posts'     => true,
            'show_posts_link'    => true,
            'label_posts'        => nvis_get_post_type_label(Program::POST_TYPE, 'name'),
            'label_posts_single' => nvis_get_post_type_label(Program::POST_TYPE, 'singular_name'),
            'label_sr_text'      => 'in ' . $term_name_str,
            'posts_link_prefix'  => 'Explore',
            'wrapper_class'      => '',
            'columns'            => 3,
            'image_size'         => 'medium'

        ],
        $atts,
        'nvis_locations'
    );

    $atts['label_sr_text'] = str_replace(
        ['%', $term_name_str],
        ['%%', '%s'],
        $atts['label_sr_text']
    );

    $args = [
        'taxonomy' => Location::TAXONOMY,
        'meta_query' => [
            [
                'key' => 'location_type',
                'value' => $atts['type']
            ]
        ]
    ];

    if (!empty($atts['parent'])) {
        $args['parent'] = $atts['parent'];
    }

    $args = apply_filters('nvis/studyabroad/locations_shortcode_term_args', $args, $atts);
    $atts['terms'] = get_terms($args);

    if ($atts['type'] !== 'city') {
        foreach($atts['terms'] as &$term) {
            $term->count = get_padded_term_count($term);
        }
    } 

    ob_start();
    nvis_sap_get_template_part('common/terms-grid', $atts);

    return ob_get_clean();
}

// TODO: Relocate this function to somewhere more appropriate.
function get_padded_term_count($term, $post_type='any') {
    if (!(is_object($term) && $term instanceof \WP_Term)) {
        return 0;
    }

    $args = [
        'post_type' => $post_type,
        'posts_per_page' => 1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => $term->taxonomy,
                'terms' => $term->term_id,
                'include_children' => true,
            ]
        ]
    ];

    $q = new \WP_Query($args);

    return $q->found_posts;
}