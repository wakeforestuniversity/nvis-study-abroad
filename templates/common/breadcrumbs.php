<?php
/**
 * Displays a breadcrumb trail, for use in the page header.
 *
 * Relies on third party plugins to handle rendering breadcrumbs. Supports:
 * - Breadcrumb NavXT
 * - Yoast SEO
 * - All in One SEO
 * - Rank Math
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$defaults = [
    'show_breadcrumbs'         => true,
    'breadcrumb_wrapper_id'    => 'breadcrumbs',
    'breadcrumb_wrapper_class' => 'breadcrumbs'
];

$args = nvis_parse_template_args($args, $defaults, $template);

if ($args['show_breadcrumbs']) : ?>
<div
  id="<?php echo esc_attr($args['breadcrumb_wrapper_id']); ?>"
  class="nvis-breadcrumbs <?php echo esc_attr($args['breadcrumb_wrapper_class']); ?>">
  <?php
    if (function_exists('bcn_display')) {
        bcn_display();
    } else if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb();
    } else if (function_exists('aioseo_breadcrumbs')) {
        aioseo_breadcrumbs();
    } else if (function_exists('rank_math_the_breadcrumbs')) {
        rank_math_the_breadcrumbs();
    } 
  ?>
</div>
<?php endif;
