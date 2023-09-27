<?php
/**
 * Breadcrumb adapter functions for supported third-party breadcrumb generators. 
 * 
 * Currently, that list includes:
 * - Breadcrumb NavXT
 * - YoastSEO
 * - All in One SEO
 * - Rank Math
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */

namespace InvisibleUs\StudyAbroad;

// Breadcrumb NavXT support.
add_action('bcn_after_fill', __NAMESPACE__ . '\navxt_replace_archive_trail');

// Yoast SEO Breadcrumb support.
add_filter('wpseo_breadcrumb_links', __NAMESPACE__ . '\yoast_update_trail');

// All in One SEO Breadcrumb support.
add_filter('aioseo_breadcrumbs_trail', __NAMESPACE__ . '\aioseo_update_trail');

// Rank Math Support.
add_filter('rank_math/frontend/breadcrumb/items', __NAMESPACE__ . '\rankmath_update_trail', 10, 2);


/**
 * Generates a crumb based on the current archive.
 *
 * @return array Associative array with 'text' and 'url' keys.
 */
function get_archive_crumb(): array {
    $found = false;

    $post_types = [
        Program::POST_TYPE
    ];

    foreach ($post_types as $post_type) {
        if (is_post_type_archive($post_type)) {
            $found = true;

            break;
        }
    }

    if ($found) {
        return [
            'text' => nvis_get_post_type_label($post_type, 'breadcrumb_label') ?? post_type_archive_title('', false),
            'url'  => get_post_type_archive_link($post_type)
        ];
    }

    return [];
}

/**
 * Gets the breadcrumb label that is indicates that post type archive is filtered. 
 *
 * @return string The breadcrumb label.
 */
function get_filtered_results_breadcrumb_label(): string {
    /**
     * Filters the breadcrumb label that is indicates that post type archive is filtered. 
     * 
     * @since 0.1
     * 
     * @param $label The breadcrumb label. 
     */
     return apply_filters('nvis/filtered_results_breadcrumb_label', Plugin::get_label('filtered_results'));
}

/**
 * Rebuilds the entire trail for archives when using Breadcrumb NavXT.
 *
 * Called on action: bcn_after_fill
 *
 * @param object $trail The current breadcrumb trail.
 * @return void
 */
function navxt_replace_archive_trail(object $trail) {
    $post_types = [Program::POST_TYPE];

    if (nvis_is_filtered_results($post_types)) {
        if ($trail->opt['bhome_display']) {
            $home = array_pop($trail->breadcrumbs);
        }
        $trail->breadcrumbs = [];
        $crumb = get_archive_crumb();

        $label = get_filtered_results_breadcrumb_label();
        $trail->add(new \bcn_breadcrumb($label, null, [], null, null, false));
        $trail->add(new \bcn_breadcrumb($crumb['text'], null, [], $crumb['url'], null, true));

        if ($trail->opt['bhome_display']) {
            $trail->breadcrumbs[] = $home;
        }
    }
}

/**
 * Updates the Yoast trail for program subpages and filtered results.
 *
 * Called on filter: wpseo_breadcrumb_links
 *
 * @param array $crumbs The current trail of crumbs.
 * @return array The filtered trail of crumbs.
 */
function yoast_update_trail(array $crumbs): array {
    $post_types = [Program::POST_TYPE];

    if (nvis_is_filtered_results($post_types)) {
        return yoast_replace_trail($crumbs);
    }

    return $crumbs;
}

/**
 * Rebuild the entire Yoast trail for the current archive.
 *
 * @param array $crumbs The current trail of crumbs.
 * @return array The new trail of crumbs.
 */
function yoast_replace_trail(array $crumbs): array {
    $home = array_shift($crumbs);

    return [
        $home,
        get_archive_crumb(),
        [
            'text' => get_filtered_results_breadcrumb_label(), 
            'url' => null
        ]
    ];
}

/**
 * Updates the AiOSEO trail for program subpages and filtered results.
 *
 * Called on filter: aioseo_breadcrumbs_trail
 *
 * @param array $crumbs The current trail of crumbs.
 * @return array The filtered trail of crumbs.
 */
function aioseo_update_trail(array $crumbs): array {
    $post_types = [Program::POST_TYPE];

    if (nvis_is_filtered_results($post_types)) {
        return aioseo_replace_trail($crumbs);
    }

    return $crumbs;
}

/**
 * Rebuild the entire AiOSEO trail for the current archive.
 *
 * @param array $crumbs The current trail of crumbs.
 * @return array The new trail of crumbs.
 */
function aioseo_replace_trail(array $crumbs): array {
    $crumbs = [];
    $home = aioseo()->breadcrumbs->maybeGetHomePageCrumb();

    if ($home) {
        $crumbs[] = $home;
    }

    $crumbs[] = aioseo()->breadcrumbs->getPostTypeArchiveCrumb(get_queried_object());
    $crumbs[] = [
        'label' => get_filtered_results_breadcrumb_label(), 
        'link' => ''
    ];

    return $crumbs;
}


/**
 * Updates the Yoast trail for program subpages and filtered results.
 *
 * Called on filter: rank_math/frontend/breadcrumb/items
 *
 * @param array $crumbs The current trail of crumbs.
 * @param Breadcrumbs $class The current breadcrumb object.
 * @return array The filtered trail of crumbs.
 */
function rankmath_update_trail(array $crumbs, \RankMath\Frontend\Breadcrumbs $class): array {
    $post_types = [Program::POST_TYPE];

    if (nvis_is_filtered_results($post_types)) {
        return rankmath_replace_trail($crumbs);
    }

    return $crumbs;
}

/**
 * Rebuild the entire RankMath trail for the current archive.
 *
 * @param array $crumbs The current trail of crumbs.
 * @return array The new trail of crumbs.
 */
function rankmath_replace_trail(array $crumbs): array {
    $new_crumbs = [];
    $show_home = \RankMath\Helper::get_settings('general.breadcrumbs_home');

    if ($show_home) {
        $new_crumbs[] = array_shift($crumbs);
    }

    $new_crumbs[] = array_values(get_archive_crumb());
    $new_crumbs[] = [
        get_filtered_results_breadcrumb_label(), 
        ''
    ];

    return $new_crumbs;
}
