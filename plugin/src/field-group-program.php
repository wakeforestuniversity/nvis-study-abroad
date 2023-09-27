<?php

return [
    'key'    => 'group_63caf7a2c047c',
    'title'  => __('Study Abroad Program Info', 'nvis-study-abroad'),
    'location' => [
        [
            [
                'param'    => 'post_type',
                'operator' => '==',
                'value'    => 'nvis_foreign_program',
            ],
        ],
    ],
    'menu_order'            => 0,
    'position'              => 'acf_after_title',
    'style'                 => 'seamless',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen'        => '',
    'active'                => true,
    'description'           => '',
    'show_in_rest'          => 0,
    'fields' => [
        [
            'key'               => 'field_63caf7a340113',
            'label'             => __('Terra Dotta ID', 'nvis-study-abroad'),
            'name'              => 'terra_dotta_id',
            'aria-label'        => '',
            'type'              => 'text',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '20',
                'class' => '',
                'id'    => '',
            ],
            'default_value' => '',
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ],
        [
            'key'               => 'field_63f3e5798b440',
            'label'             => __('Last Sync', 'nvis-study-abroad'),
            'name'              => 'last_sync',
            'aria-label'        => '',
            'type'              => 'date_time_picker',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '33',
                'class' => '',
                'id'    => '',
            ],
            'display_format' => 'F j, Y g:i a',
            'return_format'  => 'm/d/Y g:i a',
            'first_day'      => 1,
        ],
        [
            'key'               => 'field_63f3c0a447045',
            'label'             => __('Force Sync', 'nvis-study-abroad'),
            'name'              => 'sync_button',
            'aria-label'        => '',
            'type'              => 'message',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '33',
                'class' => '',
                'id'    => '',
            ],
            'message'   => '',
            'new_lines' => '',
            'esc_html'  => 0,
        ],
        [
            'key'               => 'field_63d7e9a189367',
            'label'             => __('Featured', 'nvis-study-abroad'),
            'name'              => 'featured',
            'aria-label'        => '',
            'type'              => 'true_false',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'message'       => '',
            'default_value' => 0,
            'ui_on_text'    => '',
            'ui_off_text'   => '',
            'ui'            => 1,
        ],
        [
            'key'               => 'field_641dee3257054',
            'label'             => __('Media Gallery', 'nvis-study-abroad'),
            'name'              => 'media_gallery',
            'aria-label'        => '',
            'type'              => 'gallery',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'return_format' => 'array',
            'library'       => 'all',
            'min'           => '',
            'max'           => '',
            'min_width'     => '',
            'min_height'    => '',
            'min_size'      => '',
            'max_width'     => '',
            'max_height'    => '',
            'max_size'      => '',
            'mime_types'    => '',
            'insert'        => 'append',
            'preview_size'  => 'medium',
        ],
        [
            'key' => 'field_643ff571ba749',
            'label' => 'Sponsor Content',
            'name' => 'sponsor_program_content',
            'aria-label' => '',
            'type' => 'wysiwyg',
            'instructions' => __('Override the default sponsor content. If you would like to include the default content, add <code>{$default_content}</code> in the box below.', 'nvis-study-abroad'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 1,
            'delay' => 0,
        ],
        [
            'key'               => 'field_63d93e698da3b',
            'label'             => __('Locations', 'nvis-study-abroad'),
            'name'              => 'locations',
            'aria-label'        => '',
            'type'              => 'taxonomy',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'taxonomy'      => 'nvis_location',
            'add_term'      => 0,
            'save_terms'    => 1,
            'load_terms'    => 1,
            'return_format' => 'id',
            'field_type'    => 'multi_select',
            'allow_null'    => 0,
            'multiple'      => 0,
        ],
        [
            'key'               => 'field_63d9635c0aebe',
            'label'             => __('Terms', 'nvis-study-abroad'),
            'name'              => 'terms',
            'aria-label'        => '',
            'type'              => 'taxonomy',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'taxonomy'      => 'nvis_term',
            'add_term'      => 0,
            'save_terms'    => 1,
            'load_terms'    => 1,
            'return_format' => 'id',
            'field_type'    => 'checkbox',
            'multiple'      => 0,
            'allow_null'    => 0,
        ],
        [
            'key'               => 'field_63d925ef8d962',
            'label'             => __('Dates', 'nvis-study-abroad'),
            'name'              => 'dates',
            'aria-label'        => '',
            'type'              => 'repeater',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'layout'        => 'table',
            'pagination'    => 0,
            'min'           => 0,
            'max'           => 0,
            'collapsed'     => '',
            'button_label'  => 'Add Date',
            'rows_per_page' => 20,
            'sub_fields'    => [
                [
                    'key'               => 'field_63d926048d963',
                    'label'             => __('Term', 'nvis-study-abroad'),
                    'name'              => 'term',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
                [
                    'key'               => 'field_63d926388d964',
                    'label'             => __('Year', 'nvis-study-abroad'),
                    'name'              => 'year',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
                [
                    'key'               => 'field_63d926418d965',
                    'label'             => __('Application Deadline', 'nvis-study-abroad'),
                    'name'              => 'app_deadline_date',
                    'aria-label'        => '',
                    'type'              => 'date_picker',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'display_format'  => 'F j, Y',
                    'return_format'   => 'Y-m-d',
                    'first_day'       => 1,
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
                [
                    'key'               => 'field_63d9267e8d966',
                    'label'             => __('Decision Date', 'nvis-study-abroad'),
                    'name'              => 'decision_date',
                    'aria-label'        => '',
                    'type'              => 'date_picker',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'display_format'  => 'F j, Y',
                    'return_format'   => 'Y-m-d',
                    'first_day'       => 1,
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
                [
                    'key'               => 'field_63d927584d7f9',
                    'label'             => __('Start Date', 'nvis-study-abroad'),
                    'name'              => 'start_date',
                    'aria-label'        => '',
                    'type'              => 'date_picker',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'display_format'  => 'F j, Y',
                    'return_format'   => 'Y-m-d',
                    'first_day'       => 1,
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
                [
                    'key'               => 'field_63d926958d967',
                    'label'             => __('End Date', 'nvis-study-abroad'),
                    'name'              => 'end_date',
                    'aria-label'        => '',
                    'type'              => 'date_picker',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'display_format'  => 'F j, Y',
                    'return_format'   => 'Y-m-d',
                    'first_day'       => 1,
                    'parent_repeater' => 'field_63d925ef8d962',
                ],
            ],
        ],
        [
            'key'               => 'field_63d93dd1aa85a',
            'label'             => __('Subjects', 'nvis-study-abroad'),
            'name'              => 'subjects',
            'aria-label'        => '',
            'type'              => 'taxonomy',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'taxonomy'      => 'nvis_subject',
            'add_term'      => 0,
            'save_terms'    => 1,
            'load_terms'    => 1,
            'return_format' => 'id',
            'field_type'    => 'multi_select',
            'allow_null'    => 0,
            'multiple'      => 0,
        ],
        [
            'key'               => 'field_642739ca77177',
            'label'             => __('Main Contact', 'nvis-study-abroad'),
            'name'              => 'contact',
            'aria-label'        => '',
            'type'              => 'group',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'layout'     => 'block',
            'sub_fields' => [
                [
                    'key'               => 'field_642739e177178',
                    'label'             => __('Full Name', 'nvis-study-abroad'),
                    'name'              => 'name',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value' => '',
                    'maxlength'     => '',
                    'placeholder'   => '',
                    'prepend'       => '',
                    'append'        => '',
                ],
                [
                    'key'               => 'field_642739ec77179',
                    'label'             => __('Email Address', 'nvis-study-abroad'),
                    'name'              => 'email',
                    'aria-label'        => '',
                    'type'              => 'email',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value' => '',
                    'placeholder'   => '',
                    'prepend'       => '',
                    'append'        => '',
                ],
                [
                    'key'               => 'field_64273a037717a',
                    'label'             => __('Phone Number', 'nvis-study-abroad'),
                    'name'              => 'phone',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value' => '',
                    'maxlength'     => '',
                    'placeholder'   => '',
                    'prepend'       => '',
                    'append'        => '',
                ],
            ],
        ],
        [
            'key'               => 'field_63f3e859dddec',
            'label'             => __('Sponsor', 'nvis-study-abroad'),
            'name'              => 'sponsor',
            'aria-label'        => '',
            'type'              => 'taxonomy',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'taxonomy'      => 'nvis_sponsor',
            'add_term'      => 0,
            'save_terms'    => 0,
            'load_terms'    => 1,
            'return_format' => 'id',
            'field_type'    => 'select',
            'allow_null'    => 1,
            'multiple'      => 0,
        ],
        [
            'key'               => 'field_63d7eb6d75ba0',
            'label'             => __('Program Homepage', 'nvis-study-abroad'),
            'name'              => 'program_home_page',
            'aria-label'        => '',
            'type'              => 'url',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'default_value' => '',
            'placeholder'   => '',
        ],
        [
            'key'               => 'field_63fcb5725e38c',
            'label'             => __('Video Url', 'nvis-study-abroad'),
            'name'              => 'video_url',
            'aria-label'        => '',
            'type'              => 'url',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'default_value' => '',
            'placeholder'   => '',
        ],
        [
            'key'               => 'field_63efa49378b04',
            'label'             => __('Description', 'nvis-study-abroad'),
            'name'              => 'description',
            'aria-label'        => '',
            'type'              => 'wysiwyg',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'default_value' => '',
            'tabs'          => 'all',
            'toolbar'       => 'basic',
            'media_upload'  => 0,
            'delay'         => 1,
        ],
        [
            'key'               => 'field_63f3951fb8110',
            'label'             => __('Additional Parameters', 'nvis-study-abroad'),
            'name'              => 'additional_params',
            'aria-label'        => '',
            'type'              => 'repeater',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'layout'        => 'table',
            'pagination'    => 0,
            'min'           => 0,
            'max'           => 0,
            'collapsed'     => '',
            'button_label'  => 'Add Parameter',
            'rows_per_page' => 20,
            'sub_fields'    => [
                [
                    'key'               => 'field_644026ee8f68d',
                    'label'             => 'Terra Dotta ID',
                    'name'              => 'td_param_id',
                    'aria-label'        => '',
                    'type'              => 'number',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '15',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'min'             => '',
                    'max'             => '',
                    'placeholder'     => '',
                    'step'            => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63f3951fb8110',
                ],                        
                [
                    'key'               => 'field_63f3953ab8111',
                    'label'             => __('Parameter Name', 'nvis-study-abroad'),
                    'name'              => 'name',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63f3951fb8110',
                ],
                [
                    'key'               => 'field_63f3958db8112',
                    'label'             => __('Parameter Value', 'nvis-study-abroad'),
                    'name'              => 'value',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63f3951fb8110',
                ],
            ],
        ],
        [
            'key'               => 'field_63d82b2386c92',
            'label'             => __('Brochure Sections', 'nvis-study-abroad'),
            'name'              => 'brochure_sections',
            'aria-label'        => '',
            'type'              => 'repeater',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => [
                'width' => '',
                'class' => '',
                'id'    => '',
            ],
            'layout'        => 'block',
            'pagination'    => 0,
            'min'           => 0,
            'max'           => 0,
            'collapsed'     => 'field_63d82b6186c93',
            'button_label'  => 'Add Section',
            'rows_per_page' => 20,
            'sub_fields'    => [
                [
                    'key'               => 'field_63d82b6186c93',
                    'label'             => __('Title', 'nvis-study-abroad'),
                    'name'              => 'title',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63d82b2386c92',
                ],
                [
                    'key'               => 'field_63ef8b5e50794',
                    'label'             => __('Slug', 'nvis-study-abroad'),
                    'name'              => 'slug',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'maxlength'       => '',
                    'placeholder'     => '',
                    'prepend'         => '',
                    'append'          => '',
                    'parent_repeater' => 'field_63d82b2386c92',
                ],
                [
                    'key'               => 'field_63cef7dec23fb',
                    'label'             => __('Content', 'nvis-study-abroad'),
                    'name'              => 'content',
                    'aria-label'        => '',
                    'type'              => 'wysiwyg',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'default_value'   => '',
                    'tabs'            => 'all',
                    'toolbar'         => 'full',
                    'media_upload'    => 1,
                    'delay'           => 1,
                    'parent_repeater' => 'field_63d82b2386c92',
                ],
            ],
        ],
    ],
];
