<?php
/**
 * The template for displaying Program meta items, for use on single Program.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'wrapper_class'             => 'nvis-meta-group',
    'meta_before_fmt'           => '<span class="%s nvis-meta-group__item"><span class="label">%s<span class="separator">:</span></span> <span class="value">',
    'terms_separator'           => ', ',
    'meta_after'                => '</span></span>',
    'params'                    => nvis_sap_get_program_key_params($post)
];

$args = nvis_parse_template_args($args, $defaults, $template);

$classes = [
    'program-meta',
    esc_attr($args['wrapper_class'])
];

if (is_array($args['params']) && !empty($args['params'])): 
    $inner = '';
    $open = sprintf('<div class="%s">', implode(' ', $classes));
    $close = '</div>';
    
    foreach($args['params'] as $i => $param) :
        if (!$param['value']) continue;

        $key = sanitize_title($param['name'], $i, 'post');
        $class = 'meta-' . $key;
        
        $inner .= sprintf(
            $args['meta_before_fmt'], 
            $class, 
            esc_html($param['name'])
        );
        $inner .= esc_html($param['value']);
        $inner .= $args['meta_after'];
    endforeach;

    if ($inner):
        echo $open . $inner . $close;
    endif;
endif;
