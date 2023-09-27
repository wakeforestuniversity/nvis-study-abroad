<?php
/**
 * The template for displaying the Program's application deadlines.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

$post = nvis_args_or_global('post', $args);

$defaults = [
    'heading'             => nvis_sap_get_label('app_deadlines'),
    'heading_icon'        => nvis_sap_get_icon('clock', ['style'=>'outline']),
    'label_app_deadline'  => nvis_sap_get_label('app_deadlines'),
    'label_missing_date'  => nvis_sap_get_label('missing_date'),
    'dates'               => nvis_sap_get_program_dates($post, null, true),
];

$args = nvis_parse_template_args($args, $defaults, $template);
$is_first_open = false;

if (is_array($args['dates']) && !empty($args['dates'])) : ?>
<div class="application-deadlines">
  <h2 class="fprogram-dates__title fprogram-sidebar__title">
    <?php echo $args['heading_icon']; ?>
    <span class="heading-text"><?php echo esc_html($args['heading']); ?></span>
  </h2>

  <div class="application-deadlines-list">

    <?php foreach($args['dates'] as $date) :?>

      <div class="application-deadline">
        <h3 class="application-deadline__term"><?php echo esc_html($date['term'] . ' ' . $date['year']); ?></h3>
        <?php //echo esc_html($args['label_app_deadline']); ?>
        <span class="application-deadline__date">
          <?php echo ($date['app_deadline_date']) ? esc_html($date['app_deadline_date']) : $args['label_missing_date']; ?>
        </span>
      </div>
  
    <?php endforeach; ?>

  </div>

</div>

<?php endif;
