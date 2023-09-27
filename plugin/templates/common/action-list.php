<?php

$defaults = [
    'actions' => [],
    'wrapper_class' => '',
];

$args = nvis_parse_template_args($args, $defaults, $template);

$class = nvis_get_html_class_attr(
    'action-list',
    $args['wrapper_class']
);

if (!empty($args['actions'])) : ?>
<div
    class="<?php echo $class; ?>">
    <ul>
        <?php
        foreach ($args['actions'] as $i => $action) :
            if (!$action['url'] || !$action['label']) {
                continue;
            }

            $class = $action['key'] ? str_replace('_', '-', $action['key']) : '';
            $class .= ' button ';
            $class .= $i ? 'button-secondary' : 'button-primary';
            $icon = $args['show_icons'] && isset($action['icon']) ? $action['icon'] . ' ' : '';
            $sr_text = !empty($action['sr_text']) ? 
                sprintf(' <span class="screen-reader-text">%s</span>', $action['sr_text']) :
                '';
            $offsite_atts = !empty($action['offsite']) ? 'rel="noreferrer noopener" target="_blank"' : '';

            printf(
                '<li><a class="%s" href="%s" %s>%s<span class="link-text">%s%s</span></a></li>',
                esc_attr($class),
                esc_url($action['url']),
                $offsite_atts,
                $icon,
                esc_html($action['label']),
                $sr_text
            );
        endforeach;
        ?>
    </ul>
</div>
<?php endif;
