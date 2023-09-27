
<?php
/**
 * The template for displaying the Program's content.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'min_images' => 2,
    'link_images' => true,
    'lightbox_images' => true,
    'img_size' => 'medium',
    'img_class' => null,
    'img_link_class' => null,
    'label_lightbox_dialog_title' => nvis_sap_get_label('lightbox_dialog_title'),
    'map' =>  [
        'args' => [
            'width' => $GLOBALS['content_width'],
            'height' => $GLOBALS['content_width'] * 2/3,
            'show_markers' => true,
            'hd' => false
        ],
        'alt' => null
    ],
    'images' => nvis_sap_get_program_images($post),
];

$args = nvis_parse_template_args($args, $defaults, $template);

$has_min_images = is_array($args['images']) && count($args['images']) >= $args['min_images'];
$has_map = is_array($args['map']) && !empty($map);

if ($has_min_images || $has_map) : 
    $img_size = $args['img_size'];

    if ($args['map']) :
        $locations = nvis_sap_get_program_locations($post);
        $map_alt = $args['map']['alt'] ?? nvis_sap_get_locations_alt_text($locations);

        $args['images'][] = [
            'url' => nvis_sap_get_map_image_url($locations, $args['map']['args']),
            'alt' => $map_alt,
            'width' => $args['map']['args']['width'],
            'height' => $args['map']['args']['height'],
        ];
    endif;

    if ($args['link_images'] && $args['lightbox_images']) :
        wp_enqueue_script('photoswipe');
    endif;
?>

<figure id="media-gallery" class="fprogram-media-gallery <?php echo 'fprogram-media-gallery--' . $args['img_size']; ?>">
    <?php foreach ($args['images'] as $image) : ?>
    <figure>
        <?php 
        $open = '';
        $close = '';
        $img_tag = '';

        if (isset($image['sizes'][$img_size])) :
            $src = $image['sizes'][$img_size];
            $width = $image['sizes'][$img_size.'-width'];
            $height = $image['sizes'][$img_size.'-height'];
        else :
            $src = $image['url'];
            $width = $image['width'];
            $height = $image['height'];
        endif;

        if ($args['link_images']) : 
            $open = sprintf(
                '<a href="%s" class="media-wrapper %s" data-pswp-width="%s" data-pswp-height="%s" title="%s">',
                esc_url($image['url']),
                esc_attr($args['img_link_class']),
                $image['width'],
                $image['height'],
                $image['alt']
            );
            $close = '</a>';
        else:
            $open = '<span class="media-wrapper">';
            $close = '</span>';
        endif;

        $atts = [
            'alt' => $image['alt'],
            'class' => $args['img_class'],
        ];

        if (!empty($image['ID'])) : 
            $img_tag = wp_get_attachment_image($image['ID'], $args['img_size'], false, $atts);
        elseif ($src) :
            $img_tag = nvis_get_remote_image_tag($src, [$width, $height], $atts);
        endif;

        if ($img_tag) :
            echo $open . $img_tag . $close;
        endif;
        ?>
    </figure>
    <?php endforeach; ?>
</figure>

<?php if ($args['link_images'] && $args['lightbox_images']) : ?>
<script>
    <?php printf('window.nvisPSWPDialogTitle = "%s";', $args['label_lightbox_dialog_title']) ;?>
    
    window.addEventListener('load', function() {
        const lightbox = new PhotoSwipeLightbox({
            gallery: '#media-gallery',
            children: 'a',
            pswpModule: PhotoSwipe 
        });

        lightbox.init();

        lightbox.on('afterInit', () => {
            const dialog = document.querySelector('.pswp[role="dialog"]');
            
            if (!dialog.getAttribute('title') && !dialog.getAttribute('aria-label')) {
                dialog.setAttribute('aria-label', window.nvisPSWPDialogTitle);
            }
        });
    });
</script>
<?php endif; ?>

<?php endif; 