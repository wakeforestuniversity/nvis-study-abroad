<?php
/**
 * The template for displaying the contact info.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

use InvisibleUs\StudyAbroad\Program;

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'enhanced_brochure'     => true,
    'show_main_heading'     => true,
    'show_section_headings' => true,
    'insert_location_map'   => true,
    'show_contact_info'     => true,
    'show_contact_section'  => false,
    'contact_section_slug'  => 'contact',
    'heading'               => nvis_sap_get_label('contact_info'),
    'heading_icon'          => nvis_sap_get_icon('phone', ['style'=>'solid']),
    'contact_section'       => Program::get_contact_section($post),
    'contact_name'          => get_field('contact_name', $post),
    'contact_phone'         => get_field('contact_phone', $post),
    'contact_email'         => get_field('contact_email', $post),
];

$args = nvis_parse_template_args($args, $defaults, $template);

$show_contact_info = $args['show_contact_info'] && $args['contact_name'];
$show_contact_section = $args['show_contact_section'] && $args['contact_section'];

if ($show_contact_info || $show_contact_section) : ?>
<div class="contact-info">
    <h2 class="contact-info__title fprogram-sidebar__title">
        <?php echo $args['heading_icon']; ?>
        <span class="heading-text"><?php echo esc_html($args['heading']); ?></span>
    </h2>

    <div class="conact-info__content">
    <?php if ($show_contact_info) : ?>
        <div class="main-contact__name"><strong><?php echo esc_html($args['contact_name']); ?></strong></div>    
        <div class="main-contact__email">
            <?php 
            printf(
                '<a href="%s">%s</a>',
                esc_url('mailto:' . antispambot($args['contact_email'])),
                esc_html(antispambot($args['contact_email'])),
            );
            ?>
        </div>    
        <div class="main-contact__phone"><?php echo esc_html($args['contact_phone']); ?></div>    
    <?php 
    elseif ($show_contact_section): 
        echo wp_kses_post($args['contact_section']['content']);
    endif;
    ?>
    </div>
</div>
<?php endif;