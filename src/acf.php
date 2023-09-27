<?php
/**
 * ACF related functionality.
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */

namespace InvisibleUs\StudyAbroad;

add_action('plugins_loaded', __NAMESPACE__ . '\maybe_load_acf', 1);
add_action('acf/init', __NAMESPACE__ . '\acf_init');
add_filter('acf/load_field/name=nvis_image_size_header', __NAMESPACE__ . '\choices_image_size_header');
add_filter('acf/prepare_field/name=last_sync', __NAMESPACE__ . '\prepare_last_sync');
add_filter('acf/fields/taxonomy/result/name=locations', __NAMESPACE__ . '\taxonomy_result_locations', 10, 2);
add_filter('acf/load_field/key=field_6471121d3a983', __NAMESPACE__ . '\choices_post_stati');
add_filter('acf/load_field/key=field_647112773a984', __NAMESPACE__ . '\choices_post_stati');
add_filter('acf/load_field/type=select', __NAMESPACE__ . '\choices_td_parameter_field');
add_filter('acf/prepare_field/key=field_646cac02c9be7', __NAMESPACE__ . '\load_sync_all_button');
add_filter('acf/prepare_field/key=field_63f3c0a447045', __NAMESPACE__ . '\load_program_sync_button');
add_filter('acf/prepare_field/key=field_641dee3257054', __NAMESPACE__ . '\maybe_render_gallery_field');
add_filter('acf/prepare_field/key=field_643ff571ba749', __NAMESPACE__ . '\maybe_render_sponsor_field');
add_filter('acf/prepare_field/key=field_63f37e5fe7ae9', __NAMESPACE__ . '\prepare_tdhost_field');
add_filter('acf/validate_value/key=field_63f37e5fe7ae9', __NAMESPACE__ . '\validate_tdhost_value', 10, 4);
add_filter('acf/update_value/key=field_63ef8b5e50794', __NAMESPACE__ . '\sanitize_brochure_section_slug', 10);
add_action('admin_head', __NAMESPACE__ . '\acf_admin_head');
add_action('acf/save_post', __NAMESPACE__ . '\save_options', 20);

/**
 * Loads bundled ACF Pro if ACF not already loaded.
 *
 * @return void
 */
function maybe_load_acf(): void {
    if (class_exists('ACF')) {
        // TODO: Add minimum version number handling.
        // TODO: Figure out how to deal with Free/Pro discrepancy.
        return;
    }

    $subpath = '/src/acf/';
    define('NVISC_ACF_PATH', Plugin::$path . $subpath);
    define('NVISC_ACF_URL', Plugin::$url . $subpath);

    include_once(NVISC_ACF_PATH . 'acf.php');

    add_filter('acf/settings/url', __NAMESPACE__ . '\acf_settings_url');
    add_filter('acf/settings/show_admin', '__return_false');

    return;
}

/**
 * Returns the ACF settings URL override.
 *
 * @param string $url
 * @return void
 */
function acf_settings_url(string $url) {
    return NVISC_ACF_URL;
}

/**
 * Loads all the ACF Field Groups configured throughout the plugin.
 *
 * @return void
 */
function acf_init(): void {
    acf_add_options_page(Plugin::$options_page);

    $field_groups = [
        Plugin::get_field_group(),
        Program::get_field_group(),
        Location::get_field_group(),
        Sponsor::get_field_group(),
    ];

    foreach ($field_groups as $group) {
        acf_add_local_field_group($group);
    }

    return;
}

function prepare_last_sync($field) {
    if (!$field['value']) {
        return false;
    }

    $field['readonly'] = 1;
    $field['disabled'] = 1;

    return $field;
}

function taxonomy_result_locations($text, $term) {
    $prefix = '- - ';

    if (strpos($text, $prefix) === 0) {
        $country = get_term($term->parent);

        if ($country && !is_wp_error($country)) {
            return sprintf(
                '%s%s, %s',
                $prefix,
                $term->name,
                $country->name
            );
        }
    }
    
    return $text;
}

function choices_post_stati($field) {
    $field['choices'] = get_post_stati();

    return $field;
}


function choices_td_parameter_field($field) {
    if (!is_admin()) {
        return $field;
    }

    static $param_choices = null;
    $classes = explode(' ', $field['wrapper']['class']);

    if (!in_array('td-parameter-field', $classes)) {
        return $field;
    }

    if (!is_array($param_choices)) {
        require_once Plugin::$path . '/src/data-sync.php';
        
        $parameters = sync_fetch_program_parameters();
    
        if (is_array($parameters)) {
             $param_choices = array_combine(
                wp_list_pluck($parameters, 'id'),
                wp_list_pluck($parameters, 'name')
            );
    
            $param_choices['0'] = 'Select a Parameter';
            ksort($param_choices);
        }
    }


    $field['choices'] = $param_choices;

    return $field;
}

function load_sync_all_button($field) {
    $valid_host = is_valid_terradotta_host();
    $sync_in_progress = SyncActionManager::sync_in_progress();
    $disabled = !$valid_host || $sync_in_progress;
    $sync_url = add_query_arg([
        'sync' => wp_create_nonce('nvis_sync_programs'), 
        'message' => 0
    ]);
    
    $field['message'] = sprintf(
        '<a class="button button-primary %s" href="%s">%s</a>',
        $disabled ? 'disabled' : '',
        $disabled ? '#' : esc_url($sync_url),
        __('Sync Now', 'nvis-study-abroad')
    );

    if ($sync_in_progress) {
        $cancel_url = add_query_arg(['cancel' =>1], $sync_url);
        $message = '<span class="dashicons dashicons-warning"></span> ';
        $message .= sprintf(
            __('There is a sync in progress. Go to Tools > <a href="%s">Scheduled Actions</a> for more info.', 'nvis-study-abroad'),
            esc_url(admin_url( '/tools.php?page=action-scheduler&s=nvis/studyabroad/sync_program'))
        );
        $message .= sprintf(
            ' <a href="%s" class="button button-secondary button-small">%s</a> ',
            $cancel_url,
            __('Cancel Sync', 'nvis-study-abroad')
        );
            
        $field['message'] .= sprintf(
            '<div class="nvis-notice notice-info"><p>%s</p></div>',
            $message
        );
    }

    return $field;
}

function load_program_sync_button($field) {
    global $post_id;

    $td_id = get_field('terra_dotta_id', $post_id);

    if (!$td_id) {
        return false;
    }

    $url = sprintf(
        'post.php?action=edit&post=%s&sync=%s',
        $post_id,
        wp_create_nonce('nvis_sync_program_' . $post_id)
    );

    $field['message'] = sprintf(
        '<a class="button button-primary" href="%s">%s</a>',
        esc_url(admin_url($url)),
        __('Sync Now', 'nvis-study-abroad')
    );

    return $field;
}


/**
 * Adds the list of registered image sizes to the choices.
 *
 * Called on filter: `acf/load_field/name=nvis_image_size_header`
 *
 * @param array $field The ACF field config.
 * @return array The filtered field config.
 */
function choices_image_size_header(array $field): array {
    $sizes = wp_get_registered_image_subsizes();
    $labels = [];

    foreach ($sizes as $key => $props) {
        $labels[] = sprintf(
            '%s (%s &times; %s)',
            $key,
            $props['width'],
            $props['height']
        );
    }

    $field['choices'] = array_combine(
        array_keys($sizes),
        $labels
    );

    $field['choices']['custom'] = __('Custom', 'nvis-program-pages');

    return $field;
}

function maybe_render_gallery_field($field) {
    $local_gallery = Plugin::get_option('foreign_program_galleries', -1);

    if ($local_gallery !== -1) {
        if (!$local_gallery) {
            return false;
        }
    }

    return $field;
}

function maybe_render_sponsor_field($field) {
    $sponsors = Plugin::get_option('sponsor_enable', -1);

    if ($sponsors !== -1) {
        if (!$sponsors) {
            return false;
        }
    }

    return $field;
}

function prepare_tdhost_field($field) {
    $override_msg = '';

    if (defined('NVIS_SAP_TDHOST') && NVIS_SAP_TDHOST) {
        $field['value'] = NVIS_SAP_TDHOST;
        $field['readonly'] = true;
        $field['disabled'] = true;
        $override_msg = __('(This field is currently set by the constant <code>NVIS_SAP_TDHOST</code>)', 'nvis-study-abroad');
    }

    if ($field['value']) {
        $valid = is_valid_terradotta_host();

        if ($valid || $override_msg) {
            $valid_msg = $valid ?
                '<span class="dashicons dashicons-yes nvis-icon-success"></span> Host is valid.' :
                '<span class="dashicons dashicons-no nvis-icon-error"></span> Host is <b>not</b> valid. Please double-check it.' ;
    
            $field['instructions'] = $valid_msg . ' ' . $override_msg;
        }
    
    }

    return $field;
}

function validate_tdhost_value($valid, $value, $field, $input_name) {
    if (!$value) {
        return true;
    }

    if (!in_array($valid, [true, 1])) {
        return $valid;
    }
    
    if (!defined('NVIS_SAP_TDHOST')) {
        define('NVIS_SAP_TDHOST', $value);
    }

    $valid = is_valid_terradotta_host();
    
    if (!$valid) {
        return __('<span class="dashicons dashicons-no nvis-icon-error"></span> Host is <b>not</b> valid. Please double-check it.');
    }

    return (bool) $valid;
}

function sanitize_brochure_section_slug($value) {
    $value = urldecode($value);
    $value = sanitize_title($value, uniqid('section-'), 'post');

    return $value;
}

function acf_admin_head() {
    echo "
        <style>
        .acf-field .acf-label label {
            max-width: max-content;
        }
        .nvis-nested-group .acf-fields.-top {
            border: none;
            margin: -15px -11px;
        }
        .nvis-icon-success {
            color: #00a32a;
        }
        .nvis-icon-error {
            color: #d63638;
        }
        </style>
    ";
}
/**
 * Creates the `nvis_flush_rules` transient when saving the plugin settings page.
 *
 * Called on action: `acf/save_post`
 *
 * @param int|string $post_id The id of the post being saved, or 'options'.
 * @return void
 */
function save_options($post_id) {
    if ($post_id !== 'options' || $_GET['page'] !== Plugin::$options_page_slug) {
        // Not our plugins options page. 
        return;
    }

    maybe_update_sync_schedule(); 

    return;
}

function maybe_update_sync_schedule() {
    $sched_hook = 'nvis/studyabroad/sync_schedule';
    $sched_group = 'nvis-study-abroad';
    $prev_sched_key = 'nvis_sap_current_sync_schedule';
    $prev_sched = get_option($prev_sched_key);
    $schedule = get_field('nvis_sap_sync_schedule', 'option');

    if ($prev_sched === 'manual' && $schedule === 'manual') {
        // No schedule to delete. Nothing to schedule.
        return;
    }

    $defaults = [
        'time' => '00:01:00',
        'weekday' => 6,
    ];
    $time = Plugin::get_option('sap_sync_time');
    $time = $time ?: $defaults['time'];
    $weekday = Plugin::get_option('sap_sync_weekday');
    $weekday = $weekday ?: $defaults['weekday'];
    
    $curr_sched = $schedule;

    if ($curr_sched !== 'manual') {
        [$hour, $min] = explode(':', $time);
        $curr_sched = [
            $min,
            $hour,
            '*',
            '*',
            $schedule === 'weekly' ? $weekday : '*' 
        ];

        $curr_sched = implode(' ', $curr_sched);
    }

    $is_new_sched = $prev_sched !== $curr_sched;

    if ($prev_sched) {
        if ($prev_sched !== 'manual' && $is_new_sched) {
            as_unschedule_all_actions($sched_hook, [], $sched_group);
        }
    } else {
        update_option($prev_sched_key, $curr_sched, false);
    } 

    if ($is_new_sched && $schedule !== 'manual') {
        $next = schedule_next_timestamp($schedule, $weekday, $time);
        as_schedule_cron_action($next, $curr_sched, $sched_hook, [], $sched_group);
    }
}

function schedule_next_timestamp($schedule, $weekday, $time) {
    $next = strtotime($time);

    if ($schedule === 'daily' && $next < time()) {
        $next += DAY_IN_SECONDS;
    } else if ($schedule === 'weekly') {
        $today = (int) date('w');

        if ($weekday === $today && $next < time()) {
            $next += WEEK_IN_SECONDS;
        } else {
            $adjust = $weekday < $today ? 7 : 0;
            $next += ($weekday + $adjust - $today) * DAY_IN_SECONDS;
        }
    }

    return $next;
}
