<?php
/**
 * Displays a single Program item, for use in an archive or other Program list.
 *
 * @package NVISPrograms
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'show_image'            => true,
    'show_program_location' => true,
    'show_program_meta'     => true,
    'show_featured_label'   => true,
    'show_program_desc'     => false,
    'show_program_actions'  => true,
    'wrapper_class'         => ''
];

$args = nvis_parse_template_args($args, $defaults, $template);

if ($post) : ?>
<article <?php post_class($args['wrapper_class'], $post); ?>>

    <?php
    if ($args['show_image']) :
        nvis_sap_get_template_part(
            'common/post-featured-image', 
            [
                'post' => $post, 
                'image_size' => 'medium', 
                'image_align' => 'left', 
                'link_image' => true, 
                'context' => $template,
                'show_empty_element' => true
            ]
        );
    endif;
    ?>

    <div class="program-info item-info">
        <header>
            <h2 class="program-title entry-title"><a
                    href="<?php echo get_the_permalink($post); ?>"><?php echo get_the_title($post); ?></a></h2>
            <?php if ($args['show_program_location'] && taxonomy_exists('nvis_location')) : ?>
            <div class="program-location taxonomy">
                <?php echo nvis_sap_get_icon('map-pin', ['size' => 20, 'class' => 'nvis-icon--md']); ?>
                <?php echo nvis_sap_get_location_label($post); ?>
            </div>
            <?php endif; ?>

            <?php
            if ($args['show_featured_label']) :
                nvis_sap_get_template_part(
                    'archive-program/featured-label',
                    compact('post')
                );
            endif;
            ?>
        </header>
        <?php
        if ($args['show_program_meta']) :
            nvis_sap_get_template_part(
                'archive-program/program-meta',
                compact('post')
            );
        endif;
        ?>

        <?php
        if ($args['show_program_desc']) :
            nvis_sap_get_template_part(
                'single-program/description', 
                compact('post')
            );
        endif;
        ?>
    </div>

    <?php
    if ($args['show_program_actions']) :
        nvis_sap_get_template_part(
            'single-program/program-actions',
            [
                'post'          => $post,
                'add_permalink' => true
            ]
        );
    endif;
    ?>
</article>
<?php endif;
