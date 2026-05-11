<?php
/**
 * Business Directory Archive.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

get_header();

$container = is_post_type_archive( 'business' ) || is_tax( 'business_category' )
	? 'container'
	: get_theme_mod( 'understrap_container_type' );
?>

<main id="primary" class="site-main business-directory">

	<section class="business-directory__hero">
		<div class="<?php echo esc_attr( $container ); ?>">
			<h1 class="business-directory__title">Find a Business</h1>
			<p class="business-directory__intro">
				Browse trusted businesses from the Circle for Friends member network.
			</p>
		</div>
	</section>

	<section class="business-directory__content">
		<div class="<?php echo esc_attr( $container ); ?>">

			<?php get_template_part( 'global-templates/business-filter' ); ?>

			<?php get_template_part( 'global-templates/business-loop' ); ?>

		</div>
	</section>

</main>

<?php
get_footer();