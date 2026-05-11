<?php
/**
 * Single Business Template.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

get_header();

$container = 'container';

$business_id       = get_the_ID();
$short_description = get_field( 'short_description', $business_id );
$phone_number      = get_field( 'phone_number', $business_id );
$email_address     = get_field( 'email_address', $business_id );
$website_url       = get_field( 'website_url', $business_id );
$suburb            = get_field( 'suburb', $business_id );
$state             = get_field( 'state', $business_id );
$logo_override     = get_field( 'logo_override', $business_id );

$categories = get_the_terms( $business_id, 'business_category' );
$category   = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0] : null;

$category_name = $category ? $category->name : '';
$category_link = $category ? get_term_link( $category ) : '';

$tel_link = $phone_number ? preg_replace( '/[^0-9+]/', '', $phone_number ) : '';
?>

<main id="primary" class="site-main single-business">

	<?php while ( have_posts() ) : the_post(); ?>

		<section class="single-business__hero">
			<div class="<?php echo esc_attr( $container ); ?>">

				<div class="single-business__breadcrumbs">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'business' ) ); ?>">Directory</a>
					<?php if ( $category && ! is_wp_error( $category_link ) ) : ?>
						<span>→</span>
						<a href="<?php echo esc_url( $category_link ); ?>"><?php echo esc_html( $category_name ); ?></a>
					<?php endif; ?>
					<span>→</span>
					<span><?php the_title(); ?></span>
				</div>

				<span class="single-business__verified">Verified member</span>

				<h1 class="single-business__title"><?php the_title(); ?></h1>

				<?php if ( $category_name ) : ?>
					<span class="single-business__category"><?php echo esc_html( $category_name ); ?></span>
				<?php endif; ?>

				<?php if ( $short_description ) : ?>
					<p class="single-business__summary">
						<?php echo esc_html( $short_description ); ?>
					</p>
				<?php endif; ?>

				<div class="single-business__logo-row">
                    <div class="single-business__logo">
                        <?php if ( $logo_override && ! empty( $logo_override['ID'] ) ) : ?>
                            <?php echo wp_get_attachment_image( $logo_override['ID'], 'thumbnail', false, array( 'class' => 'single-business__logo-img' ) ); ?>
                        <?php elseif ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'thumbnail', array( 'class' => 'single-business__logo-img' ) ); ?>
                        <?php else : ?>
                            <span>LOGO</span>
                        <?php endif; ?>
                    </div>
                </div>

			</div>
		</section>

		<section class="single-business__body">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row g-4">

					<div class="col-12 col-lg-8">

						<article class="single-business-card single-business-card--large">
							<h2>About this business</h2>

							<?php if ( get_the_content() ) : ?>
								<?php the_content(); ?>
							<?php else : ?>
								<p><?php the_title(); ?> is a trusted Circle for Friends member business.</p>
								<?php if ( $short_description ) : ?>
									<p><?php echo esc_html( $short_description ); ?></p>
								<?php endif; ?>
								<p>More information about this business will be available soon.</p>
							<?php endif; ?>
						</article>

						<section class="single-business-card">
							<h2>Services</h2>

							<div class="single-business__service-list">
								<span>Customer support</span>
								<span>Business consultation</span>
								<span>Local services</span>
								<span>Member support</span>
								<span>Quotes available</span>
								<span>Professional advice</span>
							</div>

							<h3>Gallery</h3>

							<div class="single-business__gallery">
								<div></div>
								<div></div>
								<div></div>
							</div>
						</section>

					</div>

					<aside class="col-12 col-lg-4">

						<section class="single-business-card single-business-card--contact">
							<h2>Contact</h2>

							<?php if ( $phone_number ) : ?>
								<div class="single-business__contact-item">
									<span>Phone</span>
									<a href="tel:<?php echo esc_attr( $tel_link ); ?>">
										<?php echo esc_html( $phone_number ); ?>
									</a>
								</div>
							<?php endif; ?>

							<?php if ( $email_address ) : ?>
								<div class="single-business__contact-item">
									<span>Email</span>
									<a href="mailto:<?php echo esc_attr( antispambot( $email_address ) ); ?>">
										<?php echo esc_html( antispambot( $email_address ) ); ?>
									</a>
								</div>
							<?php endif; ?>

							<?php if ( $website_url ) : ?>
								<div class="single-business__contact-item">
									<span>Website</span>
									<a href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener">
										<?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $website_url ) ) ); ?>
									</a>
								</div>
							<?php endif; ?>

							<?php if ( $suburb || $state ) : ?>
								<div class="single-business__contact-item">
									<span>Location</span>
									<p><?php echo esc_html( trim( $suburb . ', ' . $state, ', ' ) ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( $email_address ) : ?>
								<a class="single-business__button btn-shimmer" href="mailto:<?php echo esc_attr( antispambot( $email_address ) ); ?>">
									Request a quote
								</a>
							<?php endif; ?>

							<?php if ( $phone_number ) : ?>
								<a class="single-business__button single-business__button--outline" href="tel:<?php echo esc_attr( $tel_link ); ?>">
									Call now
								</a>
							<?php endif; ?>
						</section>

						<section class="single-business-card single-business-card--accent-green">
							<h2>Opening hours</h2>
							<p>Mon–Fri: 8:00am – 6:00pm</p>
							<p>Sat: 9:00am – 1:00pm</p>
							<p>Sun: Closed</p>
							<p class="single-business__muted">After-hours availability may vary.</p>
						</section>

						

					</aside>

				</div>
			</div>
		</section>

	<?php endwhile; ?>

</main>

<?php
get_footer();