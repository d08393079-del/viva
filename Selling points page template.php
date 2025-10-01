<?php
/**
 * Template Name: Selling points
 * Description: A custom template for selling point pages.
 */
get_header();
?>

<div class="page-hero">
    <h1><?php the_title(); ?></h1>
</div>

  <div class="single-page-body-elementor-section">
    <?php
      while ( have_posts() ) :
        the_post();
        the_content();
      endwhile;
    ?>
  </div>

<?php get_footer(); ?>
