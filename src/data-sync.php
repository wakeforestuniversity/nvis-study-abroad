<?php

namespace InvisibleUs\StudyAbroad;

add_action('nvis/studyabroad/sync_program', __NAMESPACE__ . '\sync_program');

function is_valid_terradotta_host() {
    $url = TerraDottaAPI::get_base_url();
    $key = 'nvis_sap_tdhost_' . md5($url);

    $validated = get_option($key, -1);

    if ($validated === -1) {
        $programs = TerraDottaAPI::get_programs();
        
        $validated = $programs !== false && !is_wp_error($programs);

        $success = update_option( 
            $key,
            (int) $validated,
            false
        );
    }

    return $validated;
}

function sync_fetch_program_data($td_program_id) {
    // TODO: Add `force` param to allow bypassing cache.
    $transient_key = sync_get_program_transient_key($td_program_id);
    $expiration = HOUR_IN_SECONDS;
    $data = get_transient( $transient_key );

    if ($data) {
        return $data;
    }

    $data = TerraDottaAPI::get_program_brochure($td_program_id);

    if (is_wp_error($data)) {
        $error_msg = sprintf(
            __('Data fetch failed for Terra Dotta program id: %s. See `error_data` for more info.', 'nvis-study-abroad'),
            $td_program_id
        );

        new \WP_Error(
            'nvis_sap_sync_data_fetch_failed',
            $error_msg,
            $data
        );

        return false;
    }

    $data['deadlines'] = TerraDottaAPI::get_program_deadlines($td_program_id);

    if (is_wp_error($data['deadlines'])) {
        $error_msg = sprintf(
            __('Data fetch failed for Terra Dotta program id: %s. See `error_data` for more info.', 'nvis-study-abroad'),
            $td_program_id
        );

        new \WP_Error(
            'nvis_sap_sync_data_fetch_failed',
            $error_msg,
            $data['deadlines']
        );
    }

    set_transient($transient_key, $data, $expiration);

    return $data;
}

function sync_fetch_program_parameters() {
    $transient_key = 'nvis_program_parameters_list';
    $parameters = get_transient($transient_key);

    
    if (is_array($parameters)) {
        return $parameters;
    }


    $raw_params = TerraDottaAPI::get_program_parameters();
        
    if (is_wp_error($raw_params)) {
        return $raw_params;
    }
    if (empty($raw_params)) {
        return [];
    }

    $parameters = [];

    foreach ($raw_params as $param) {
        $parameters[] = [
            'id' => str_replace('p_', '', $param['FORM_NAME']),
            'name' => $param['DISPLAY_NAME']
        ];
    }

    set_transient($transient_key, $parameters, 2 * HOUR_IN_SECONDS );

    return $parameters;
}

function sync_get_program_transient_key($td_program_id) {
    return 'nvis_td_program_raw_' . $td_program_id;
}

function sync_program(string $td_program_id, $post = null) {
    $post = get_post($post);
    $data = sync_fetch_program_data($td_program_id);
    
    if (!is_array($data) || !isset($data['PROGRAM_ID'])) {
        // TODO: Sync log.
        $error_msg = sprintf(
            __('Data fetch failed for Terra Dotta program id: %s. See `error_data` for more info.', 'nvis-study-abroad'),
            $td_program_id
        );
        return new \WP_Error(
            'nvis_sap_sync_data_fetch_failed',
            $error_msg,
            $data
        );
    }
    
    if (!$post) {
        $post = Program::get_by_meta('terra_dotta_id', $td_program_id, '=', true);
    }

    $insert = is_wp_error($post) || !$post;
    $is_active = $data['PROGRAM_ACTIVE'] ?? false;
    $is_enhanced_brochure = Plugin::get_option('sap_enhanced_brochure_enabled', false);
    $brochure_field = !empty(trim($data['PROGRAM_BROCHURE2'])) ? 'PROGRAM_BROCHURE2' : 'PROGRAM_BROCHURE';
    $params = sync_process_program_params($data);
    $params_by_id = sync_get_program_params_by_id($params);

    $meta = [
        'terra_dotta_id' => $data['PROGRAM_ID'],
        'last_sync' => current_time('Y-m-d H:i:s', false), 
        'featured' => sync_get_is_featured($data, $params_by_id),
        'program_home_page' => $data['PROGRAM_HOME_PAGE'] ?? '',
        'action_apply_now' => $data['APPLY_NOW'] ?? '',
        'action_request_info' => $data['REQUEST_ADVISING'] ?? '',
    ];

    sync_map_params($params_by_id, $meta);
    sync_map_dates($data, $meta);
    sync_map_brochure_content(
        $data[$brochure_field], 
        $meta, 
        $post,
        $is_enhanced_brochure
    );
    sync_map_acf_fields($meta, Program::get_field_group());

    $postarr = [
        'post_title' => $data['PROGRAM_NAME'],
        'post_content' => '',
        'post_type' => Program::POST_TYPE,
        'meta_input' => &$meta,
        'terms_input' => []
    ];

    $postarr = apply_filters(
        'nvis/studyabroad/sync_program_postarr', 
        $postarr, 
        $td_program_id, 
        $data
    );

    if ($insert) {
        $postarr['post_status'] = $is_active ? 
            Plugin::get_option('sap_pub_insert', 'pending') : 
            Plugin::get_option('sap_pub_inactive', 'draft');

        $post = wp_insert_post($postarr, false, false);
    } else {
        $postarr['ID'] = $post->ID;

        if (!$is_active) {
            $postarr['post_status'] = 'draft';
        }

        $post = wp_update_post($postarr, false, false);
    }

    sync_program_locations($post, $data);
    sync_program_subjects($post, $params_by_id);
    sync_program_terms($post, $data);
    sync_program_sponsor($post, $data);

    if (!empty($meta['images'])) {
        sync_maybe_set_featured_image($post, $meta['images'][0]);
    }

    return true;
}

function sync_get_is_featured(array $data, array $params_by_id) {
    $option = Plugin::get_option('sap_sync_featured');

    if ($option) {
        switch ($option) {
            case 'param':
                $param_id = (int) Plugin::get_option('sap_tdparam_id_featured');
                if ($param_id) {
                    return $params_by_id[$param_id];
                }
                break;
            case 'no':
                return 0;
            default:
                break;
        }
    } 

    return $data['ISFEATURED'];
}

function sync_program_locations($post, array $data) {
    $locations_set = $data['LOCATIONS']['LOCATION'] ?? false;

    if (!$locations_set) {
        return new \WP_Error(
            'nvis_sap_sync_missing_data',
            'Locations data could not be found.'
        );
    }

    $post = get_post($post);
    $post_term_ids = [];
    $locations = $data['LOCATIONS']['LOCATION'];
    $single = $data['LOCATIONS']['LOCATION']['PROGRAM_CITY'] ?? false;
    $errors = new \WP_Error();

    if ($single) {
        $locations = [$locations];
    } 
    
    foreach ($locations as $location) {
        // Init the parent id to zero to set us at the top of the hierarchy.
        $parent_id = 0;

        foreach (Location::$location_types as $location_type) {
            $key = 'PROGRAM_' . strtoupper($location_type);
            $meta = compact('location_type');
            $sync_gps = 
                $location_type === 'city' && 
                !empty($location['PROGRAM_LATITUDE']) &&
                !empty($location['PROGRAM_LONGITUDE']);

            if ($sync_gps) {
                $meta['geo_coords'] = sprintf(
                    '%s,%s',
                    $location['PROGRAM_LATITUDE'],
                    $location['PROGRAM_LONGITUDE']
                );
            }

            if (isset($location[$key])) {
                $parent_id = update_or_insert_term(
                    $location[ $key ], 
                    Location::TAXONOMY, 
                    [
                        'parent' => $parent_id,
                        'meta' => &$meta
                    ]
                );
            } else {
                // Something went very wrong. Bail out. 
                $errors->add(
                    'nvis_missing_key',
                    'Missing location type key: ' . $key,
                    $locations
                );
            }
        }

        // Add the final parent id (a misnomer at this point) to the terms
        // to assign this program to the _city_ location only.
        $post_term_ids[] = $parent_id;
    }

    wp_set_post_terms($post->ID, $post_term_ids, Location::TAXONOMY);
}

function sync_map_dates($data, array &$meta) {
    $dates = $data['DATES']['DATE'] ?? false;

    if (!$dates) {
        return new \WP_Error(
            'nvis_sap_sync_missing_data',
            'Dates data could not be found.'
        );
    }

    $single = $dates['APP_DEADLINE'] ?? false;

    if ($single) {
        // Wrap single date in an array to normalize.
        $dates = [$dates];
    }

    $date_count = 0;
    $date_key = 'dates';

    $key_map = [
        'term' => 'APP_TERM',
        'year' => 'APP_YEAR',
        'app_deadline_date' => 'APP_DEADLINE',
        'decision_date' => 'APP_DECISION',
        'start_date' => 'TERM_START',
        'end_date' => 'TERM_END'
    ]; 

    foreach ($dates as $date) {
        foreach ($key_map as $local => $foreign) {
            $value = $date[$foreign];

            if ($value && strpos($local, '_date') !== false) {
                $value = date('Y-m-d', strtotime($value));
            }
    
            $meta_key = implode('_', [$date_key, $date_count, $local]);
            $meta[$meta_key] = $value;
        }
        
        $date_count++;
    }

    $meta[$date_key] = $date_count;
}

function sync_map_brochure_content(string $content, array &$meta, $post, bool $is_enhanced_brochure=false) {
    if (!$content) {
        return;
    }

    if ($is_enhanced_brochure) {
        $selectors = [
            'description'        => Plugin::get_option('sap_enhanced_brochure_desc_selector'),
            'images'             => Plugin::get_option('sap_enhanced_brochure_images_selector'),
            'video'              => Plugin::get_option('sap_enhanced_brochure_video_selector'),
            'sections_container' => Plugin::get_option('sap_enhanced_brochure_container_selector'),
            'sections_title'     => Plugin::get_option('sap_enhanced_brochure_title_selector'),
            'sections_content'   => Plugin::get_option('sap_enhanced_brochure_content_selector'),
        ];
        $brochure = new BrochureContentParser($content, $selectors);
        $meta['description'] = $brochure->description;
        $meta['images'] = $brochure->images;
        $meta['video_url'] = $brochure->video_url;
        $sections = $brochure->sections;
    } else {
        $sections = [
            [
                'slug' => 'content',
                'title' => __('Program Description', 'nvis-study-abroad'),
                'content' => BrochureContentParser::normalize_brochure_content($content),
            ]
        ];
    }

    sync_map_brochure_sections($sections, $meta);
}

function sync_map_brochure_sections(array $sections, array &$meta) {
    $section_count = 0;
    $section_key = 'brochure_sections';

    foreach ($sections as $section) {
        if (empty($section['title']) || empty($section['content'])) {
            continue;
        }

        $slug_meta_key = implode('_', [$section_key, $section_count, 'slug']);
        $title_meta_key = implode('_', [$section_key, $section_count, 'title']);
        $content_meta_key = implode('_', [$section_key, $section_count, 'content']);

        $slug = rawurldecode($section['title']);
        $meta[$slug_meta_key] = sanitize_title($slug, 'section-' . ($section_count + 1), 'post');
        $meta[$title_meta_key] = $section['title'];
        $meta[$content_meta_key] = $section['content'];
        $section_count++;
    }

    $meta[$section_key] = $section_count;

    return;
}

function sync_maybe_set_featured_image($post, $image) {
    $first_featured = apply_filters(
        'nvis/studyabroad/sync_set_first_featured',
        $post && !has_post_thumbnail($post),
        $post,
        $image
    );
    
    if ($first_featured) {
        $attach_id = save_img_url_as_attachment($image, $post);

        if (!is_wp_error($attach_id)) {
            set_post_thumbnail($post, $attach_id);
        }
    }

    return;
}

function sync_map_params(array $params_by_id, array &$meta) {
    $ignore = [
        Plugin::get_option('sap_map_params_subject')
    ];

    $map_ids = [
        'video' => false,
        'contact_name' => false,
        'contact_email' => false,
        'contact_phone' => false,
    ];

    $tmp = [];

    foreach ($map_ids as $name => $id) {
        $id = Plugin::get_option('sap_map_params_' . $name);

        if ($id) {
            $ignore[] = $id;
            $tmp[ $name ] = $id;
        }
    }

    $map_ids = $tmp;
    $params_key = 'additional_params';
    $params_count = 0;

    foreach($params_by_id as $id => $params) {
        $name = array_search($id, $map_ids);
        
        if ($name !== false) {
            $meta[$name] = implode(', ', wp_list_pluck($params, 'PARAM_VALUE'));
            continue;
        }

        $viewable = $params[0]['PARAM_VIEWABLE'] ?? false;
        
        if (!in_array($id, $ignore) && $viewable) {
            $id_key = implode('_', [$params_key, $params_count, 'td_param_id']);
            $name_key = implode('_', [$params_key, $params_count, 'name']);
            $value_key = implode('_', [$params_key, $params_count, 'value']);
            $meta[$id_key] = $params[0]['PARAM_ID'] ?? '';
            $meta[$name_key] = $params[0]['PROGRAM_PARAM_TEXT'] ?? '';
            $meta[$value_key] = implode(', ', wp_list_pluck($params, 'PARAM_VALUE'));
            $params_count++;
        }

    }

    $meta[$params_key] = $params_count;

    return;
}

function sync_get_program_params_by_id($params) {
    $index = [];

    foreach ($params as $param) {
        $id = $param['PARAM_ID'] ?? false;

        if (!$id) {
            continue;
        }

        if (!isset($index[$id])) {
            $index[$id] = [];
        }

        $index[$id][] = $param;
    }

    return $index;
}

function sync_process_program_params($data) {
    $params = $data['PARAMETERS']['PARAMETER'] ?? false;

    if (!$params) {
        return new \WP_Error(
            'nvis_sap_sync_missing_data',
            'Parameters data could not be found.'
        );
    }

    if (isset($params['PROGRAM_PARAM_TEXT'])) {
        // There's only one. We need to wrap it.
        $params = [$params];
    } else {
        usort($params, function($a, $b) {
            $sort_key = 'PARAM_ORDINAL';
    
            if ($a[$sort_key] == $b[$sort_key]) {
                return 0;
            }
    
            return $a[$sort_key] < $b[$sort_key] ? -1 : 1;
        });
    }

    return $params;
}

function sync_map_acf_fields(array &$meta, array $field_group) {
    $map = [];

    if (!isset($field_group['fields']) || !is_array($field_group['fields'])) {
        return false;
    }

    foreach($field_group['fields'] as $field) {
        if (!empty($field['name'])) {
            $map[ $field['name'] ] = $field['key'];
        }

        if ($field['type'] === 'group') {
            $meta[ $field['name'] ] = '';

            foreach ($field['sub_fields'] as $subfield) {
                if (!empty($subfield['name'])) {
                    $map[ $field['name'] . '_' . $subfield['name'] ] = $subfield['key'];
                }
            }
        }

    }

    foreach($meta as $key => $value) {
        if (isset($map[$key])) {
            $meta['_' . $key] = $map[$key];
        }
    }

    return;
}

function sync_program_subjects($post, array $params_by_id) {
    $post = get_post($post);
    $subject_param_id = Plugin::get_option('sap_map_params_subject');

    if (!$subject_param_id) {
        return;
    }

    $subjects = $params_by_id[$subject_param_id] ?? false;
        
    if (!$subjects) {
        return;
    }
    
    $post_term_ids = [];

    foreach ($subjects as $subject) {
        $term_id = update_or_insert_term($subject['PARAM_VALUE'], Subject::TAXONOMY);

        if (!is_wp_error($term_id)) {
            $post_term_ids[] = $term_id;
        } else if (WP_DEBUG_LOG) {
            error_log(
                $term_id->get_error_code() . ': ' .
                $term_id->get_error_message()  
            );
        }
    }

    wp_set_post_terms($post->ID, $post_term_ids, Subject::TAXONOMY);

    return;
}

function sync_program_terms($post, array $data) {
    $post = get_post($post);
    $terms = sync_load_program_terms($data);

    if (is_wp_error($terms)) {
        return;
    }

    $post_term_ids = [];

    foreach ($terms as $term) {
        $term_id = update_or_insert_term($term, Term::TAXONOMY);

        if (!is_wp_error($term_id)) {
            $post_term_ids[] = $term_id;
        } else if (WP_DEBUG_LOG) {
            error_log(
                $term_id->get_error_code() . ': ' .
                $term_id->get_error_message()  
            );
        }
    }

    wp_set_post_terms($post->ID, $post_term_ids, Term::TAXONOMY);

    return;
}

function sync_load_program_terms(array $data) {
    $single_term = $data['TERMS']['TERM']['PROGRAM_TERM'] ?? false;

    if ($single_term) {
        // Wrap it in an array before returning it.
        return [$single_term];
    }

    $terms = $data['TERMS']['TERM'] ?? false;

    if (!is_array($terms)) {
        return new \WP_Error(
            'nvis_sap_sync_missing_data',
            'Terms data not found.',
            $data
        );
    }

    return wp_list_pluck($terms, 'PROGRAM_TERM');
}

function sync_program_sponsor($post, array $data) {
    if (empty(trim($data['SPONSOR_NAME'])) || !taxonomy_exists(Sponsor::TAXONOMY)) {
        return;
    }

    $post = get_post($post);
    $post_term_ids = [];

    $term_id = update_or_insert_term($data['SPONSOR_NAME'], Sponsor::TAXONOMY);

    if (!is_wp_error($term_id)) {
        $post_term_ids[] = $term_id;
    } else if (WP_DEBUG_LOG) {
        error_log(
            $term_id->get_error_code() . ': ' .
            $term_id->get_error_message()  
        );
    }

    wp_set_post_terms($post->ID, $post_term_ids, Sponsor::TAXONOMY);

    return;
}
