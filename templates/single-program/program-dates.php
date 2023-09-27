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
    'show_decision_date'  => true,
    'heading'             => nvis_sap_get_label('program_dates'),
    'heading_icon'        => nvis_sap_get_icon('calendar-days'),
    'label_term'          => nvis_get_taxonomy_label('nvis_term', 'singular_name'),
    'label_app_deadline'  => nvis_sap_get_label('app_deadline'),
    'label_decision_date' => nvis_sap_get_label('decision_date'),
    'label_program_start' => nvis_sap_get_label('program_start'),
    'label_program_end'   => nvis_sap_get_label('program_end'),
    'label_missing_date'  => nvis_sap_get_label('missing_date'),
    'dates'               => nvis_sap_get_program_dates($post),
];

$args = nvis_parse_template_args($args, $defaults, $template);

if (is_array($args['dates'])) : ?>
<section id="program-dates" class="fprogram-dates">
  <h2 class="fprogram-dates__title fprogram-sidebar__title">
    <?php echo $args['heading_icon']; ?>
    <?php echo esc_html($args['heading']); ?>
  </h2>

  <div class="fprogram-dates-table-wrapper">
    <table class="fprogram-dates-table table">
      <thead>
        <tr>
          <th scope="col"><?php echo esc_html($args['label_term']); ?></th>
          <th scope="col"><?php echo esc_html($args['label_app_deadline']); ?></th>
  
          <?php if ($args['show_decision_date']) : ?>
          <th scope="col"><?php echo esc_html($args['label_decision_date']); ?></th>
          <?php endif; ?>
          
          <th scope="col"><?php echo esc_html($args['label_program_start']); ?></th>
          <th scope="col"><?php echo esc_html($args['label_program_end']); ?></th>
        </tr>
      </thead>
      <tbody>
  
        <?php foreach($args['dates'] as $date) :?>
  
        <tr>
          <th scope="row"><?php echo esc_html($date['term'] . ' ' . $date['year']); ?></th>
          <td><?php echo ($date['app_deadline_date']) ? esc_html($date['app_deadline_date']) : $args['label_missing_date']; ?></td>
  
          <?php if ($args['show_decision_date']) : ?>
          <td><?php echo ($date['decision_date']) ? esc_html($date['decision_date']) : $args['label_missing_date']; ?></td>
          <?php endif; ?>
          
          <td><?php echo ($date['start_date']) ? esc_html($date['start_date']) : $args['label_missing_date']; ?></td>
          <td><?php echo ($date['end_date']) ? esc_html($date['end_date']) : $args['label_missing_date']; ?></td>
        </tr>
  
        <?php endforeach; ?>
        
      </tbody>
    </table>
  </div>

</section>

<?php endif;
