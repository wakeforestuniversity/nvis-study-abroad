<?php
$post = nvis_args_or_global('post', $args);

$defaults = [
    'sponsor_content' => nvis_sap_get_sponsor_program_content($post)
];

$args = nvis_parse_template_args($args, $defaults, $template);

if (!empty($args['sponsor_content'])) {
    printf(
        '<div class="partner-content">%s</div>',
        $args['sponsor_content']
    );
} 
