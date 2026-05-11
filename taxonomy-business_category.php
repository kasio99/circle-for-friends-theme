<?php
/**
 * Business Category Archive.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

get_header();

$container = 'container';
$term      = get_queried_object();
?>

<main id="primary" class="site-main business-directory">

	<section class="business-directory__hero">
		<div class="<?php echo esc_attr( $container ); ?>">
			<h1 class="business-directory__title">
				<?php echo esc_html( $term->name ); ?>
			</h1>

			<p class="business-directory__intro">
				Browse trusted <?php echo esc_html( strtolower( $term->name ) ); ?> businesses from the Circle for Friends member network.
			</p>
		</div>
	</section>

	<section class="business-directory__content">
		<div class="<?php echo esc_attr( $container ); ?>">

			<?php 
            
            set_query_var( 'business_category_from_taxonomy', $term->slug );
            
            get_template_part( 'global-templates/business-filter' ); 
			
			get_template_part( 'global-templates/business-loop' );
			?>
<p>Content area loading</p>
		</div>
	</section>

</main>

<?php
get_footer();