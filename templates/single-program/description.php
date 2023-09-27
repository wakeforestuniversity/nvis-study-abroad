<?php
$post = nvis_args_or_global('post', $args);

$defaults = [];

$args = nvis_parse_template_args($args, $defaults, $template);

$description = get_field('description', $post);
?>
<div class="description">
    <?php echo wp_kses_post($description); ?>
</div>