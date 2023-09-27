<?php
/**
 * The template for displaying the Program sidebar with calls to action and contact info.
 *
 * @package NVISStudyAbroad
 * @subpackage Templates
 * @version 1.0
 */

defined('ABSPATH') || exit;

?>
<section class="fprogram-sidebar nvis-sidebar">
  <div class="nvis-sidebar__content nvis-sticky">
    <?php nvis_sap_get_template_part('single-program/program-actions'); ?>
    <?php nvis_sap_get_template_part('single-program/term-deadlines'); ?>
    <?php nvis_sap_get_template_part('single-program/contact'); ?>

    <?php nvis_back_to_top_link(); ?>
  </div>
</section>