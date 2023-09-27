<?php
/**
 * The template to display a program archives.
 *
 * @package NVISStudyAbroad
 * @subpackage templates
 * @version 1.0
 */

 defined('ABSPATH') || exit;

 global $posts;
 
 nvis_sap_get_template_part('common/header'); ?>
 <div class="programs-archive-main">
     <?php
         nvis_sap_get_template_part('common/breadcrumbs');
         nvis_sap_get_template_part('archive-program/page-header');
         nvis_sap_get_template_part('archive-program/filters');
         nvis_sap_get_template_part('archive-program/num-results');
         nvis_sap_get_template_part('archive-program/program-list', ['programs' => $posts]);
         nvis_sap_get_template_part('common/pagination');
     ?>
 </div>
 <?php nvis_sap_get_template_part('common/footer');
 