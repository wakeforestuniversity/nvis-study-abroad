<?php
/**
 * The template for displaying Program action buttons.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);
$sr_text = sprintf('(%s)', get_the_title($post)); 

$defaults = [
    'context'         => $template,
    'show_icons'      => true,
    'add_permalink'   => false,
    'label_permalink' => nvis_get_post_type_label('nvis_foreign_program', 'view_item'),
    'actions'         => [
        [
            'label'   => nvis_sap_get_label('apply_now_action'),
            'url'     => nvis_sap_get_program_action_url('apply_now', $post),
            'key'     => 'apply_now',
            'icon'    => nvis_sap_get_icon('paper-airplane', ['size' => 20, 'class' => 'nvis-icon--md']),
            'sr_text' => $sr_text,
            'offsite' => true
        ],
        [
            'label'   => nvis_sap_get_label('request_info_action'),
            'url'     => nvis_sap_get_program_action_url('request_info', $post),
            'key'     => 'request_info',
            'icon'    => nvis_sap_get_icon('inbox-arrow-down', ['size' => 20, 'class' => 'nvis-icon--md']),
            'sr_text' => $sr_text,
            'offsite' => true
        ]
    ],
    'wrapper_class' => 'program-actions'
];

$program_homepage = get_field('program_home_page', $post);

if ($program_homepage) {
    $defaults['actions'][] = [
        'label'   => nvis_sap_get_label('prog_homepage_action'),
        'url'     => $program_homepage,
        'key'     => 'visit_homepage',
        'icon'    => nvis_sap_get_icon('arrow-top-right-on-square', ['size' => 20, 'class' => 'nvis-icon--md']),
        'sr_text' => $sr_text,
        'offsite' => true
    ];
}

$args = nvis_parse_template_args($args, $defaults, $template);

if ($args['add_permalink']) {
    $permalink = $args['add_permalink'] === true ? get_permalink($post) : $args['add_permalink'];

    array_unshift(
        $args['actions'],
        [
            'label'   => $args['label_permalink'],
            'url'     => $permalink,
            'key'     => 'program_details',
            'icon'    => nvis_sap_get_icon('arrow-right-circle', ['size' => 20, 'class' => 'nvis-icon--md']),
            'sr_text' => $sr_text,
        ]
    );
}

nvis_sap_get_template_part('common/action-list', $args);
