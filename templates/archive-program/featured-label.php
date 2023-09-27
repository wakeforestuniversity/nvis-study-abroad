<?php

$post = nvis_args_or_global('post', $args);

$defaults = [
    'attachment_id' => 0,
    'image_size'    => 20,
    'icon'          => 'flag',
    'text'          => nvis_sap_get_label('featured'),
];

$args = nvis_parse_template_args($args, $defaults, $template);

if (nvis_sap_program_is_featured($post)) : ?>
<div class="program-featured-label">
    <?php if ($args['attachment_id'] && is_numeric($args['attachment_id'])): ?>
    <span class="program-featured-label__img"><?php echo wp_get_attachment_image($args['attachment_id'], [$args['image_size'], $args['image_size']]); ?></span>
    <?php elseif ($args['icon']) :?>
    <span class="program-featured-label__icon"><?php echo nvis_sap_get_icon($args['icon'], ['size'=> '20']); ?></span>
    <?php endif; ?>

    <?php if ($args['text']): ?>
    <span class="program-featured-label__text"><?php echo esc_html($args['text']); ?></span>
    <?php endif; ?>
</div>
<?php endif;
