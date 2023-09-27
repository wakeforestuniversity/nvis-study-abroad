<?php
/**
 * The template for displaying the Program's brochure sections.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'enhanced_brochure' => true,
    'show_main_heading' => true,
    'show_section_headings' => true,
    'insert_location_map' => true,
    'hide_contact_section' => false,
    'location_section_slug' => 'location',
    'contact_section_slug' => nvis_sap_contact_section_slug($post),
    'heading' => nvis_sap_get_label('program_info'),
    'heading_icon' => nvis_sap_get_icon('information-circle'),
    'sections' => nvis_sap_get_program_brochure_sections($post)
];

$args = nvis_parse_template_args($args, $defaults, $template);

if (!is_array($args['sections'])) {
    return;
}

$section_htag = nvis_get_heading_tag($args['show_main_heading'] ? 3 : 2);
$num_sections = count($args['sections']);
$wrapper_classes = ['brochure-sections'];
$is_enhanced = $args['enhanced_brochure'] && $num_sections > 1;

if ($is_enhanced) {
    // This is the _earliest possible moment_ that we know whether we need Tabby.
    wp_enqueue_script('tabby-js');
    wp_add_inline_script( 
        'tabby-js', 
        file_get_contents(nvis_sap_get_path('assets/js/program.min.js'))
    );
    $wrapper_classes[] = 'brochure-sections--enhanced';
}
if ($num_sections) : ?>
<section class="<?php echo implode(' ', $wrapper_classes); ?>">
    <?php if ($args['show_main_heading']) : ?>
    <h2 class="brochure-sections__title">
        <?php echo $args['heading_icon']; ?>
        <?php echo esc_html($args['heading']); ?>
    </h2>
    <?php endif; ?>

    <div class="content-wrapper">

        <?php if ($num_sections > 1) : $default = 'data-tabby-default'; ?>
        <ul data-tabs>    
            <?php 
            foreach($args['sections'] as $section) :
                $display_section = 
                    !empty(trim($section['content'])) && // Only list sections with content.
                    (
                        $section['slug'] !== $args['contact_section_slug'] ||
                        !$args['hide_contact_section']   
                    );
                    
                if ($display_section) : ?>
            <li>
                <a <?php if ($default) { echo $default; $default = false; } ?> href="<?php echo '#section-' . esc_attr($section['slug']); ?>">
                <?php echo esc_html($section['title']); ?></a>
            </li>
            <?php 
                endif; 
            endforeach; 
            ?>
        </ul>
        <?php endif; // Sections > 1 ?>

        <?php
        // Output the content.
        foreach($args['sections'] as $section) : 
            $display_section = 
                !empty(trim($section['content'])) && // Only output sections with content.
                (
                    $section['slug'] !== $args['contact_section_slug'] ||
                    !$args['hide_contact_section']   
                );

            if ($display_section) : 
        ?>
        <div id="<?php echo 'section-' . esc_attr($section['slug']); ?>" class="brochure-section">
            <?php 
            if ($args['show_section_headings']) :  
                printf(
                    '<%s class="brochure-section__title">%s</%s>', 
                    $section_htag, 
                    esc_html($section['title']), 
                    $section_htag
                );
            endif; 
            ?>

            <div class="brochure-section__content">
                <?php 
                    if ($section['slug'] === $args['location_section_slug'] && $args['insert_location_map']) {
                        $width = ceil($GLOBALS['content_width'] * 2/3);
                        $height = ceil($width/2);

                        nvis_sap_get_template_part(
                            'single-program/location-map', 
                            array_merge($args,compact('width', 'height'))
                        ); 
                    }

                    echo wp_kses_post($section['content']); 
                ?>
            </div>
            <hr>
        </div>
        <?php 
                endif;
            endforeach;
        ?>
        
    </div>
</section>
<?php endif; // Number of sections > 0 
