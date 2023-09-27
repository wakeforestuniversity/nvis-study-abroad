<?php

/**
 * The template to display a Single Program
 *
 * @package NVISStudyAbroad
 * @subpackage templates
 * @version 1.0
 */

$args['post'] = $post;
nvis_sap_get_template_part('common/header');
?>

<article
  id="<?php nvis_article_id_attr('', true); ?>"
  <?php post_class('', $post); ?>
>
  <?php
  nvis_sap_get_template_part('common/breadcrumbs');
  nvis_sap_get_template_part('single-program/page-header', $args);
  ?>
  <div class="fprogram-main entry-content">
    <?php nvis_sap_get_template_part('single-program/content', $args); ?>
  </div>
  <?php nvis_sap_get_template_part('single-program/sidebar', $args); ?>
  <footer>
    <hr>
  </footer>
</article>

<?php nvis_sap_get_template_part('common/footer');
