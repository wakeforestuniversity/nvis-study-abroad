<?php
/**
 * The template for displaying Program meta items, for use on single Program.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

$post = nvis_args_or_global('post', $args);

$defaults = [
    'video_url' => get_field('video_url', $post),
    'height' => '',
    'width' => '',
    'discover' => true
];

$args = nvis_parse_template_args($args, $defaults, $template); 

$html = false; 

if ($args['video_url']) {
    $html = wp_oembed_get($args['video_url'], $args);

    if ($html) {
        $html = apply_filters('embed_oembed_html', $html, $args['video_url'], $args, $post->ID);
    }
    
}

if ($html) : ?>
<div class="program-video">
    <?php echo $html; ?>
</div>
<?php endif; 