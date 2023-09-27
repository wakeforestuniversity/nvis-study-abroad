<?php
/**
 * The template for displaying the featured image of a post.
 *
 * @package NVISPrograms
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'show_image'             => true,
    'show_empty_element'     => false,
    'link_image'             => false,
    'image_size'             => [
        $GLOBALS['content_width'] / 2,
        $GLOBALS['content_width'] / 3,
    ],
    'image_align'            => 'right',
    'image_wrapper_class'    => '',
    'image_attributes'       => '',
    'fallback'               => ''
];

$args = nvis_parse_template_args($args, $defaults, $template);

$align_class = in_array($args['image_align'], ['left','right','center','none'], true)
    ? $args['image_align']
    : 'right';

$align_class = 'align' . $align_class;
$classes = [
    'featured-image',
    $align_class,
    esc_attr($args['image_wrapper_class'])
];

$show_image = $args['show_image'] && (
    has_post_thumbnail($post) ||
    !empty($args['fallback'])
);

if ($show_image) :
    $image = nvis_post_thumbnail_or_fallback(
        $post,
        $args['fallback'],
        $args['image_size'],
        $args['image_attributes']
    );

    if ($args['link_image']) :
        printf(
            '<div class="%s"><a href="%s">%s</a></div>',
            implode(' ', $classes),
            get_the_permalink($post),
            $image
        );
    else:
        printf(
            '<div class="%s">%s</div>',
            implode(' ', $classes),
            $image
        );
    endif;
elseif ($args['show_empty_element']) :
    printf(
        '<div class="%s"></div>',
        implode(' ', $classes)
    );
endif;
