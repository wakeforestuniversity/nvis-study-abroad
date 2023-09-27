<?php
/**
 * Asset management.
 *
 * @package NVISPrograms
 * @since 0.1.0
 */

namespace InvisibleUs\StudyAbroad;

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\register_assets', 0);
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets');

/**
 * Registers frontend assets.
 *
 * @return void
 */
function register_assets() {
    $global = '/assets/css/global.min.css';
    wp_register_style(
        'nvis-global',
        Plugin::$url . $global,
        [],
        filemtime(Plugin::$path . $global)
    );

    $base = '/assets/css/base.min.css';
    wp_register_style(
        'nvis-abroad-base',
        Plugin::$url . $base,
        ['nvis-global'],
        filemtime(Plugin::$path . $base)
    );

    $global_full = '/assets/css/global-full.min.css';
    wp_register_style(
        'nvis-global-full',
        Plugin::$url . $global_full,
        ['nvis-global'],
        filemtime(Plugin::$path . $global_full)
    );

    $full = '/assets/css/full.min.css';
    wp_register_style(
        'nvis-abroad-full',
        Plugin::$url . $full,
        ['nvis-global', 'nvis-abroad-base'],
        filemtime(Plugin::$path . $full)
    );

    $terms_grid = '/assets/css/terms-grid.min.css';
    wp_register_style(
        'nvis-terms-grid',
        Plugin::$url . $terms_grid,
        ['nvis-global'],
        filemtime(Plugin::$path . $terms_grid)
    );

    $global = '/assets/js/global.min.js';
    wp_register_script(
        'nvis-global',
        Plugin::$url . $global,
        [],
        filemtime(Plugin::$path . $global),
        true
    );

    $tabby = Plugin::$url . '/assets/vendor/tabby/js/tabby.polyfills.min.js';
    wp_register_script('tabby-js', $tabby, [], '12.0.3', true);

    $pswp_lb = Plugin::$url . '/assets/vendor/photoswipe/umd/photoswipe-lightbox.umd.min.js';
    wp_register_script('photoswipe-lb', $pswp_lb, [], '5.3.6', true);

    $pswp = Plugin::$url . '/assets/vendor/photoswipe/umd/photoswipe.umd.min.js';
    wp_register_script('photoswipe', $pswp, ['photoswipe-lb'], '5.3.6', true);

    $pswp_css = Plugin::$url . '/assets/vendor/photoswipe/photoswipe.css';
    wp_register_style('photoswipe', $pswp_css, [], '5.3.6');
}

/**
 * Enqueues frontend assets.
 *
 * @return void
 */
function enqueue_assets() {
    $present_mode = Plugin::get_option('presentation_mode', 'base');

    if (is_admin()) {
        return;
    }

    // Looking for any page/post that could contain the terms grid shortcode.
    if (is_singular() && $present_mode !== 'none') {
        $post = get_post();

        if (strpos($post->post_content, '[nvis_locations ')) {
            wp_enqueue_style('nvis-terms-grid');
        }
    }

    $is_plugin_content = 
        is_singular(Plugin::post_types()) || 
        is_post_type_archive(Plugin::post_types()) ||
        is_tax(Plugin::taxonomies());

    if (!$is_plugin_content) {
        return;
    }

    wp_enqueue_script('nvis-global');

    if ($present_mode !== 'none') {
        wp_enqueue_style('nvis-global');
        wp_enqueue_style('nvis-abroad-base');
    }

    if ($present_mode === 'full') {
        wp_enqueue_style('nvis-global-full');
        wp_enqueue_style('nvis-abroad-full');
    }
    
    if (is_singular(Program::POST_TYPE)) {
        // Unfortunately, we have to load this stylesheet before we know if we
        // need it. 
        wp_enqueue_style('photoswipe');
    }

}
