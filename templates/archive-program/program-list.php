<?php
/**
 * Displays a list of Program items, for use in an archive.
 *
 * @package NVISPrograms
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$defaults = [
    'label_no_programs_found' => nvis_get_post_type_label('nvis_foreign_program', 'not_found'),
    'wrapper_class' => '',
];

$args = nvis_parse_template_args($args, $defaults, $template);

$list_classes = nvis_get_html_class_attr(
    'programs-list',
    (nvis_is_filtered_results('nvis_foreign_program') ? 'filtered-results' : ''),
    $args['wrapper_class']
);

?>
<section id="post-list" class="<?php echo $list_classes; ?>">
    <?php if (is_array($args['programs']) && !empty($args['programs'])) :
        foreach ($args['programs'] as $post) :
            nvis_sap_get_template_part('archive-program/program-item', compact('post'));
        endforeach;
    else: ?>

    <p class="empty-state-message"><?php echo esc_html($args['label_no_programs_found']); ?>
    </p>

    <?php endif; ?>
</section>