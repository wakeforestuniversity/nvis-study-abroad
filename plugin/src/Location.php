<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Location custom taxonomy.
 *
 * @package NVISStudyAbroad
 * @subpackage ContentModel
 * @since 0.1.0
 */
class Location extends CustomTaxonomy {
    public const TAXONOMY = 'nvis_location';

    public $object_types = [Program::POST_TYPE];

    public array $args = [
        'query_var'             => 'loc',
        'rewrite'               => false,
        'description'           => '',
        'sort'                  => true,
        'rewrite'               => ['slug' => 'location'],
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_quick_edit'    => true,
        'show_admin_column'     => true,
        'show_in_nav_menus'     => true,
        'show_tagcloud'         => false,
        'meta_box_cb'           => false,
    ];

    public static $location_types = ['region', 'country', 'city'];

    protected function setup_labels(): void {
        $this->args['labels'] = [
            'name'                       => _x( 'Locations', 'taxonomy general name' , 'nvis-study-abroad'), 
            'singular_name'              => _x( 'Location', 'taxonomy singular name', 'nvis-study-abroad' ),
            'search_items'               => __( 'Search Locations' , 'nvis-study-abroad'), 
            'popular_items'              => __( 'Popular Locations', 'nvis-study-abroad' ),
            'all_items'                  => __( 'All Locations' , 'nvis-study-abroad'), 
            'parent_item'                => __( 'Parent Location' , 'nvis-study-abroad'), 
            'parent_item_colon'          => __( 'Parent Location:' , 'nvis-study-abroad'), 
            'edit_item'                  => __( 'Edit Location' , 'nvis-study-abroad'), 
            'view_item'                  => __( 'View Location' , 'nvis-study-abroad'), 
            'update_item'                => __( 'Update Location' , 'nvis-study-abroad'), 
            'add_new_item'               => __( 'Add New Location', 'nvis-study-abroad' ),
            'new_item_name'              => __( 'New Location Name', 'nvis-study-abroad' ),
            'separate_items_with_commas' => __( 'Separate locations with commas' , 'nvis-study-abroad'), 
            'add_or_remove_items'        => __( 'Add or remove locations', 'nvis-study-abroad' ),
            'choose_from_most_used'      => __( 'Choose from the most used locations' , 'nvis-study-abroad'), 
            'not_found'                  => __( 'No locations found.' , 'nvis-study-abroad'), 
            'no_terms'                   => __( 'No locations' , 'nvis-study-abroad'), 
            'filter_by_item'             => __( 'Filter by location' , 'nvis-study-abroad'), 
            'items_list_navigation'      => __( 'Location list navigation' , 'nvis-study-abroad'), 
            'items_list'                 => __( 'Location list', 'nvis-study-abroad' ),
            'back_to_items'              => __( '&larr; Go to Locations' , 'nvis-study-abroad'), 
            'item_link'                  => _x( 'Location Link', 'navigation link block title' , 'nvis-study-abroad'), 
            'item_link_description'      => _x( 'A link to a location.', 'navigation link block description', 'nvis-study-abroad' ),
            'none_selected'              => _x( 'Any Location', 'dropdown list none selected', 'nvis-study-abroad'),
        ];
    }

    protected function setup_field_group() {
        $field_group = require_once Plugin::$path . '/src/field-group-location.php';

        $this->field_groups[] = $field_group;
    }

    static function get_full($term) {
        $loc_type = get_term_meta($term->term_id, 'location_type', true);

        if ($loc_type === 'city' && $term->parent) {
            $country = get_term($term->parent, Location::TAXONOMY);

            if (!is_wp_error($term)) {
                return $term->name . ', ' . $country->name;
            }
        } 

        return $term->name;
    }

    static function get_by_type(string $location_type) {
        if (!in_array($location_type, static::$location_types)) {
            return new \WP_Error(
                'invalid_location_type',
                'Requested invalid location type: ' . $location_type
            );
        }

        $args = [
            'taxonomy' => static::TAXONOMY,
            'meta_key' => 'location_type',
            'meta_value' => $location_type,
            'hide_empty' => false
        ];

        $terms = get_terms($args);

        return $terms;
    }

    static function get_countries_grouped_by_region($empty = false) {
        $locations = get_terms([
            'taxonomy' => static::TAXONOMY,
            'meta_key' => 'location_type',
            'meta_value' => ['region','country'],
            'meta_compare' => 'IN',
            'hide_empty' => !$empty
        ]);

        if (is_wp_error($locations)) {
            return $locations;
        }

        $regions = []; 

        foreach ($locations as $term) {
            if (!$term->parent) {
                $term->children = [];
                $regions[$term->term_id] = $term;
            }
        }

        foreach ($locations as $term) {
            if ($term->parent) {
                $regions[$term->parent]->children[] = $term;
            }
        }

        return $regions;
    }

    static function get_coords($term) {
        $term = get_term($term);

        if (!$term || is_wp_error($term)) {
            return $term;
        }

        $coords = get_term_meta( $term->term_id, 'geo_coords', true );
        
        if (!$coords) {
            return false;
        }

        [$lat,$long] = explode(',', $coords);

        $lat = trim($lat);
        $long = trim($long);

        return compact('lat', 'long');
    }    
}
