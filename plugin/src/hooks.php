<?php
/**
 * Various global hook functions.
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */

namespace InvisibleUs\StudyAbroad;

add_filter('register_taxonomy_args', __NAMESPACE__ . '\options_register_taxonomy_args', 5, 2);
add_filter('document_title_parts', __NAMESPACE__ . '\document_title_parts', 10, 3);
add_filter('get_the_archive_title_prefix', __NAMESPACE__ . '\get_the_archive_title_prefix', 5);
add_filter('post_type_archive_title', __NAMESPACE__ . '\options_post_type_archive_title', 5, 2);
add_filter('get_the_post_type_description', __NAMESPACE__ . '\options_post_type_description', 5, 2);
add_filter('taxonomy_labels_nvis_location', __NAMESPACE__ . '\options_location_labels');
add_filter('taxonomy_labels_nvis_subject', __NAMESPACE__ . '\options_subject_labels');
add_filter('taxonomy_labels_nvis_term', __NAMESPACE__ . '\options_term_labels');
add_filter('taxonomy_labels_nvis_sponsor', __NAMESPACE__ . '\options_sponsor_labels');
add_action('wp_head', __NAMESPACE__ . '\options_wp_head', 7);
add_filter('body_class', __NAMESPACE__ . '\body_class', 10, 3);
add_filter('term_link', __NAMESPACE__ . '\term_link', 10, 3);
add_filter('nvis/studyabroad/before_main_content', __NAMESPACE__ . '\before_main_content');
add_filter('nvis/studyabroad/after_main_content', __NAMESPACE__ . '\after_main_content');
add_filter('nvis/template_defaults', __NAMESPACE__ . '\options_template_defaults', 5, 2);
add_filter('nvis/template_args', __NAMESPACE__ . '\options_template_args', 5, 2);
add_filter('nvis/studyabroad/sync_set_first_featured', __NAMESPACE__ . '\options_set_first_featured', 5);
add_filter('embed_oembed_html', __NAMESPACE__ . '\add_video_title_attribute', PHP_INT_MAX, 3);

/**
 * Modifies the taxonomy args based on plugin options.
 *
 * @param array $args Array of arguments for registering a taxonomy.
 *                       See the register_taxonomy() function for accepted arguments.
 * @param string $taxonomy Taxonomy key.
 * @return array
 */
function options_register_taxonomy_args(array $args, string $taxonomy): array {
    $archive_taxonomies = [
        Location::TAXONOMY,
        Subject::TAXONOMY,
        Sponsor::TAXONOMY,
    ];

    if (!in_array($taxonomy, $archive_taxonomies)) {
        return $args;
    }

    $taxonomy = str_replace('nvis_', '', $taxonomy);
    $enable_archive = Plugin::get_option($taxonomy . '_enable_archive', -1);

    if ($enable_archive !== -1) {
        /*
         * The first test determines whether an option has been saved.
         * The second test checks the value before acting.
         */
        if (!$enable_archive) {
            $args['rewrite'] = false;
        }
    }

    return $args;
}

/**
 * Updates the title for filtered results in archives and program subpages.
 *
 * Called on filter: document_title_parts
 *
 * @param array $title The current title parts.
 */
function document_title_parts(array $title): array {

    if (nvis_is_filtered_results(Plugin::post_types())) {
        $post_type = get_query_var('post_type');
        /**
         * Filters the format of the document title part of filtered post type archives.
         *
         * @param $format The format of the document title part.
         * @param $post_type The current post type.
         */
        $format = apply_filters(
            'nvis/filter_results_document_title_format',
            '%1$s, %2$s',
            $post_type
        );

        /**
         * Filters the label indicating 'Filtered Results' in the document title.
         *
         * @param $label The filtered results label.
         * @param $post_type The current post type.
         */
        $filtered_label = apply_filters(
            'nvis/filtered_results_document_title_label',
            Plugin::get_label('filtered_results'),
            $post_type
        );

        $title['title'] = sprintf(
            $format,
            post_type_archive_title('', false),
            $filtered_label
        );

        return $title;
    }

    return $title;
}

function get_the_archive_title_prefix($prefix) {
    return sprintf(
        '<span class="prefix">%s</span>',
        $prefix
    );
}

function options_post_type_archive_title($title, $post_type) {
    if (in_array($post_type, Plugin::post_types())) {
        $post_type = str_replace('nvis_', '', $post_type);
        $new_title = Plugin::get_option($post_type . '_archive_title');

        if ($new_title) {
            $title = esc_html($new_title);
        }
    }
    return $title;
}

/**
 * Updates the post type description based on plugin option.
 *
 * Called on filter: `get_the_post_type_description`
 *
 * @since 0.1.0
 *
 * @param string $description The post type description.
 * @param WP_Post_Type $post_type_obj The current post type object.
 * @return string The filtered post type description.
 */
function options_post_type_description($description,  $post_type_obj) {
    if (in_array($post_type_obj->name, Plugin::post_types())) {
        $post_type = str_replace('nvis_', '', $post_type_obj->name);
        $new_desc = Plugin::get_option($post_type . '_archive_description');

        if ($new_desc) {
            $description = wp_kses($new_desc, wp_kses_allowed_html('post'));
        }
    }

    return $description;
}

function options_wp_head() {
    $style_tag = '<style>html body{%s}</style>';
    $var_ptrn = '--nvis-%s: %s';
    $options = [
        'active_color',
        'active_color_text'
    ];
    $vars = [];

    foreach($options as $option) {
        $value = Plugin::get_option($option);

        if ($value) {
            $vars[] = sprintf(
                $var_ptrn,
                str_replace('_', '-', $option),
                $value
            );
        }
    };

    if (!empty($vars)) {
        printf(
            $style_tag,
            implode(';', $vars)
        );
    }
}

/**
 * Adds body class name based on presentation mode.
 *
 * @param array $classes An array of body class names.
 * @return array The resulting array of body class names.
 */
function body_class(array $classes): array {
    $presentation_mode = Plugin::get_option('presentation_mode');

    if ($presentation_mode) {
        $classes[] = 'nvis-present-mode--' . $presentation_mode;
    }

    return $classes;
}

/**
 * Changes the behavior of term links when the taxonomy does not support archives.
 *
 * Replaces the home_url with the current post type archive link except for
 * pages and posts.
 *
 * Called on filter: `term_link`
 *
 * @param string $link
 * @return string
 */
function term_link(string $link, $term, $taxonomy): string {
    if (!in_array($taxonomy, Plugin::taxonomies())) {
        return $link;
    }

    $query_start = strpos($link, '?');

    if ($query_start !== false) {
        $link = get_post_type_archive_link(Program::POST_TYPE) . substr($link, $query_start);
    }

    return $link;
}

function options_location_labels($labels) {
    return options_taxonomy_labels($labels, Location::TAXONOMY);
}

function options_subject_labels($labels) {
    return options_taxonomy_labels($labels, Subject::TAXONOMY);
}

function options_term_labels($labels) {
    return options_taxonomy_labels($labels, Term::TAXONOMY);
}

function options_sponsor_labels($labels) {
    return options_taxonomy_labels($labels, Sponsor::TAXONOMY);
}

function options_taxonomy_labels($labels, $taxonomy) {
    $tax = str_replace('nvis_', '', $taxonomy);

    $single = Plugin::get_option($tax . '_label_single');
    $plural = Plugin::get_option($tax . '_label_plural');

    if ($single) {
        $labels->singular_name = $single;
    }

    if ($plural) {
        $labels->name = $plural;
    }

    return $labels;
}

/**
 * Action to output opening tag of main content wrapper.
 *
 * Called on: nvis/studyabroad/before_main_content
 *
 * @return void
 */
function before_main_content() {
    $pattern = '<%s id="%s" class="%s">';
    $id = apply_filters('nvis/studyabroad/main_content_wrapper_id', 'main-content-wrapper');
    $classes = ['nvis-progs-template', 'nvis-template'];
    $classes = apply_filters('nvis/careers/main_content_wrapper_class', $classes);
    $tag = Plugin::get_option('main_content_wrapper_tag', 'div');

    printf(
        $pattern,
        $tag,
        $id,
        esc_attr(implode(' ', $classes))
    );
}

/**
 * Action to output end tag of main content wrapper.
 *
 * Called on: nvis/studyabroad/after_main_content
 *
 * @return void
 */
function after_main_content() {
    $tag = Plugin::get_option('main_content_wrapper_tag', 'div');

    echo "</{$tag}>";
}

/**
 * Updated the template defaults based on the plugin options.
 *
 * Called on filter: `nvis/template_defaults`
 *
 * @param array $defaults The template defaults.
 * @param string $template The name of the template.
 * @return array The filtered defaults.
 */
function options_template_defaults($defaults, $template) {
    $post_type = get_post_type();
    $taxonomy = get_query_var('taxonomy');

    if (!in_array($post_type, Plugin::post_types()) && !in_array($taxonomy, Plugin::taxonomies())) {
        return $defaults;
    }
 
    $post_type = str_replace('nvis_', '', $post_type);
    $presentation_mode = Plugin::get_option('presentation_mode');

    switch($template) {
        case 'common/breadcrumbs':
            $defaults['show_breadcrumbs'] = Plugin::get_option('display_breadcrumbs', true);
            break;
        case 'common/page-header-backdrop':
            $defaults = options_page_header_backdrop($defaults, $post_type, $presentation_mode);

            break;
        case 'common/term-featured-image':
            $defaults = options_header_image_size($defaults);

            break;
        case 'common/post-type-featured-image':
            $defaults = options_header_image_size($defaults);
            $defaults['featured_img'] = Plugin::get_option($post_type . '_archive_featured_image');

            break;
        case 'archive-program/program-item': 
            $defaults['show_image'] = Plugin::get_option($post_type . '_archive_show_images', true);
            $defaults['show_featured_label'] = Plugin::get_option($post_type . '_archive_featured_label_enable', true);

            break;
        case 'common/post-featured-image':
            $defaults = options_post_featured_image($defaults, $post_type, $presentation_mode);

            break;
        case 'archive-program/featured-label':
            $defaults = options_featured_label($defaults);

            break;
        case 'single-program/brochure-sections':
            $defaults = options_brochure_sections($defaults);

            break;
        case 'single-program/contact':
            $defaults = options_sidebar_contact($defaults);

            break;
        case 'single-program/media-gallery':
            $defaults = options_media_gallery($defaults);

            break;            
        case 'single-program/program-dates':
            $defaults = options_program_dates($defaults);

            break;
        case 'single-program/additional-details':
            $defaults = options_additional_details($defaults);

            break;
        default:
            break;
    }

    return $defaults;
}

function options_template_args($args, $template) {
    $post_type = get_post_type();
    $taxonomy = get_query_var('taxonomy');

    if (!in_array($post_type, Plugin::post_types()) && !in_array($taxonomy, Plugin::taxonomies())) {
        return $args;
    }

    switch ($template) {
        case 'common/post-featured-image':
            if ($post_type === Program::POST_TYPE) {
                $args = options_map_featured_image($args);
            }

            break;
        case 'common/filters':
            $args = options_search_filters($args);

            break;
        case 'common/filters/taxonomy':
            $args = options_taxonomy_filter($args);

            break;
        case 'common/action-list':
            $args = options_action_list($args);

            break;
        default:
            break;
    }

    return $args;
}

function options_map_featured_image($args) {
    if (!Plugin::get_option('sap_insert_map_featured')) {
        return $args;
    }

    $post = nvis_args_or_global('post', $args);
    $locations = nvis_sap_get_program_locations($post);
    $map_src = is_array($locations) && count($locations) ? 
        nvis_sap_get_map_image_url($locations, $args) :
        false;
    
    if ($map_src) {
        $args['fallback'] = $map_src;
        $args['image_attributes']['alt'] = nvis_sap_get_locations_alt_text($locations);
    }

    return $args;
}

function options_search_filters($args) {
    $post_type = get_post_type();
    $post_type = $post_type ? $post_type : get_query_var('post_type');
    $post_type = str_replace('nvis_', '', $post_type);

    $enabled = Plugin::get_option($post_type . '_archive_search_filters', true);

    if (!is_array($enabled)) {
        return $args;
    }

    $args['filters'] = [];

    $tax_filters_map = Plugin::get_tax_filters_map();

    foreach ($enabled as $filter) {
        if (array_key_exists($filter, $tax_filters_map)) {
            if (taxonomy_exists($tax_filters_map[$filter])) {
                $args['filters'][] = $filter;
            }
        } else {
            $args['filters'][] = $filter;
        }
    }

    return $args;
}

function options_taxonomy_filter($args) {
    if (empty($args['taxonomy'])) {
        return $args;
    }

    $tax_object = get_taxonomy($args['taxonomy']);
    $query_var = get_query_var($tax_object->query_var, false);

    if ($query_var) {
        return $args;
    }

    $terms = \InvisibleUs\StudyAbroad\get_current_posts_limited_terms($args['taxonomy']);

    if (empty($terms)) {
        // Send an array with a single, improbable, `term_id` to prevent dropdown_categories from ignoring the arg.
        $terms = [1];
    }

    $args['include'] = !is_wp_error($terms) ? $terms : null;

    return $args;
}


function options_action_list($args) {
    if ($args['context'] === 'single-program/program-actions') {
        $prefix = 'foreign_program';

        foreach ($args['actions'] as $i => &$action) {
            $option = "{$prefix}_action_{$action['key']}";

            if ($action['key'] === 'visit_homepage') {
                $enabled = Plugin::get_option($option . '_enable', -1);

                if ($enabled !== -1) {
                    $view = is_post_type_archive(Program::POST_TYPE) ? 'archive' : 'single';
                    
                    if (!$enabled || (is_array($enabled) && !in_array($view, $enabled))) {
                        unset($args['actions'][$i]);
                        continue;
                    }
                }
            }

            $value = Plugin::get_option($option . '_label');

            if ($value) {
                $action['label'] = $value;
            }

            $value = Plugin::get_option($option . '_icon', -1);
            $value = $value !== -1 ? strtolower($value) : $value;

            if ($value === 'none') {
                $action['icon'] = '';
            } else if ($value && $value !== -1) {
                $action['icon'] = nvis_sap_get_icon($value, ['size' => 20, 'class' => 'nvis-icon--md']);
            }
        }
    }

    return $args;
}

/**
 * Filters the template defaults for `common/post-featured-image` based on plugin options.
 *
 * @param array $defaults The template defaults.
 * @return array The filtered defaults.
 */
function options_post_featured_image($defaults, $post_type, $presentation_mode) {
    $zoom_class = 'featured-image--zoom-hover';
    $photo_class = 'featured-image--photo-style';
    $post_types = Plugin::post_types();

    if (!is_post_type_archive($post_types) && !is_singular($post_types)) {
        return $defaults;
    }

    $defaults['fallback'] = Plugin::get_option($post_type . '_featured_image');

    if (is_post_type_archive()) {
        $defaults['show_image'] = Plugin::get_option($post_type . '_archive_show_images', true);

        if ($presentation_mode === 'full') {
            $defaults['image_wrapper_class'] = $zoom_class;
        }
    }

    if (is_singular()) {
        $defaults = options_header_image_size($defaults);

        if ($presentation_mode === 'full') {
            $defaults['image_wrapper_class'] = $photo_class;
        }
    }

    return $defaults;
}

function options_featured_label($defaults) {
    $base = 'foreign_program_archive_featured_label_';
    $src = Plugin::get_option($base . 'icon_src');
    $text = Plugin::get_option($base . 'text', -1);

    switch ($src) {
        case 'image':
            $defaults['attachment_id'] = Plugin::get_option($base . 'image');
            $defaults['image_size'] = Plugin::get_option($base . 'image_size');

            break;
        case 'heroicon':
            $defaults['icon'] = Plugin::get_option($base . 'icon');

            break;
        case 'none':
        default:
            $defaults['icon'] = '';
            $defaults['attachment_id'] = 0;

    }

    if ($text !== -1) {
        $defaults['text'] = Plugin::get_option($base . 'text');
    }

    return $defaults;
}

function options_brochure_sections($defaults) {
    $label = Plugin::get_option('foreign_program_label_brochure_section', -1);

    if ($label !== -1 && $label) {
        $defaults['heading'] = $label;
    }

    $headings = Plugin::get_option('sap_enhanced_brochure_show_headings', -1);

    if ($headings !== -1) {
        $defaults['show_section_headings'] = $headings;
    }

    $insert = Plugin::get_option('sap_insert_map_brochure', -1);

    if ($insert !== -1) {
        $defaults['insert_location_map'] = $insert;
    }

    $extract_contact = Plugin::get_option('sap_enhanced_brochure_extract_contact', -1);

    if ($extract_contact !== -1) {
        $defaults['hide_contact_section'] = $extract_contact;
    }

    return $defaults;
}

function options_media_gallery($defaults) {
    $insert = Plugin::get_option('sap_insert_map_gallery', -1);

    if ($insert !== -1 && !$insert) {
        $defaults['map'] = false;
    }

    $lightbox = Plugin::get_option('foreign_program_enable_lightbox', -1);

    if ($lightbox !== -1) {
        $defaults['lightbox_images'] = $lightbox;
    }

    $min_images = Plugin::get_option('foreign_program_min_images', -1);

    if ($min_images !== -1) {
        $defaults['min_images'] = $min_images;
    }

    return $defaults;
}

function options_program_dates($defaults) {
    $label = Plugin::get_option('foreign_program_label_program_dates', -1);

    if ($label !== -1 && $label) {
        $defaults['heading'] = $label;
    }
    
    $show_decision = Plugin::get_option('foreign_program_enable_decision_date', -1);

    if ($show_decision !== -1) {
        $defaults['show_decision_date'] = $show_decision;
    }

    return $defaults;
}

function options_additional_details($defaults) {
    $label = Plugin::get_option('foreign_program_label_additional_details', -1);

    if ($label !== -1 && $label) {
        $defaults['heading'] = $label;
    }

    $show_key_params = Plugin::get_option('foreign_program_enable_key_parameters', -1);

    if ($show_key_params !== -1) {
        $defaults['show_key_params'] = $show_key_params;
    }

    return $defaults;
}

function options_sidebar_contact($defaults) {
    $fields = [
        'show_contact_section' => 'sap_enhanced_brochure_extract_contact',
        'heading' => 'foreign_program_label_program_contact'
    ];

    foreach ($fields as $arg => $option) {
        $value = Plugin::get_option($option, -1);
    
        if ($value !== -1 && $value) {
            $defaults[$arg] = $value;
        }
    }

    return $defaults;
}

function options_header_image_size($defaults) {
    $size = Plugin::get_option('image_size_header');

    if ($size) {
        $defaults['image_size'] = $size;

        if ($defaults['image_size'] === 'custom') {
            $defaults['image_size'] = [
                (int) Plugin::get_option('image_size_header_w'),
                (int) Plugin::get_option('image_size_header_h'),
            ];
        }
    }

    return $defaults;
}

function options_page_header_backdrop($defaults, $post_type, $presentation_mode) {
    if ($presentation_mode !== 'full') {
        return $defaults;
    }

    $defaults['show_backdrop'] = true;
    
    if (is_archive()) {
        if (is_tax()) {
            return options_tax_header_backdrop($defaults);
        }

        if (is_post_type_archive()) {
            $defaults['attachment_id'] = Plugin::get_option($post_type . '_archive_header_background');
            return $defaults;
        }
    } 
    
    if (is_singular()) {
        return options_singular_header_backdrop($defaults, $post_type);
    }

    return $defaults;
}

function options_singular_header_backdrop($defaults, $post_type) {
    if (is_singular(Program::POST_TYPE)) {
        $map_header_bg = Plugin::get_option('sap_insert_map_header');

        if ($map_header_bg) {
            $terms = Program::get_locations();

            if (is_array($terms) && !empty($terms)) {
                $map_args = options_get_map_background_args();
                $defaults['image_source'] = nvis_sap_get_map_image_url($terms, $map_args);
                $defaults['wrapper_class'] = 'map-image';

                return $defaults;
            } 
        }
    }

    if (!$defaults['image_source']) {
        $img = Plugin::get_option($post_type . '_header_background');

        if ($img) {
            $defaults['attachment_id'] = $img;

            return $defaults;
        } 
        
        if ($defaults['fallback_to_post']) {
            $img = Plugin::get_option($post_type . '_default_featured_image');

            if ($img) {
                $defaults['attachment_id'] = $img;

                return $defaults;
            }
        }
    }

    return $defaults;
}

function options_tax_header_backdrop($defaults) {
    $taxonomy = get_query_var('taxonomy');
    $term = get_term_by('slug', get_query_var('term'), $taxonomy);
    $background = get_field('header_background', $term);
    
    // First, let's check for explicit header background set on this term.
    if ($background) {
        $defaults['attachment_id'] = $background;
        return $defaults;
    } 

    // Next, map fallbacks requested?
    if ($taxonomy === Location::TAXONOMY && Plugin::get_option('sap_insert_map_header')) {
        $coords = Location::get_coords($term);

        if ($coords) {
            $defaults['image_source'] = nvis_sap_get_map_image_url(
                [$term], 
                options_get_map_background_args(3)
            );
            $defaults['wrapper_class'] = 'map-image';

            return $defaults;
        }
    }

    // Did they set a featured image? 
    $featured = get_field('featured_image', $term);

    if ($featured) {
        $defaults['attachment_id'] = $featured;
        return $defaults;
    }

    // Is there a default on the post type?
    $taxonomy = get_taxonomy($taxonomy);

    if (!empty($taxonomy->object_type)) {
        $post_type = str_replace('nvis_', '', $taxonomy->object_type[0]);
        $defaults['attachment_id'] = Plugin::get_option($post_type . '_archive_header_background');
        return $defaults;
    }

    return $defaults;
}

function options_get_map_background_args(?int $zoom=4) {
    $zoom = min(max($zoom, 0), 16);

    $map_args = [
        'width' => $GLOBALS['content_width'], 
        'height' => ceil($GLOBALS['content_width'] / 2),
        'show_markers' => false,
        'hd' => false,
        'zoom' => $zoom,
        'offset_long' => 9,
        'style' => 'mapbox/satellite-v9'
    ];

    return apply_filters('nvis/studyabroad/map_background_args', $map_args);
}

function options_set_first_featured(bool $first_featured): bool {
    $option = Plugin::get_option('nvis_sap_enhanced_brochure_first_featured', -1);

    if ($option !== -1) {
        return (bool) $option;
    }

    return $first_featured;
}

function add_video_title_attribute($html, $url, $args) {
    if (!isset($args['video_url'])) {
        // This is not our video template. 
        return $html;
    }

    if (strpos($html, 'title') !== false) {
        return $html;
    }

    $search = '<iframe ';
    return str_replace($search, $search . 'title="featured-video" ', $html);
}