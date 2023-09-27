<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Term custom taxonomy.
 *
 * @package NVISStudyAbroad
 * @subpackage ContentModel
 * @since 0.1.0
 */
class Term extends CustomTaxonomy {
    public const TAXONOMY = 'nvis_term';
    public string $name = 'Term';
    public string $plural_name = 'Terms';

    public $object_types = [Program::POST_TYPE];

    public array $args = [
        'query_var'             => 'sess',
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
            'name'                       => _x( 'Terms', 'taxonomy general name' , 'nvis-program-pages'), 
            'singular_name'              => _x( 'Term', 'taxonomy singular name', 'nvis-program-pages' ),
            'search_items'               => __( 'Search Terms' , 'nvis-program-pages'), 
            'popular_items'              => __( 'Popular Terms', 'nvis-program-pages' ),
            'all_items'                  => __( 'All Terms' , 'nvis-program-pages'), 
            'parent_item'                => __( 'Parent Term' , 'nvis-program-pages'), 
            'parent_item_colon'          => __( 'Parent Term:' , 'nvis-program-pages'), 
            'edit_item'                  => __( 'Edit Term' , 'nvis-program-pages'), 
            'view_item'                  => __( 'View Term' , 'nvis-program-pages'), 
            'update_item'                => __( 'Update Term' , 'nvis-program-pages'), 
            'add_new_item'               => __( 'Add New Term', 'nvis-program-pages' ),
            'new_item_name'              => __( 'New Term Name', 'nvis-program-pages' ),
            'separate_items_with_commas' => __( 'Separate terms with commas' , 'nvis-program-pages'), 
            'add_or_remove_items'        => __( 'Add or remove terms', 'nvis-program-pages' ),
            'choose_from_most_used'      => __( 'Choose from the most used terms' , 'nvis-program-pages'), 
            'not_found'                  => __( 'No terms found.' , 'nvis-program-pages'), 
            'no_terms'                   => __( 'No terms' , 'nvis-program-pages'), 
            'filter_by_item'             => __( 'Filter by term' , 'nvis-program-pages'), 
            'items_list_navigation'      => __( 'Term list navigation' , 'nvis-program-pages'), 
            'items_list'                 => __( 'Term list', 'nvis-program-pages' ),
            'back_to_items'              => __( '&larr; Go to Terms' , 'nvis-program-pages'), 
            'item_link'                  => _x( 'Term Link', 'navigation link block title' , 'nvis-program-pages'), 
            'item_link_description'      => _x( 'A link to a term.', 'navigation link block description', 'nvis-program-pages' ),
        ];
    }
}
