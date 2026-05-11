<?php
/**
 * Footer full-width area.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

$container = 'container';
?>

<footer class="site-footer-cff" role="contentinfo">
	<div class="<?php echo esc_attr( $container ); ?>">

		<div class="row g-4 align-items-start">

			<div class="col-12 col-lg-5">
				<h2 class="site-footer-cff__brand">Circle for Friends</h2>

				<p class="site-footer-cff__text">
					Building a trusted circle for community, business connection and support.
				</p>
			</div>

			<div class="col-12 col-md-6 col-lg-3">
				<h3>Contact</h3>

				<ul class="site-footer-cff__list">
					<li>
						<a href="mailto:info@circleforfriends.com.au">
							info@circleforfriends.com.au
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">
							Contact us
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/business-directory/' ) ); ?>">
							Business directory
						</a>
					</li>
				</ul>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<a 
					class="site-footer-cff__club"
					href="https://abruzzoclub.com.au/"
					target="_blank"
					rel="noopener"
				>
					<img
						class="site-footer-cff__club-logo"
						src="<?php echo esc_url( home_url( '/wp-content/uploads/2026/05/abruzzo-logo-white-200x63-1.png' ) ); ?>"
						alt="Abruzzo Club Logo"
					>

					<p>
						Circle for Friends proudly works in association with the Abruzzo Club to support connection, community and shared opportunity.
					</p>
				</a>
			</div>

		</div>

		<div class="site-footer-cff__bottom">
			<p>
				&copy; <?php echo esc_html( date( 'Y' ) ); ?> Circle for Friends. All rights reserved.
			</p>

			<p>
				Website by
				<a href="https://kasio99.com" target="_blank" rel="noopener">
					Kasio99 Website Development
				</a>
			</p>
		</div>

	</div>
</footer>