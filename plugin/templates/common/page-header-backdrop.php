<?php

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'attachment_id'    => 0,
    'image_source'     => null,
    'show_backdrop'    => false,
    'backdrop_size'    => 'large',
    'fallback_to_post' => true,
    'wrapper_class'    => ''
];

$args = nvis_parse_template_args($args, $defaults, $template);

if (!$args['image_source'] && empty($args['attachment_id']) && $args['fallback_to_post']) {
    $args['attachment_id'] = get_post_thumbnail_id($post);
}

if ($args['show_backdrop'] && ($args['image_source'] || $args['attachment_id'])) :
    if ($args['image_source']) {
        global $content_width;

        $image = sprintf(
            '<img src="%s" width="%s" height="%s" alt="" title="" aria-hidden="true">',
            $args['image_source'],
            $content_width,
            $content_width / 2,
        );
    } else {
        $image = wp_get_attachment_image(
            $args['attachment_id'],
            $args['backdrop_size'],
            false,
            ['alt' => '', 'title' => '', 'aria-hidden' => 'true']
        );
    }

    $wrapper_class = is_array($args['wrapper_class']) ? 
        implode(' ', $args['wrapper_class']) :
        $args['wrapper_class'];

    printf('<div class="page-header__backdrop %s">%s</div>', $wrapper_class, $image);
endif;
