<?php
/**
 * Business Directory Loop.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

$business_search   = isset( $_GET['business_search'] ) ? sanitize_text_field( wp_unslash( $_GET['business_search'] ) ) : '';
$business_category = isset( $_GET['business_category'] ) ? sanitize_text_field( wp_unslash( $_GET['business_category'] ) ) : '';

if ( ! $business_category ) {
	$business_category = get_query_var( 'business_category_from_taxonomy' );
}

$business_suburb   = isset( $_GET['business_suburb'] ) ? sanitize_text_field( wp_unslash( $_GET['business_suburb'] ) ) : '';

$args = array(
	'post_type'      => 'business',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
	'orderby'        => 'title',
	'order'          => 'ASC',
);

if ( $business_search ) {
	$args['s'] = $business_search;
}

if ( $business_category ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'business_category',
			'field'    => 'slug',
			'terms'    => $business_category,
		),
	);
}

if ( $business_suburb ) {
	$args['meta_query'] = array(
		array(
			'key'     => 'suburb',
			'value'   => $business_suburb,
			'compare' => '=',
		),
	);
}

$business_query = new WP_Query( $args );
?>

<div class="business-directory-results">

	<div class="business-directory-results__count">
		<?php
		printf(
			esc_html( _n( '%s business found', '%s businesses found', $business_query->found_posts, 'understrap-child' ) ),
			esc_html( number_format_i18n( $business_query->found_posts ) )
		);
		?>
	</div>

	<?php if ( $business_query->have_posts() ) : ?>

		<div class="business-directory-results__list">
			<?php
			while ( $business_query->have_posts() ) :
				$business_query->the_post();

				get_template_part( 'global-templates/business-card' );

			endwhile;
			?>
		</div>

		<?php
		$pagination = paginate_links(
			array(
				'total'     => $business_query->max_num_pages,
				'current'   => $paged,
				'type'      => 'array',
				'prev_text' => __( '&laquo; Previous', 'understrap-child' ),
				'next_text' => __( 'Next &raquo;', 'understrap-child' ),
				'add_args'  => array_filter(
					array(
						'business_search'   => $business_search,
						'business_category' => $business_category,
						'business_suburb'   => $business_suburb,
					)
				),
			)
		);
		?>

		<?php if ( $pagination ) : ?>
			<nav class="business-directory-pagination" aria-label="<?php esc_attr_e( 'Business directory pagination', 'understrap-child' ); ?>">
				<ul class="pagination">
					<?php foreach ( $pagination as $page_link ) : ?>
						<li class="page-item">
							<?php echo wp_kses_post( str_replace( 'page-numbers', 'page-link', $page_link ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

	<?php else : ?>

		<div class="business-directory-results__empty">
			<h2>No businesses found</h2>
			<p>Try adjusting your filters or searching again.</p>
		</div>

	<?php endif; ?>

</div>

<?php wp_reset_postdata(); ?>