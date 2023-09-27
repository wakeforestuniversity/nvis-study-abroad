<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Subject custom taxonomy.
 * 
 * @package NVISStudyAbroad
 * @subpackage ContentModel
 * @since 0.1.0
 */
class Subject extends CustomTaxonomy {
    public const TAXONOMY = 'nvis_subject';

    public $object_types = [Program::POST_TYPE];

    public array $args = [
        'query_var'             => 'subj',
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
            'name'                       => _x( 'Subjects', 'taxonomy general name' , 'nvis-study-abroad'), 
            'singular_name'              => _x( 'Subject', 'taxonomy singular name', 'nvis-study-abroad' ),
            'search_items'               => __( 'Search Subjects' , 'nvis-study-abroad'), 
            'popular_items'              => __( 'Popular Subjects', 'nvis-study-abroad' ),
            'all_items'                  => __( 'All Subjects' , 'nvis-study-abroad'), 
            'parent_item'                => __( 'Parent Subject' , 'nvis-study-abroad'), 
            'parent_item_colon'          => __( 'Parent Subject:' , 'nvis-study-abroad'), 
            'edit_item'                  => __( 'Edit Subject' , 'nvis-study-abroad'), 
            'view_item'                  => __( 'View Subject' , 'nvis-study-abroad'), 
            'update_item'                => __( 'Update Subject' , 'nvis-study-abroad'), 
            'add_new_item'               => __( 'Add New Subject', 'nvis-study-abroad' ),
            'new_item_name'              => __( 'New Subject Name', 'nvis-study-abroad' ),
            'separate_items_with_commas' => __( 'Separate subjects with commas' , 'nvis-study-abroad'), 
            'add_or_remove_items'        => __( 'Add or remove subjects', 'nvis-study-abroad' ),
            'choose_from_most_used'      => __( 'Choose from the most used subjects' , 'nvis-study-abroad'), 
            'not_found'                  => __( 'No subjects found.' , 'nvis-study-abroad'), 
            'no_terms'                   => __( 'No subjects' , 'nvis-study-abroad'), 
            'filter_by_item'             => __( 'Filter by subject' , 'nvis-study-abroad'), 
            'items_list_navigation'      => __( 'Subject list navigation' , 'nvis-study-abroad'), 
            'items_list'                 => __( 'Subject list', 'nvis-study-abroad' ),
            'back_to_items'              => __( '&larr; Go to Subjects' , 'nvis-study-abroad'), 
            'item_link'                  => _x( 'Subject Link', 'navigation link block title' , 'nvis-study-abroad'), 
            'item_link_description'      => _x( 'A link to a subject.', 'navigation link block description', 'nvis-study-abroad' ),
        ];
    }
}
