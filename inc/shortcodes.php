<?php
function cff_business_directory_promo_shortcode() {
	ob_start();

	$icon_map = array(
		'trades'       => 'fa-solid fa-screwdriver-wrench',
		'professional' => 'fa-solid fa-briefcase',
		'auto'         => 'fa-solid fa-car-side',
		'hospitality'  => 'fa-solid fa-utensils',
		'travel'       => 'fa-solid fa-umbrella-beach',
		'health'       => 'fa-solid fa-heart-pulse',
	);

	$terms = get_terms(
		array(
			'taxonomy'   => 'business_category',
			'hide_empty' => false,
            'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	?>

	<section class="business-directory-promo">
		<div class="container">
			<div class="business-directory-promo__inner">

				<div class="business-directory-promo__content">
					<h2 class="business-directory-promo__heading">
						Find trusted local businesses
					</h2>

					<p class="business-directory-promo__text">
						Only members appear in the directory, so you can choose with confidence.
					</p>

					<a class="business-directory-promo__button btn-shimmer" href="<?php echo esc_url( home_url( '/business-directory/' ) ); ?>">
						Browse all business listings
					</a>
				</div>

				<div class="business-directory-promo__categories">
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$icon_name  = get_field( 'icon_name', 'business_category_' . $term->term_id );
						$icon_name  = $icon_name ? sanitize_html_class( $icon_name ) : 'store';
						$icon_class = 'fa-solid fa-' . $icon_name;
						$term_link  = get_term_link( $term );
						?>

						<?php if ( ! is_wp_error( $term_link ) ) : ?>
							<a class="business-directory-promo__category-tile" href="<?php echo esc_url( $term_link ); ?>">
								<span><?php echo esc_html( $term->name ); ?></span>
								<i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>

			</div>
		</div>
	</section>

	<?php
	return ob_get_clean();
}
add_shortcode( 'business_directory_promo', 'cff_business_directory_promo_shortcode' );