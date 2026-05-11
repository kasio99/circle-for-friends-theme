<?php
/**
 * Business Directory Filters.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

$current_search   = isset( $_GET['business_search'] ) ? sanitize_text_field( wp_unslash( $_GET['business_search'] ) ) : '';
$current_category = isset( $_GET['business_category'] ) ? sanitize_text_field( wp_unslash( $_GET['business_category'] ) ) : '';
$current_suburb   = isset( $_GET['business_suburb'] ) ? sanitize_text_field( wp_unslash( $_GET['business_suburb'] ) ) : '';

$categories = get_terms(
	array(
		'taxonomy'   => 'business_category',
		'hide_empty' => false,
		'parent'     => 0,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$suburbs_query = new WP_Query(
	array(
		'post_type'      => 'business',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

$suburbs = array();

if ( $suburbs_query->have_posts() ) {
	foreach ( $suburbs_query->posts as $business_id ) {
		$suburb = get_field( 'suburb', $business_id );

		if ( $suburb ) {
			$suburbs[] = trim( $suburb );
		}
	}
}

$suburbs = array_unique( array_filter( $suburbs ) );
sort( $suburbs );
?>

<form class="business-directory-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'business' ) ); ?>">

	<div class="row g-3 align-items-end">

		<div class="col-12 col-lg-4">
			<label class="form-label" for="business-search">Search</label>
			<input
				id="business-search"
				class="form-control"
				type="search"
				name="business_search"
				value="<?php echo esc_attr( $current_search ); ?>"
				placeholder="Search by business name..."
			>
		</div>

		<div class="col-12 col-md-6 col-lg-3">
			<label class="form-label" for="business-category">Category</label>
			<select id="business-category" class="form-select" name="business_category">
				<option value="">All categories</option>

				<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $current_category, $category->slug ); ?>>
							<?php echo esc_html( $category->name ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>

		<div class="col-12 col-md-6 col-lg-3">
			<label class="form-label" for="business-suburb">Suburb</label>
			<select id="business-suburb" class="form-select" name="business_suburb">
				<option value="">All suburbs</option>

				<?php foreach ( $suburbs as $suburb ) : ?>
					<option value="<?php echo esc_attr( $suburb ); ?>" <?php selected( $current_suburb, $suburb ); ?>>
						<?php echo esc_html( $suburb ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-12 col-lg-2">
			<button class="business-directory-filters__button btn-shimmer" type="submit">
				Filter
			</button>
		</div>

	</div>

	<?php if ( $current_search || $current_category || $current_suburb ) : ?>
		<div class="business-directory-filters__reset">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'business' ) ); ?>">
				<i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
				Reset filters
			</a>
		</div>
	<?php endif; ?>

</form>