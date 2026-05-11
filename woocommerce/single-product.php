<?php
/**
 * The Template for displaying all single products
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

// Remove product image gallery.
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

// Remove tabs: Description / Additional information.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

// Remove related products.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

// Remove sidebar.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

get_header( 'shop' );
?>

<main id="primary" class="site-main single-product-clean">
	<div class="container">

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; ?>

	</div>
</main>

<?php
get_footer( 'shop' );