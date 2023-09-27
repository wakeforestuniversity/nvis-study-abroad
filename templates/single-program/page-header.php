<?php
$post = nvis_args_or_global('post', $args);
$defaults = [
    'show_video'        => true,
    'show_image'        => true,
    'show_location'     => true
];

$args = nvis_parse_template_args($args, $defaults, $template);

?>
<header class="single-fprogram-page-header page-header entry-header">
  <?php nvis_sap_get_template_part('common/page-header-backdrop'); ?>

  <div class="page-header__content">

    <div class="page-header__title-group">
      <h1 class="page-title">
        <?php echo get_the_title($post); ?>
      </h1>

      <?php if ($args['show_location']) : ?>
      <div class="fprogram-location taxonomy">
        <?php echo nvis_sap_get_icon('map-pin', ['size' => 24, 'class' => 'nvis-icon--md']); ?>
        <?php echo nvis_sap_get_location_label($post); ?>
      </div>
      <?php endif; ?>

    </div>

    <?php nvis_sap_get_template_part('single-program/program-meta', compact('post')); ?>

    <?php
    if ($args['show_video'] && nvis_sap_program_has_video($post)) :
          nvis_sap_get_template_part('single-program/video', $args);
    elseif ($args['show_image']) :
        nvis_sap_get_template_part('common/post-featured-image', $args);
    endif;
    ?>
  </div>
</header><!-- .page-header -->
