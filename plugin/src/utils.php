<?php

namespace InvisibleUs\StudyAbroad;

function update_or_insert_term(string $name, string $taxonomy, array $args = []) {
    $parent = $args['parent'] ?? null;
    $term = term_exists($name, $taxonomy, $parent);

    if (!$term) {
        $term = wp_insert_term($name, $taxonomy, $args);
    
        if (is_wp_error($term)) {
            return $term;
        } 

        $term_id = (int) $term['term_id'];
    }

    $term_id = (int) $term['term_id'];
    $meta = $args['meta'] ?? false;

    if (is_array($meta) && !empty($meta)) {
        foreach ($meta as $key => $value) {
            update_term_meta($term_id, $key, $value);
        }
    }

    return $term_id;
}

function strip_wrapper_tag(string $html, array $tag_names = []) {
    $html = trim($html);

    $do_strip = empty($tag_names);

    if (!$do_strip) {
        foreach ($tag_names as $tag) {
            $tag_open = sprintf('<%s ', $tag);

            if (strpos($html, $tag_open) === 0) {
                $do_strip = true;
                break;
            }
        }
    }

    if ($do_strip) {
        $html = substr(
            $html, 
            strpos($html, '>') + 1,
            -1 * (strlen($html) - strrpos($html, '<'))
        ); 
    }

    return $html;
}

function save_img_url_as_attachment(array $img, $post) {
    $post = get_post($post);

    if (!$post) {
        return new \WP_Error(
            'nvis_sap_sync_post_not_found',
            __('The provided post is invalid.'),
            compact('post')
        );
    }

    $attach_id = get_attachment_by_guid($img['url']);

    if ($attach_id) {
        return $attach_id;
    }
    
    $slug = sanitize_title( $post->post_title, 'post', 'post');
    $file = download_image($img['url'], $slug);

    if (is_wp_error($file)) {
        return $file;   
    }

    $title = $img['alt'] ? 
        $img['alt'] :
        sprintf( 
            /* translators: The placeholder is the name of the location. Could be a region, country, or city.*/
            __('Downloaded Image for %s', 'nvis-study-abroad'),
            $slug
        );

    $args = [
        'guid' => $img['url'],
        'post_mime_type' => $file['mime_type'],
        'post_title' => $title
    ];

    $id = wp_insert_attachment($args, $file['filepath'], $post->ID, true, false);

    if (is_wp_error($id)) {
        return $id;
    }

    if (!function_exists('wp_generate_attachment_metadata')) {
        include(ABSPATH . 'wp-admin/includes/image.php');
    }

    // The image sub-sizes are created during wp_generate_attachment_metadata().
    // This is generally slow and may cause timeouts or out of memory errors.
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file['filepath']));

    if ($img['alt']) {
        update_post_meta($id, '_wp_attachment_image_alt', $img['alt']);
    }

    return $id;
}

function download_image(?string $url, $filename = 'image') {
    $url = filter_var($url, FILTER_VALIDATE_URL);

    if (!$url) {
        return new \WP_Error(
            'nvis_sap_sync_download_image_failed',
            __('Image download failed. URL is invalid.', 'nvis-study-abroad'),
            compact('url')
        );
    }

    $response = wp_remote_get($url);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return new \WP_Error(
            'nvis_sap_sync_download_image_failed',
            __('Image download failed. See `error_data` for full response.', 'nvis-study-abroad'),
            $response
        );
    }

    $mime_type = wp_remote_retrieve_header($response, 'Content-Type');
    if (strpos($mime_type, ';') !== false) {
        $mime_type = strstr($mime_type, ';', true);
    }
    $img_content = wp_remote_retrieve_body($response);
    $extension = wp_get_default_extension_for_mime_type($mime_type);
    
    if (!$img_content || strpos($mime_type, 'image/') !== 0) {
        return new \WP_Error(
            'nvis_sap_sync_download_image_failed',
            __('Image download failed. Response did not provide image content. See `error_data` for full response.', 'nvis-study-abroad'),
            $response
        );
    }

    $upload_dir = wp_upload_dir();
    $filename = wp_unique_filename($upload_dir['path'], $filename . '.' . $extension);
    $filepath = $upload_dir['path'] . '/' . $filename;

    $bytes = file_put_contents($filepath, $img_content);

    if (!$bytes) {
        return new \WP_Error(
            'nvis_sap_sync_filewrite_error',
            _('Could not write image file.'),
            compact('filename')
        );
    }

    return compact('filepath', 'mime_type');
}

function get_attachment_by_guid($guid_url) {
    global $wpdb;

    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid=%s";
    $attach_id = $wpdb->get_col($wpdb->prepare($query, $guid_url)); 

    if (is_array($attach_id) && !empty($attach_id)) {
        return $attach_id[0]; 
    } 

    return false;
}

function get_current_query_post_ids() {
    static $posts = false; 
    
    if ($posts !== false) {
        return $posts;    
    }
    
    global $wp_query;

    if ($wp_query->found_posts > $wp_query->var('posts_per_page')) {
        $args = $wp_query->query_vars;
        $args['nopaging'] = true;
        $args['fields'] = 'ids';

        $posts = get_posts($args);
    } else {
        $posts = wp_list_pluck($wp_query->posts, 'ID');
    }

    return $posts;
}

function get_current_posts_limited_terms(string $taxonomy) {
    $post_ids = \InvisibleUs\StudyAbroad\get_current_query_post_ids();

    if ($taxonomy === 'nvis_location') {
        $cities = wp_get_object_terms(
            $post_ids, 
            $taxonomy
        );
        $terms = wp_list_pluck($cities, 'parent');
    } else {
        $terms = wp_get_object_terms(
            $post_ids, 
            $taxonomy, 
            ['fields' => 'ids', 'orderby' => 'id']
        );
    }

    return $terms;
}
