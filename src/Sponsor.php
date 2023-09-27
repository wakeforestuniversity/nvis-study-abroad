<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Sponsor custom taxonomy.
 *
 * @package NVISStudyAbroad
 * @subpackage ContentModel
 * @since 0.1.0
 */
class Sponsor extends CustomTaxonomy
{
    public const TAXONOMY = 'nvis_sponsor';

    public $object_types = [Program::POST_TYPE];

    public array $args = [
        'query_var'             => 'spnsr',
        'description'           => '',
        'sort'                  => true,
        'rewrite'               => false,
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'show_in_nav_menus'     => false,
        'show_tagcloud'         => false,
        'meta_box_cb'           => false,
    ];

    protected function setup_labels(): void {
        $this->args['labels'] = [
            'name'                       => _x('Sponsors', 'taxonomy general name', 'nvis-study-abroad'),
            'singular_name'              => _x('Sponsor', 'taxonomy singular name', 'nvis-study-abroad'),
            'search_items'               => __('Search Sponsors', 'nvis-study-abroad'),
            'popular_items'              => __('Popular Sponsors', 'nvis-study-abroad'),
            'all_items'                  => __('All Sponsors', 'nvis-study-abroad'),
            'parent_item'                => __('Parent Sponsor', 'nvis-study-abroad'),
            'parent_item_colon'          => __('Parent Sponsor:', 'nvis-study-abroad'),
            'edit_item'                  => __('Edit Sponsor', 'nvis-study-abroad'),
            'view_item'                  => __('View Sponsor', 'nvis-study-abroad'),
            'update_item'                => __('Update Sponsor', 'nvis-study-abroad'),
            'add_new_item'               => __('Add New Sponsor', 'nvis-study-abroad'),
            'new_item_name'              => __('New Sponsor Name', 'nvis-study-abroad'),
            'separate_items_with_commas' => __('Separate sponsors with commas', 'nvis-study-abroad'),
            'add_or_remove_items'        => __('Add or remove sponsors', 'nvis-study-abroad'),
            'choose_from_most_used'      => __('Choose from the most used sponsors', 'nvis-study-abroad'),
            'not_found'                  => __('No sponsors found.', 'nvis-study-abroad'),
            'no_terms'                   => __('No sponsors', 'nvis-study-abroad'),
            'filter_by_item'             => __('Filter by sponsor', 'nvis-study-abroad'),
            'items_list_navigation'      => __('Sponsor list navigation', 'nvis-study-abroad'),
            'items_list'                 => __('Sponsor list', 'nvis-study-abroad'),
            'back_to_items'              => __('&larr; Go to Sponsors', 'nvis-study-abroad'),
            'item_link'                  => _x('Sponsor Link', 'navigation link block title', 'nvis-study-abroad'),
            'item_link_description'      => _x('A link to a sponsor.', 'navigation link block description', 'nvis-study-abroad'),
        ];
    }

    protected function setup_field_group() {
        $field_group = require_once Plugin::$path . '/src/field-group-sponsor.php';

        $this->field_groups[] = $field_group;
    }

    public static function get_program_dates($post) {
        if (!taxonomy_exists(static::TAXONOMY)) {
            return false;
        }

        $post = get_post($post);
        $sponsors = get_the_terms($post, static::TAXONOMY);

        if (!is_array($sponsors)) {
            return false;
        }

        return get_field('program_dates', $sponsors[0]);
    }

    public static function get_program_brochure_sections($post) {
        if (!taxonomy_exists(static::TAXONOMY)) {
            return false;
        }

        $post = get_post($post);
        $sponsors = get_the_terms($post, static::TAXONOMY);

        if (!is_array($sponsors)) {
            return false;
        }

        return get_field('program_brochure_sections', $sponsors[0]);
    }
}
