
<?php
/**
 * The template for displaying the Program's 'parameters'.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'show_locations' => true,
    'show_terms' => true,
    'show_subjects' => true,
    'show_key_params' => false,
    'heading' => nvis_sap_get_label('additional_details'),
    'heading_icon' => nvis_sap_get_icon('list-bullet'),
    'label_location' => nvis_get_taxonomy_label('nvis_location', 'name'),
    'label_subject' => nvis_get_taxonomy_label('nvis_subject', 'name'),
    'label_term' => nvis_get_taxonomy_label('nvis_term', 'name'),
    'params' => get_field('additional_params', $post),
];

$args = nvis_parse_template_args($args, $defaults, $template);

$params = $args['show_key_params'] ?
    $args['params'] :
    nvis_sap_filter_key_params($args['params']);

$scale = [
    'extra-large' => 30,
    'large' => 20,
    'medium' => 10,
    'small' => 2,
    'single' => 1,
];

$num_locs = wp_count_terms([
    'taxonomy' => 'nvis_location',
    'object_ids' => $post->ID, 
]);
$num_subjects = wp_count_terms([
    'taxonomy' => 'nvis_subject',
    'object_ids' => $post->ID, 
]);
$num_terms = wp_count_terms([
    'taxonomy' => 'nvis_term',
    'object_ids' => $post->ID, 
]);

if ($num_locs) {
    $locations = nvis_sap_get_locations_list($post);
    $locations = array_map('esc_html', $locations);
    $locations = !$locations ?:
        sprintf('<ul class="terms items-%s"><li>', $num_locs) .
        implode('</li><li>', $locations) .
        '</ul>';
}

if ($num_subjects) {
    $subj_list_class = nvis_sap_get_scale_label($num_subjects, $scale);
    
    $subjects = nvis_get_the_term_list(
        $post->ID,
        'nvis_subject',
        sprintf('<ul class="%s"><li>', $subj_list_class),
        '</li><li>',
        '</li></ul>',
        false
    );
}

if ($num_terms) {
    $terms = nvis_get_the_term_list(
        $post->ID,
        'nvis_term',
        sprintf('<ul class="terms items-%s"><li>', $num_terms),
        '</li><li>',
        '</li></ul>',
        false
    );
}

$show_params = is_array($args['params']) && !empty($params);

if ($args['show_locations'] || $args['show_subjects'] || $args['show_terms'] || $show_params) :
?>
<section class="additional-details">
    <h2>
        <?php echo $args['heading_icon']; ?>
        <?php echo esc_html($args['heading']); ?>
    </h2>
    <table class="parameters-table table">
        <tbody>

            <?php if ($args['show_locations'] && $num_locs) : ?>
            <tr id="param-location" class="param-location param">
                <th scope="row"><?php echo esc_html($args['label_location']); ?></th>
                <td><?php echo $locations; ?></td>
            </tr>
            <?php endif; ?>
            
            <?php if ($args['show_subjects'] && $num_subjects) : ?>
            <tr id="param-subject" class="param-subject param">
                <th scope="row"><?php echo esc_html($args['label_subject']); ?></th>
                <td><?php echo $subjects; ?></td>
            </tr>
            <?php endif; ?>

            <?php if ($args['show_terms'] && $num_terms) : ?>
            <tr id="param-term" class="param-term param">
                <th scope="row"><?php echo esc_html($args['label_term']); ?></th>
                <td><?php echo $terms; ?></td>
            </tr>
            <?php endif; ?>

            <?php 
            if ($show_params) :
                foreach ($params as $i => $param) :
                    $param_slug = 'param-' . sanitize_title( $param['name'], $i, 'post' ); 
            ?>
            <tr id="<?php echo $param_slug; ?>" class="<?php echo $param_slug . ' param'; ?>">
                <th scope="row"><?php echo esc_html($param['name']); ?></th>
                <td><?php echo esc_html($param['value']); ?></td>
            </tr>
            <?php 
                endforeach; 
            endif;
            ?>

        </tbody>
    </table>
</section>

<?php endif; 
