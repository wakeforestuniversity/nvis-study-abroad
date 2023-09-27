<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Foreign Program custom post type.
 *
 * @package NVISStudyAbroad
 * @subpackage ContentModel
 * @since 0.1.0
 */
class Program extends CustomPostType {
    /**
     * The post type to register.
     */
    public const POST_TYPE = 'nvis_foreign_program';

    /**
     * The args to pass to register_post_type.
     *
     * Gets updated throughout the setup process.
     *
     * @var array
     */
    public array $args = [
        'rewrite'             => ['slug' => 'program'],
        'has_archive'         => 'programs-abroad',
        'capability_type'     => self::POST_TYPE,
        'menu_icon'           => 'dashicons-admin-site-alt',
        'menu_position'       => 5,
        'description'         => '',
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'supports'            => ['title', 'thumbnail'],
        'taxonomies'          => ['nvis_subject','nvis_location','nvis_term','nvis_sponsor']
    ];

    protected function setup_labels(): void {
        $this->args['labels'] = [
            'name'                     => _x('Programs', 'post type general name', 'nvis-study-abroad'),
            'singular_name'            => _x('Program', 'post type singular name', 'nvis-study-abroad'),
            'menu_name'                => __('Study Abroad', 'nvis-study-abroad'),
            'add_new_item'             => __('Add New Program', 'nvis-study-abroad'),
            'edit_item'                => __('Edit Program', 'nvis-study-abroad'),
            'new_item'                 => __('New Program', 'nvis-study-abroad'),
            'view_item'                => __('View Program', 'nvis-study-abroad'),
            'view_items'               => __('View Programs', 'nvis-study-abroad'),
            'search_items'             => __('Search Programs', 'nvis-study-abroad'),
            'not_found'                => __('No programs found.', 'nvis-study-abroad'),
            'not_found_in_trash'       => __('No programs found in Trash.', 'nvis-study-abroad'),
            'parent_item_colon'        => __('Parent Program:', 'nvis-study-abroad'),
            'all_items'                => __('All Programs', 'nvis-study-abroad'),
            'archives'                 => __('Programs', 'nvis-study-abroad'),
            'attributes'               => __('Program Attributes', 'nvis-study-abroad'),
            'insert_into_item'         => __('Insert into program', 'nvis-study-abroad'),
            'uploaded_to_this_item'    => __('Uploaded to this program', 'nvis-study-abroad'),
            'filter_items_list'        => __('Filter programs list', 'nvis-study-abroad'),
            'items_list_navigation'    => __('Programs list navigation', 'nvis-study-abroad'),
            'items_list'               => __('Programs list', 'nvis-study-abroad'),
            'item_published'           => __('Program published.', 'nvis-study-abroad'),
            'item_published_privately' => __('Program published privately.', 'nvis-study-abroad'),
            'item_reverted_to_draft'   => __('Program reverted to draft.', 'nvis-study-abroad'),
            'item_scheduled'           => __('Program scheduled.', 'nvis-study-abroad'),
            'item_updated'             => __('Program updated.', 'nvis-study-abroad'),
            'item_link'                => _x('Program Link', 'navigation link block title', 'nvis-study-abroad'),
            'item_link_description'    => _x('A link to a program.', 'navigation link block description', 'nvis-study-abroad'),
        ];
    }

    protected function setup_field_group() {
        $field_group = require_once Plugin::$path . '/src/field-group-program.php';

        $this->field_groups[] = $field_group;
    }


    public function setup_hooks(): void {
        $post_type = static::POST_TYPE;

        add_action('pre_get_posts', [static::class, 'update_sort_order']);
        add_action("manage_{$post_type}_posts_columns", [static::class, 'posts_columns']);
        add_action("manage_{$post_type}_posts_custom_column", [static::class, 'render_posts_columns'], 10, 2);
    }

    /**
     * Changes the sort order for Programs.
     *
     * Called on filter: pre_get_posts
     *
     * @param WP_Query $query The current WP_Query
     * @return void
     */
    public static function update_sort_order(\WP_Query $query): void {
        $is_program_content = 
            $query->is_post_type_archive(self::POST_TYPE) || 
            $query->is_tax([Location::TAXONOMY, Subject::TAXONOMY]);
        $update_order =
            $query->is_main_query() &&
            !$query->get('orderby') &&
            $is_program_content;

        if ($update_order) {
            $query->set('orderby', ['meta_value_num' => 'DESC', 'title' => 'ASC']);
            $query->set('meta_key', 'featured');
        }

        return;
    }

    public static function is_featured($post=null) {
        $post = get_post($post);

        return get_field('featured', $post);
    }

    public static function get_location_label($post=null, $empty_label='') {
        $post = get_post($post);

        $terms = get_the_terms($post, Location::TAXONOMY);

        if (!$terms || is_wp_error($terms)) {
            return $empty_label;
        }

        $num_locs = count($terms);

        if (!$num_locs) {
            return $empty_label;
        }
        
        if ($num_locs === 1) {
            return Location::get_full($terms[0]);
        }

        $same_country = true;
        $country = $terms[0]->parent;

        foreach ($terms as $term) {
            if ($term->parent !== $country) {
                $same_country = false;
                break;
            }
        }

        if ($same_country) {
            $country = get_term($country, Location::TAXONOMY);
            /* translators: The placehold is the name of the country. */
            $label = __('Multiple Cities in %s', 'nvis-study-abroad');
            return sprintf($label, $country->name);
        }

        return __('Multiple Countries', 'nvis-study-abroad');
    }

    public static function get_locations_list($post=null) {
        $terms = self::get_locations($post);

        if (!$terms) {
            return false;
        }

        $list = [];

        foreach ($terms as $term) {
            $list[] = Location::get_full($term);
        }

        return $list;
    }

    public static function get_locations($post=null) {
        $post = get_post($post);
        $terms = get_the_terms($post, Location::TAXONOMY);

        if (!$terms || is_wp_error($terms)) {
            return false;
        }

        foreach ($terms as &$term) {
            $term->full_name = Location::get_full($term);
        }

        return $terms;
    }

    public static function get_key_params($post=null): array {
        $post = get_post($post);
        $key_param_ids = get_field('nvis_sap_key_parameters_list', 'option');

        if (empty($key_param_ids) || !is_array($key_param_ids)) {
            return [];
        }

        $key_params = [];
        $key_param_ids = wp_list_pluck($key_param_ids, 'id');
        $params = get_field('additional_params', $post);

        if (empty($params) || !is_array($params)) {
            return [];
        }

        foreach($params as $param) {
            $i = array_search($param['td_param_id'], $key_param_ids);

            if ($i !== false) {
                $key_params[$i] = $param;
            }
        }

        ksort($key_params);
        
        return $key_params;
    }


    public static function get_sponsor_program_content($post=null) {
        $post = get_post($post);
        $terms = get_the_terms($post, Sponsor::TAXONOMY);

        if (!$terms || is_wp_error($terms)) {
            return '';
        }

        $sponsor = $terms[0];
        $default_content = get_field(
            'sponsor_program_content', 
            $sponsor->taxonomy .'_'.$sponsor->term_id
        );
        $sponsor_content = get_field('sponsor_program_content', $post);

        if (!empty($sponsor_content)) {
            $sponsor_content = str_replace(
                '{$default_content}',
                $default_content,
                $sponsor_content
            );
        } else {
            $sponsor_content = $default_content;
        }

        return apply_filters('nvis/studyabroad/sponsor_program_content', $sponsor_content, $post);
    }

    public static function get_dates($post=null, $format=null, $hide_past=false) {
        $post = get_post($post);
        $dates = Sponsor::get_program_dates($post);

        if (!is_array($dates)) {
            $dates = get_field('dates', $post);
        }

        if (!is_array($dates)) {
            return false;
        }

        if ($hide_past) {
            $dates = array_filter($dates, function($item) {
                $cmp_fmt = 'Y-m-d';
                $deadline = date($cmp_fmt, strtotime($item['app_deadline_date']));
                
                return $deadline >= date($cmp_fmt);
            });
        }

        return self::format_program_dates($dates, $format);
    }

    public static function format_program_dates(array $dates, $format=null) {
        $format = $format ?? get_option('date_format');

        $format = apply_filters('nvis/studyabroad/program_date_format', $format);

        $dates = array_map(
            function($date_row) use($format) {
                $date_regex = '/^[\d]{4}\-[\d]{2}\-[\d]{2}$/';

                foreach ($date_row as $key => &$val) {
                    $maybe_format = $val && 
                        (strpos($key, '_date') !== false) && 
                        preg_match($date_regex, trim($val));

                    if ($maybe_format) {
                        $val = date($format, strtotime($val));
                    }
                }

                return $date_row;
            }, 
            $dates
        );

        return $dates;
    }

    public static function get_images($post) {
        $post = get_post($post);

        $media = get_field('media_gallery', $post);

        if ($media && is_array($media)) {
            return $media;
        } 
          
        $images = get_field('images', $post);
        
        if (!is_array($images)) {
            return [];
        }

        return $images;
    }

    public static function get_brochure_sections($post=null) {
        $post = get_post($post);
        $sections = Sponsor::get_program_brochure_sections($post);

        if (!is_array($sections)) {
            $sections = get_field('brochure_sections', $post);
        }

        return $sections;
    }

    public static function get_contact_section($post) {
        $sections = get_field('brochure_sections', $post);

        if (!$sections) {
            return;
        }

        $slug = self::get_contact_section_slug($post);

        foreach ($sections as $section) {
            if ($section['slug'] === $slug) {
                return $section;
            }
        }

        return false;
    }

    public static function get_contact_section_slug($post) {
        return apply_filters('nvis/studyabroad/contact_section_slug', 'contact', $post);
    }

    public static function get_action_url(string $action, $post=null) {
        $post = get_post($post);
        $post_type = str_replace('nvis_', '', self::POST_TYPE);
        $is_td_action = in_array($action, ['request_info', 'apply_now']);
        $option = sprintf(
            '%s_action_%s_url',
            $post_type,
            $action
        );
        $url = Plugin::get_option($option, -1);

        $get_td_action = $url == -1 || ($is_td_action && $url === 'td');

        if ($get_td_action) {
            $action = get_field('action_' . $action, $post);

            $enabled = 
                is_array($action) && 
                $action['ENABLED'] !== 'NO' &&
                (
                    $action['ENABLED'] === 'YES' ||
                    wp_validate_boolean($action['ENABLED'])
                );

            if ($enabled) {
                $url = sprintf(
                    'https://%s/%s',
                    TerraDottaAPI::get_host(),
                    $action['LINK']['HREF']
                );
            }
        } else if ($url === 'custom') {
            $url = Plugin::get_option($option . '_custom');
        }

        if (!$url || in_array($url, [-1,'td'])) {
            return false;
        }

        $td_id = get_field('terra_dotta_id', $post);

        return str_replace(
            ['{$program_slug}', '{$terra_dotta_id}'],
            [$post->post_name, $td_id],
            $url
        );
    }

    public static function posts_columns($columns) {
        return [
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'terra_dotta_id' => __('Terra Dotta ID', 'nvis-study-abroad'),
            'taxonomy-nvis_location' => $columns['taxonomy-nvis_location'],
            'taxonomy-nvis_subject' => $columns['taxonomy-nvis_subject'],
            'last_sync' => __('Last Sync', 'nvis-study-abroad'),
            'date' => $columns['date']
        ];
    }

    public static function render_posts_columns($column_key, $post_id) {
        switch ($column_key) {
            case 'terra_dotta_id':
                self::render_column_terra_dotta_id($post_id);
                break;
            case 'last_sync':
                self::render_column_last_sync($post_id);
                break;
            default;
                break;
        }
    }

    public static function render_column_terra_dotta_id($post_id) {
        $td_id = get_field('terra_dotta_id', $post_id);

        echo $td_id ? 
            (int) $td_id :
            'Not set';
        
        return;
    }

    public static function render_column_last_sync($post_id) {
        $last_sync = get_field('last_sync', $post_id);

        if ($last_sync) {
            $time = strtotime($last_sync);

            echo sprintf(
                // translators: the placeholders are date and time, in WordPress option format.
                __('%1$s at %2$s', 'nvis-study-abroad'),
                date(get_option('date_format'), $time),
                date(get_option('time_format'), $time),
            );
        } else {
            echo '<em>No last sync</em>';
        }
    }
}
