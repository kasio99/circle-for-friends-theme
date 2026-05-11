<?php
/**
 * Business Card Template Part.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

$business_id = get_the_ID();

$short_description = get_field( 'short_description', $business_id );
$phone_number      = get_field( 'phone_number', $business_id );
$email_address     = get_field( 'email_address', $business_id );
$suburb            = get_field( 'suburb', $business_id );
$state             = get_field( 'state', $business_id );
$logo_override     = get_field( 'logo_override', $business_id );

$categories = get_the_terms( $business_id, 'business_category' );
$category   = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0] : null;

$category_name = $category ? $category->name : '';
$category_slug = $category ? $category->slug : 'default';

//$card_classes = 'business-card business-card--' . sanitize_html_class( $category_slug );

$card_classes = 'business-card';
?>

<article <?php post_class( $card_classes ); ?>>

	<div class="business-card__accent" aria-hidden="true"></div>

	<div class="business-card__inner">

		<div class="business-card__logo">
			<?php if ( $logo_override && ! empty( $logo_override['ID'] ) ) : ?>
				<?php echo wp_get_attachment_image( $logo_override['ID'], 'thumbnail', false, array( 'class' => 'business-card__logo-img' ) ); ?>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'business-card__logo-img' ) ); ?>
			<?php else : ?>
				<span class="business-card__logo-placeholder">Logo</span>
			<?php endif; ?>
		</div>

		<div class="business-card__content">

			<header class="business-card__header">
				<h3 class="business-card__title">
					<a href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>

				<?php if ( $category_name ) : ?>
					<span class="business-card__category">
						<?php echo esc_html( $category_name ); ?>
					</span>
				<?php endif; ?>
			</header>

			<?php if ( $short_description ) : ?>
				<p class="business-card__description">
					<?php echo esc_html( $short_description ); ?>
				</p>
			<?php endif; ?>

			<div class="business-card__meta">
				<?php if ( $suburb || $state ) : ?>
					<span>
						<?php echo esc_html( trim( $suburb . ', ' . $state, ', ' ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( $phone_number ) : ?>
					<span>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone_number ) ); ?>">
							<?php echo esc_html( $phone_number ); ?>
						</a>
					</span>
				<?php endif; ?>

				<?php if ( $email_address ) : ?>
					<span>
						<a href="mailto:<?php echo esc_attr( antispambot( $email_address ) ); ?>">
							<?php echo esc_html( antispambot( $email_address ) ); ?>
						</a>
					</span>
				<?php endif; ?>
			</div>

		</div>

		<div class="business-card__action">
			<a class="business-card__button btn-shimmer" href="<?php the_permalink(); ?>">
				Learn more
			</a>
		</div>

	</div>

</article>