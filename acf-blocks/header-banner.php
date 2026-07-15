<?php
/**
 * Header Banner Block Template.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

// ACF fields.
$eyebrow_text = get_field( 'eyebrow_text' );
$heading      = get_field( 'heading' );
$sub_heading  = get_field( 'sub_heading' );
$button       = get_field( 'button' );
$banner_image = get_field( 'banner_image' );

// Block settings.
$block_id = 'header-banner-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$block_id = $block['anchor'];
}

$class_name = 'acf-header-banner';
if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$class_name .= ' align' . $block['align'];
}
?>

<section id="<?php echo esc_attr( $block_id ); ?>" class="<?php echo esc_attr( $class_name ); ?>">
	<div class="container">
		<div class="acf-header-banner__inner row align-items-center">

			<div class="acf-header-banner__content col-12 col-lg-6">
				<?php if ( $eyebrow_text ) : ?>
					<p class="acf-header-banner__eyebrow">
						<?php echo esc_html( $eyebrow_text ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h1 class="acf-header-banner__heading">
						<?php echo esc_html( $heading ); ?>
					</h1>
				<?php endif; ?>

				<?php if ( $sub_heading ) : ?>
					<div class="acf-header-banner__sub-heading">
						<?php echo wp_kses_post( nl2br( $sub_heading ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $button && ! empty( $button['url'] ) && ! empty( $button['title'] ) ) : ?>
					<div class="acf-header-banner__button-wrap">
						<a
							class="acf-header-banner__button btn btn-primary"
							href="<?php echo esc_url( $button['url'] ); ?>"
							target="<?php echo ! empty( $button['target'] ) ? esc_attr( $button['target'] ) : '_self'; ?>"
						>
							<?php echo esc_html( $button['title'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<div class="acf-header-banner__image-col col-12 col-lg-6">
				<?php if ( $banner_image ) : ?>
					<?php
					$image_url = is_array( $banner_image ) && ! empty( $banner_image['url'] ) ? $banner_image['url'] : '';
					$image_alt = is_array( $banner_image ) && ! empty( $banner_image['alt'] ) ? $banner_image['alt'] : '';
					$image_id  = is_array( $banner_image ) && ! empty( $banner_image['ID'] ) ? (int) $banner_image['ID'] : 0;
					?>

					<div class="acf-header-banner__image-wrap">
						<?php if ( $image_id ) : ?>
							<?php echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => 'acf-header-banner__image img-fluid' ) ); ?>
						<?php elseif ( $image_url ) : ?>
							<img
								class="acf-header-banner__image img-fluid"
								src="<?php echo esc_url( $image_url ); ?>"
								alt="<?php echo esc_attr( $image_alt ); ?>"
							>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>