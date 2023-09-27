<?php
/**
 * Displays a dropdown filter for a given taxonomy.
 *
 * An abstract template. Only meant to be referenced by other filter templates.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */


defined('ABSPATH') || exit;

$defaults = [
    'taxonomy'             => null,
    'query_var'            => null,
    'hierarchical'         => false,
    'hide_empty'           => true,
    'selected'             => '',
    'show_option_none'     => '',
    'option_none_value'    => '',
    'value_field'          => 'slug',
    'orderby'              => 'name',
    'order'                => 'ASC',
    'walker'               => null,
    'label'                => null,
    'short_label'          => null,
    'none_selected_prefix' => nvis_sap_get_label('none_selected_prefix'),
    'missing_data_text'    => nvis_sap_get_label('missing_filter_data')
];

$defaults['selected'] = strtolower(get_query_var($args['query_var']));

$args = nvis_parse_template_args($args, $defaults, $template);

if (!taxonomy_exists($args['taxonomy'])) {
    return;
}

$label = $args['label'] ?? nvis_get_taxonomy_label($args['taxonomy'], 'singular_name');
$args['name'] = $args['query_var'];
$args['nvis_search_filter'] = true;

if (!$defaults['show_option_none']) {
    $short_label = $args['short_label'] ?? $label;
    $args['show_option_none'] = 
        nvis_get_taxonomy_label($args['taxonomy'], 'none_selected') ??
        $args['none_selected_prefix'] . $short_label;
}

if ($args['taxonomy'] && $args['query_var'] && $label) : ?>
<div
    class="nvis-filter-<?php echo esc_attr($args['query_var']); ?> nvis-filters-field">
    <label
        for="<?php echo esc_attr($args['query_var']); ?>"
        class="nvis-filters-field__label"><?php echo esc_html($label); ?></label>
    <?php wp_dropdown_categories($args); ?>
</div>
<?php else : ?>
<div>
    <?php printf($args['missing_data_text'], $args['taxonomy']); ?>
</div>
<?php endif;
