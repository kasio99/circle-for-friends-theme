<?php
/**
 * Understrap Child Theme functions and definitions
 *
 * @package UnderstrapChild
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



/**
 * Removes the parent themes stylesheet and scripts from inc/enqueue.php
 */
function understrap_remove_scripts() {
	wp_dequeue_style( 'understrap-styles' );
	wp_deregister_style( 'understrap-styles' );

	wp_dequeue_script( 'understrap-scripts' );
	wp_deregister_script( 'understrap-scripts' );
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );



/**
 * Enqueue our stylesheet and javascript file
 */
function theme_enqueue_styles() {

	// Get the theme data.
	$the_theme = wp_get_theme();

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	// Grab asset urls.
	$theme_styles  = "/css/child-theme{$suffix}.css";
	$theme_scripts = "/js/child-theme{$suffix}.js";

	wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . $theme_styles, array(), $the_theme->get( 'Version' ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . $theme_scripts, array(), $the_theme->get( 'Version' ), true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function cff_enqueue_fontawesome_kit() {
	wp_enqueue_script(
		'fontawesome-kit',
		'https://kit.fontawesome.com/782da41165.js',
		array(),
		null,
		false
	);
}
add_action( 'wp_enqueue_scripts', 'cff_enqueue_fontawesome_kit' );

function cff_fontawesome_script_attributes( $tag, $handle, $src ) {
	if ( 'fontawesome-kit' === $handle ) {
		return '<script src="' . esc_url( $src ) . '" crossorigin="anonymous"></script>' . "\n";
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'cff_fontawesome_script_attributes', 10, 3 );

/**
 * Enqueue event ticket attendee fields script.
 */
function cff_enqueue_event_ticket_script() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$product_id = get_queried_object_id();

	if ( ! $product_id || ! has_term( 'event', 'product_cat', $product_id ) ) {
		return;
	}

	wp_enqueue_script(
		'cff-event-tickets',
		get_stylesheet_directory_uri() . '/js/cff-event-tickets.js',
		array(),
		filemtime( get_stylesheet_directory() . '/js/cff-event-tickets.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cff_enqueue_event_ticket_script' );

/**
 * Enqueue membership person fields script.
 */
function cff_enqueue_membership_details_script() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$product_id = get_queried_object_id();

	if ( ! $product_id || ! has_term( 'membership', 'product_cat', $product_id ) ) {
		return;
	}

	wp_enqueue_script(
		'cff-membership-details',
		get_stylesheet_directory_uri() . '/js/cff-membership-details.js',
		array(),
		filemtime( get_stylesheet_directory() . '/js/cff-membership-details.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cff_enqueue_membership_details_script' );



/**
 * Load the child theme's text domain
 */
function add_child_theme_textdomain() {
	load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );



/**
 * Overrides the theme_mod to default to Bootstrap 5
 *
 * This function uses the `theme_mod_{$name}` hook and
 * can be duplicated to override other theme settings.
 *
 * @return string
 */
function understrap_default_bootstrap_version() {
	return 'bootstrap5';
}
add_filter( 'theme_mod_understrap_bootstrap_version', 'understrap_default_bootstrap_version', 20 );



/**
 * Loads javascript for showing customizer warning dialog.
 */
function understrap_child_customize_controls_js() {
	wp_enqueue_script(
		'understrap_child_customizer',
		get_stylesheet_directory_uri() . '/js/customizer-controls.js',
		array( 'customize-preview' ),
		'20130508',
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'understrap_child_customize_controls_js' );

// Save ACF field groups to theme.
add_filter('acf/settings/save_json', function ($path) {
	return get_stylesheet_directory() . '/acf-json';
});

// Load ACF field groups from theme.
add_filter('acf/settings/load_json', function ($paths) {
	unset($paths[0]); // remove default path
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
});

add_action('acf/init', function () {
	if (! function_exists('acf_register_block_type')) {
		return;
	}

	acf_register_block_type(array(
		'name'            => 'header-banner',
		'title'           => __('Header Banner', 'understrap'),
		'description'     => __('A custom header banner block.', 'understrap'),
		'render_template' => get_stylesheet_directory() . '/acf-blocks/header-banner.php',
		'category'        => 'formatting',
		'icon'            => 'cover-image',
		'keywords'        => array('banner', 'header', 'hero'),
		'mode'            => 'edit',
		'supports'        => array(
			'align' => true,
			'jsx'   => true,
		),
	));
});

//post type setup found here
require_once get_stylesheet_directory() . '/inc/post-types-taxonomies.php';
//shortcodes found here
require_once get_stylesheet_directory() . '/inc/shortcodes.php';

//block editor support for products
function cff_enable_block_editor_for_products( $can_edit, $post_type ) {
	if ( 'product' === $post_type ) {
		return true;
	}

	return $can_edit;
}
add_filter( 'use_block_editor_for_post_type', 'cff_enable_block_editor_for_products', 10, 2 );

// Replace WooCommerce add to cart button with custom CTA.
add_action( 'wp', function () {

	// Remove default Woo button.
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

	// Add custom button.
	add_action( 'woocommerce_single_product_summary', 'cff_custom_add_to_cart_button', 30 );
});

function cff_custom_add_to_cart_button() {
	global $product;

	if ( ! $product || ! $product->is_purchasable() ) {
		return;
	}

	$product_id   = $product->get_id();
	$cart_url = wc_get_cart_url();

	$is_membership = has_term( 'membership', 'product_cat', $product_id );
	$is_event      = has_term( 'event', 'product_cat', $product_id );

	$button_text = __( 'Add to Cart', 'understrap-child' );

	if ( $is_membership ) {
		$button_text = __( 'Join Circle for Friends Now', 'understrap-child' );
	} elseif ( $is_event ) {
		$button_text = __( 'Reserve Your Seat', 'understrap-child' );
	}
	?>

	<form class="cart" action="<?php echo esc_url( $cart_url ); ?>" method="post">

		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" />

		<?php if ( $is_event ) : ?>
			<div class="cff-event-purchase-options">
				<div class="cff-event-purchase-options__row">
					<div class="cff-event-quantity">
						<label for="quantity"><?php esc_html_e( 'Select Number of Tickets', 'understrap-child' ); ?></label>

						<?php
						woocommerce_quantity_input(
							array(
								'min_value'   => 1,
								'max_value'   => 20,
								'input_value' => 1,
							)
						);
						?>
					</div>

					<label class="cff-event-purchase-options__check">
						<input type="checkbox" data-cff-buy-table />
						<span><?php esc_html_e( 'Buy a Table of 10', 'understrap-child' ); ?></span>
					</label>
				</div>

				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: contact us link. */
							__( 'Corporate sponsorship tables are available. Please %s to discuss sponsorship opportunities.', 'understrap-child' ),
							array(
								'a' => array(
									'href' => array(),
								),
							)
						),
						'<a href="' . esc_url( home_url( '/contact-us' ) ) . '">' . esc_html__( 'contact us', 'understrap-child' ) . '</a>'
					);
					?>
				</p>
			</div>
		<?php elseif ( $is_membership ) : ?>
			<div class="cff-event-quantity">
				<label for="quantity"><?php esc_html_e( 'Select Number of Memberships', 'understrap-child' ); ?></label>

				<?php
				woocommerce_quantity_input(
					array(
						'min_value'   => 1,
						'max_value'   => 20,
						'input_value' => 1,
					)
				);
				?>
			</div>

		<?php endif; ?>

		<?php if ( $is_event ) : ?>

			<?php cff_render_event_ticket_fields(); ?>

		<?php elseif ( $is_membership ) : ?>

			<?php cff_render_membership_person_fields(); ?>

		<?php endif; ?>

		<button
			type="submit"
			class="single_add_to_cart_button btn btn-primary nav-cta btn-shimmer"
		>
			<?php echo esc_html( $button_text ); ?>
		</button>

	</form>

	<?php
}

/**
 * Dietary requirement options for event ticket attendees.
 */
function cff_get_dietary_requirement_options() {
	return array(
		'vegetarian'  => __( 'Vegetarian', 'understrap-child' ),
		'vegan'       => __( 'Vegan', 'understrap-child' ),
		'gluten-free' => __( 'Gluten free', 'understrap-child' ),
		'dairy-free'  => __( 'Dairy free', 'understrap-child' ),
		'nut-allergy' => __( 'Nut allergy', 'understrap-child' ),
		'other'       => __( 'Other', 'understrap-child' ),
	);
}

/**
 * Render optional per-ticket attendee fields on event product pages.
 */
function cff_render_event_ticket_fields() {
	$options = cff_get_dietary_requirement_options();
	?>

	<div class="cff-event-ticket-fields" data-cff-event-ticket-fields data-max-tickets="20">
		<h3><?php esc_html_e( 'Ticket details', 'understrap-child' ); ?></h3>
		<label class="cff-event-ticket-fields__toggle">
			<input type="checkbox" data-cff-event-ticket-toggle />
			<span><?php esc_html_e( 'Add attendee names and dietary requirements now', 'understrap-child' ); ?></span>
		</label>
		<p><?php esc_html_e( 'If you do not know the attendee details and/or dietary requirements, please email us 2 weeks prior to the event. The details will be on your purchase receipt.', 'understrap-child' ); ?></p>

		<div class="cff-event-ticket-fields__list" data-cff-event-ticket-list hidden></div>

		<template data-cff-event-ticket-template>
			<div class="cff-event-ticket" data-cff-event-ticket>
				<h4 data-cff-ticket-heading><?php esc_html_e( 'Ticket', 'understrap-child' ); ?></h4>

				<label>
					<span><?php esc_html_e( 'Attendee name', 'understrap-child' ); ?></span>
					<input type="text" data-cff-ticket-name placeholder="<?php esc_attr_e( 'Optional', 'understrap-child' ); ?>" />
				</label>

				<fieldset>
					<legend><?php esc_html_e( 'Dietary requirements', 'understrap-child' ); ?></legend>

					<?php foreach ( $options as $value => $label ) : ?>
						<label class="cff-event-ticket__check">
							<input type="checkbox" data-cff-ticket-dietary value="<?php echo esc_attr( $value ); ?>" />
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<label class="cff-event-ticket__other" data-cff-ticket-other-wrap hidden>
					<span><?php esc_html_e( 'Please specify', 'understrap-child' ); ?></span>
					<input type="text" data-cff-ticket-other placeholder="<?php esc_attr_e( 'Optional', 'understrap-child' ); ?>" />
				</label>
			</div>
		</template>
	</div>

	<?php
}

/**
 * Sanitise optional event ticket attendee data posted from the product form.
 */
function cff_get_event_ticket_details_from_product_post() {
	if ( empty( $_POST['cff_event_tickets'] ) || ! is_array( $_POST['cff_event_tickets'] ) ) {
		return array();
	}

	$allowed_dietary_options = array_keys( cff_get_dietary_requirement_options() );
	$quantity                = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
	$quantity                = min( 20, $quantity );
	$posted_tickets          = wp_unslash( $_POST['cff_event_tickets'] );
	$tickets                 = array();

	for ( $index = 0; $index < $quantity; $index++ ) {
		$posted_ticket = isset( $posted_tickets[ $index ] ) && is_array( $posted_tickets[ $index ] )
			? $posted_tickets[ $index ]
			: array();

		$name    = isset( $posted_ticket['name'] ) ? sanitize_text_field( $posted_ticket['name'] ) : '';
		$other   = isset( $posted_ticket['other'] ) ? sanitize_text_field( $posted_ticket['other'] ) : '';
		$dietary = array();

		if ( isset( $posted_ticket['dietary'] ) && is_array( $posted_ticket['dietary'] ) ) {
			foreach ( $posted_ticket['dietary'] as $dietary_value ) {
				$dietary_value = sanitize_key( $dietary_value );

				if ( in_array( $dietary_value, $allowed_dietary_options, true ) ) {
					$dietary[] = $dietary_value;
				}
			}
		}

		$dietary = array_values( array_unique( $dietary ) );
		$other   = in_array( 'other', $dietary, true ) ? $other : '';

		if ( '' === $name && empty( $dietary ) && '' === $other ) {
			continue;
		}

		$tickets[] = array(
			'ticket_number' => $index + 1,
			'name'          => $name,
			'dietary'       => $dietary,
			'other'         => $other,
		);
	}

	return $tickets;
}

/**
 * Add optional event ticket attendee details to the cart item.
 */
function cff_add_event_ticket_details_to_cart_item( $cart_item_data, $product_id ) {
	if ( ! has_term( 'event', 'product_cat', $product_id ) ) {
		return $cart_item_data;
	}

	$tickets = cff_get_event_ticket_details_from_product_post();

	if ( empty( $tickets ) ) {
		return $cart_item_data;
	}

	$cart_item_data['cff_event_tickets']      = $tickets;
	$cart_item_data['cff_event_tickets_hash'] = md5( wp_json_encode( $tickets ) );

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'cff_add_event_ticket_details_to_cart_item', 10, 2 );

/**
 * Format event ticket details for cart/order display.
 */
function cff_format_event_ticket_details( $tickets ) {
	$options = cff_get_dietary_requirement_options();
	$lines   = array();

	foreach ( $tickets as $ticket ) {
		$parts = array();

		if ( ! empty( $ticket['name'] ) ) {
			$parts[] = sprintf(
				/* translators: %s: attendee name. */
				__( 'Name: %s', 'understrap-child' ),
				$ticket['name']
			);
		}

		if ( ! empty( $ticket['dietary'] ) ) {
			$dietary_labels = array();

			foreach ( $ticket['dietary'] as $dietary_value ) {
				$dietary_labels[] = isset( $options[ $dietary_value ] ) ? $options[ $dietary_value ] : $dietary_value;
			}

			if ( ! empty( $ticket['other'] ) && in_array( 'other', $ticket['dietary'], true ) ) {
				$dietary_labels[] = $ticket['other'];
			}

			$parts[] = sprintf(
				/* translators: %s: dietary requirements. */
				__( 'Dietary: %s', 'understrap-child' ),
				implode( ', ', $dietary_labels )
			);
		}

		if ( empty( $parts ) ) {
			continue;
		}

		$lines[] = sprintf(
			/* translators: 1: ticket number, 2: ticket details. */
			__( 'Ticket %1$d - %2$s', 'understrap-child' ),
			(int) $ticket['ticket_number'],
			implode( '; ', $parts )
		);
	}

	return implode( "\n", $lines );
}

/**
 * Show event ticket details against the cart item.
 */
function cff_show_event_ticket_details_in_cart( $item_data, $cart_item ) {
	if ( empty( $cart_item['cff_event_tickets'] ) || ! is_array( $cart_item['cff_event_tickets'] ) ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'     => __( 'Ticket details', 'understrap-child' ),
		'value'   => nl2br( esc_html( cff_format_event_ticket_details( $cart_item['cff_event_tickets'] ) ) ),
		'display' => nl2br( esc_html( cff_format_event_ticket_details( $cart_item['cff_event_tickets'] ) ) ),
	);

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'cff_show_event_ticket_details_in_cart', 10, 2 );

/**
 * Render required per-person membership fields on membership product pages.
 */
function cff_render_membership_person_fields() {
	?>

	<div class="cff-membership-person-fields" data-cff-membership-fields data-max-memberships="20">
		<h3><?php esc_html_e( 'Membership details', 'understrap-child' ); ?></h3>
		<p><?php esc_html_e( 'Each membership must belong to a specific person.', 'understrap-child' ); ?></p>

		<div class="cff-membership-person-fields__list" data-cff-membership-list></div>

		<template data-cff-membership-template>
			<div class="cff-membership-person" data-cff-membership-person>
				<h4 data-cff-membership-heading><?php esc_html_e( 'Membership', 'understrap-child' ); ?></h4>

				<label>
					<span><?php esc_html_e( 'Name', 'understrap-child' ); ?></span>
					<input type="text" data-cff-membership-name required />
				</label>

				<label>
					<span><?php esc_html_e( 'Occupation', 'understrap-child' ); ?></span>
					<input type="text" data-cff-membership-occupation required />
				</label>

				<label>
					<span><?php esc_html_e( 'Date of birth (dd/mm/yyyy)', 'understrap-child' ); ?></span>
					<input type="text" data-cff-membership-date-of-birth placeholder="dd/mm/yyyy" pattern="(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([0-9]{4})" maxlength="10" inputmode="numeric" required />
				</label>

				<label>
					<span><?php esc_html_e( 'Place of birth/Region', 'understrap-child' ); ?></span>
					<input type="text" data-cff-membership-place-of-birth required />
				</label>
			</div>
		</template>
	</div>

	<?php
}

/**
 * Sanitise required membership details posted from the product form.
 */
function cff_get_membership_details_from_product_post( $quantity ) {
	if ( empty( $_POST['cff_memberships'] ) || ! is_array( $_POST['cff_memberships'] ) ) {
		return array();
	}

	$quantity           = min( 20, max( 1, absint( $quantity ) ) );
	$posted_memberships = wp_unslash( $_POST['cff_memberships'] );
	$memberships        = array();

	for ( $index = 0; $index < $quantity; $index++ ) {
		$posted_membership = isset( $posted_memberships[ $index ] ) && is_array( $posted_memberships[ $index ] )
			? $posted_memberships[ $index ]
			: array();

		$memberships[] = array(
			'membership_number' => $index + 1,
			'name'              => isset( $posted_membership['name'] ) ? sanitize_text_field( $posted_membership['name'] ) : '',
			'occupation'        => isset( $posted_membership['occupation'] ) ? sanitize_text_field( $posted_membership['occupation'] ) : '',
			'date_of_birth'     => isset( $posted_membership['date_of_birth'] ) ? sanitize_text_field( $posted_membership['date_of_birth'] ) : '',
			'place_of_birth'    => isset( $posted_membership['place_of_birth'] ) ? sanitize_text_field( $posted_membership['place_of_birth'] ) : '',
		);
	}

	return $memberships;
}

/**
 * Validate required membership details before adding to cart.
 */
function cff_validate_membership_details_before_add_to_cart( $passed, $product_id, $quantity ) {
	if ( ! has_term( 'membership', 'product_cat', $product_id ) ) {
		return $passed;
	}

	$memberships = cff_get_membership_details_from_product_post( $quantity );

	if ( count( $memberships ) < absint( $quantity ) ) {
		wc_add_notice( __( 'Please enter details for each membership.', 'understrap-child' ), 'error' );
		return false;
	}

	foreach ( $memberships as $membership ) {
		$membership_number = isset( $membership['membership_number'] ) ? absint( $membership['membership_number'] ) : 0;

		if (
			empty( $membership['name'] )
			|| empty( $membership['occupation'] )
			|| empty( $membership['date_of_birth'] )
			|| empty( $membership['place_of_birth'] )
		) {
			wc_add_notice(
				sprintf(
					/* translators: %d: membership number. */
					__( 'Please complete all fields for membership %d.', 'understrap-child' ),
					$membership_number
				),
				'error'
			);
			$passed = false;
		}

		if (
			! empty( $membership['date_of_birth'] )
			&& ! preg_match( '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([0-9]{4})$/', $membership['date_of_birth'] )
		) {
			wc_add_notice(
				sprintf(
					/* translators: %d: membership number. */
					__( 'Please enter the date of birth for membership %d in dd/mm/yyyy format.', 'understrap-child' ),
					$membership_number
				),
				'error'
			);
			$passed = false;
		}
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'cff_validate_membership_details_before_add_to_cart', 10, 3 );

/**
 * Add required membership details to the cart item.
 */
function cff_add_membership_details_to_cart_item( $cart_item_data, $product_id, $variation_id, $quantity ) {
	if ( ! has_term( 'membership', 'product_cat', $product_id ) ) {
		return $cart_item_data;
	}

	$memberships = cff_get_membership_details_from_product_post( $quantity );

	if ( empty( $memberships ) ) {
		return $cart_item_data;
	}

	$cart_item_data['cff_memberships']      = $memberships;
	$cart_item_data['cff_memberships_hash'] = md5( wp_json_encode( $memberships ) );

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'cff_add_membership_details_to_cart_item', 10, 4 );

/**
 * Format membership details for cart display.
 */
function cff_format_membership_details( $memberships ) {
	$lines = array();

	foreach ( $memberships as $membership ) {
		$lines[] = sprintf(
			/* translators: 1: membership number, 2: name, 3: occupation, 4: date of birth, 5: place of birth. */
			__( 'Membership %1$d - Name: %2$s; Occupation: %3$s; DOB: %4$s; Place of birth/Region: %5$s', 'understrap-child' ),
			(int) $membership['membership_number'],
			$membership['name'],
			$membership['occupation'],
			$membership['date_of_birth'],
			$membership['place_of_birth']
		);
	}

	return implode( "\n", $lines );
}

/**
 * Show membership details against the cart item.
 */
function cff_show_membership_details_in_cart( $item_data, $cart_item ) {
	if ( empty( $cart_item['cff_memberships'] ) || ! is_array( $cart_item['cff_memberships'] ) ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'     => __( 'Membership details', 'understrap-child' ),
		'value'   => nl2br( esc_html( cff_format_membership_details( $cart_item['cff_memberships'] ) ) ),
		'display' => nl2br( esc_html( cff_format_membership_details( $cart_item['cff_memberships'] ) ) ),
	);

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'cff_show_membership_details_in_cart', 10, 2 );

/**
 * Check if the cart contains an event-category product.
 */
function cff_cart_contains_event_product() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;

		if ( $product_id && has_term( 'event', 'product_cat', $product_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get event products currently in the cart.
 */
function cff_get_cart_event_items() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}

	$event_items = array();

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;

		if ( ! $product_id || ! has_term( 'event', 'product_cat', $product_id ) ) {
			continue;
		}

		$product = wc_get_product( $product_id );

		$event_items[] = array(
			'cart_item_key' => $cart_item_key,
			'product_id'     => $product_id,
			'product_name'   => $product ? $product->get_name() : __( 'Event ticket', 'understrap-child' ),
			'quantity'       => isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1,
		);
	}

	return $event_items;
}

/**
 * Check if an order contains an event-category product.
 *
 * @param WC_Order $order Order object.
 */
function cff_order_contains_event_product( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return false;
	}

	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();

		if ( $product_id && has_term( 'event', 'product_cat', $product_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get any attendee details posted for an event ticket line item.
 *
 * Kept for classic checkout fallback. The live checkout page uses Checkout Blocks.
 */
function cff_get_event_ticket_details_from_post( $cart_item_key ) {
	if ( isset( $_POST['cff_event_ticket_details'] ) && is_array( $_POST['cff_event_ticket_details'] ) ) {
		$value = isset( $_POST['cff_event_ticket_details'][ $cart_item_key ] ) ? wp_unslash( $_POST['cff_event_ticket_details'][ $cart_item_key ] ) : '';
		return sanitize_textarea_field( $value );
	}

	return '';
}

/**
 * Render optional attendee fields on checkout when event products are in the cart.
 */
function cff_render_event_attendee_fields() {
	if ( ! cff_cart_contains_event_product() ) {
		return;
	}

	$event_items = cff_get_cart_event_items();
	if ( empty( $event_items ) ) {
		return;
	}
	?>

	<div class="cff-event-ticket-details">
		<h3><?php esc_html_e( 'Ticket attendee details', 'understrap-child' ); ?></h3>
		<p class="small text-muted">
			<?php esc_html_e( 'Optional. Leave blank if you do not know attendee names yet. Add one line per ticket, for example: Jane Doe — no nuts.', 'understrap-child' ); ?>
		</p>

		<?php foreach ( $event_items as $item ) : ?>
			<div class="cff-event-ticket-detail-field" style="margin-bottom: 1rem;">
				<label for="cff_event_ticket_details_<?php echo esc_attr( $item['cart_item_key'] ); ?>">
					<?php echo esc_html( sprintf( __( 'Attendee details for %s', 'understrap-child' ), $item['product_name'] ) ); ?>
				</label>
				<textarea
					id="cff_event_ticket_details_<?php echo esc_attr( $item['cart_item_key'] ); ?>"
					name="cff_event_ticket_details[<?php echo esc_attr( $item['cart_item_key'] ); ?>]"
					rows="4"
					placeholder="<?php esc_attr_e( 'Optional. Example: Ticket 1 — Jane Doe, no nuts', 'understrap-child' ); ?>"
				></textarea>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
}
add_action( 'woocommerce_checkout_before_order_review', 'cff_render_event_attendee_fields' );
// Also render before payment block for classic templates that output order review/payment separately.
add_action( 'woocommerce_review_order_before_payment', 'cff_render_event_attendee_fields' );

/**
 * Enqueue frontend script to inject attendee fields on Block or classic checkout.
 */
function cff_enqueue_event_checkout_script() {
	return;

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	wp_enqueue_script(
		'cff-event-checkout',
		get_stylesheet_directory_uri() . '/js/cff-event-checkout.js',
		array(),
		filemtime( get_stylesheet_directory() . '/js/cff-event-checkout.js' ),
		true
	);

	$items = cff_get_cart_event_items();
	$data = array(
		'event_items' => $items,
	);

	wp_add_inline_script( 'cff-event-checkout', 'window._cff_event_checkout_data = ' . wp_json_encode( $data ) . ';' );
}
add_action( 'wp_enqueue_scripts', 'cff_enqueue_event_checkout_script' );

/**
 * Save optional attendee details to the event order line item.
 */
function cff_save_event_ticket_details_to_order_item( $item, $cart_item_key, $values, $order ) {
	if ( empty( $values['cff_event_tickets'] ) || ! is_array( $values['cff_event_tickets'] ) ) {
		return;
	}

	$options = cff_get_dietary_requirement_options();

	foreach ( $values['cff_event_tickets'] as $ticket ) {
		$ticket_number = isset( $ticket['ticket_number'] ) ? absint( $ticket['ticket_number'] ) : 0;

		if ( ! $ticket_number ) {
			continue;
		}

		if ( ! empty( $ticket['name'] ) ) {
			$item->add_meta_data(
				sprintf(
					/* translators: %d: ticket number. */
					__( 'Ticket %d attendee', 'understrap-child' ),
					$ticket_number
				),
				$ticket['name'],
				true
			);
		}

		if ( empty( $ticket['dietary'] ) ) {
			continue;
		}

		$dietary_labels = array();

		foreach ( $ticket['dietary'] as $dietary_value ) {
			$dietary_labels[] = isset( $options[ $dietary_value ] ) ? $options[ $dietary_value ] : $dietary_value;
		}

		if ( ! empty( $ticket['other'] ) && in_array( 'other', $ticket['dietary'], true ) ) {
			$dietary_labels[] = $ticket['other'];
		}

		$item->add_meta_data(
			sprintf(
				/* translators: %d: ticket number. */
				__( 'Ticket %d dietary requirements', 'understrap-child' ),
				$ticket_number
			),
			implode( ', ', $dietary_labels ),
			true
		);
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'cff_save_event_ticket_details_to_order_item', 10, 4 );

/**
 * Save required membership details to the membership order line item.
 */
function cff_save_membership_details_to_order_item( $item, $cart_item_key, $values, $order ) {
	if ( empty( $values['cff_memberships'] ) || ! is_array( $values['cff_memberships'] ) ) {
		return;
	}

	foreach ( $values['cff_memberships'] as $membership ) {
		$membership_number = isset( $membership['membership_number'] ) ? absint( $membership['membership_number'] ) : 0;

		if ( ! $membership_number ) {
			continue;
		}

		$fields = array(
			'name'           => __( 'name', 'understrap-child' ),
			'occupation'     => __( 'occupation', 'understrap-child' ),
			'date_of_birth'  => __( 'date of birth', 'understrap-child' ),
			'place_of_birth' => __( 'place of birth/region', 'understrap-child' ),
		);

		foreach ( $fields as $field_key => $field_label ) {
			if ( empty( $membership[ $field_key ] ) ) {
				continue;
			}

			$item->add_meta_data(
				sprintf(
					/* translators: 1: membership number, 2: field label. */
					__( 'Membership %1$d %2$s', 'understrap-child' ),
					$membership_number,
					$field_label
				),
				$membership[ $field_key ],
				true
			);
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'cff_save_membership_details_to_order_item', 10, 4 );
remove_action( 'wp_enqueue_scripts', 'cff_enqueue_event_checkout_script' );

// Allow membership quantities so details can be captured for each person.
add_filter(
	'woocommerce_is_sold_individually',
	function( $sold_individually, $product ) {

		if ( has_term( 'membership', 'product_cat', $product->get_id() ) ) {
			return false;
		}

		return $sold_individually;
	},
	10,
	2
);

add_filter( 'woocommerce_add_to_cart_redirect', function () {
	return wc_get_cart_url();
} );

add_filter( 'woocommerce_get_privacy_policy_url', function( $url ) {
	return 'https://circleforfriends.com.au/wp-content/uploads/2026/05/privacy-policy.pdf';
});


//google tags

function kasio_is_production_site() {
    return $_SERVER['HTTP_HOST'] === 'www.circleforfriends.com.au' || $_SERVER['HTTP_HOST'] === 'circleforfriends.com.au';
}

add_action('wp_head', function () {
    if (!kasio_is_production_site()) {
        return;
    }
    ?>
    <!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-WDVVKBK2');</script>
	<!-- End Google Tag Manager -->
    <?php
}, 1);

add_action('wp_body_open', function () {
    if (!kasio_is_production_site()) {
        return;
    }
    ?>
    <!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WDVVKBK2"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
    <?php
}, 1);

//move event products straight to complete.

add_filter( 'woocommerce_payment_complete_order_status', function( $status, $order_id, $order = null ) {

    $event_category = 'event'; // ← category slug (see note below)

    if ( ! $order ) {
        $order = wc_get_order( $order_id );
    }
    if ( ! $order ) return $status;

    // Only auto-complete if every item in the order is in the Events category
    foreach ( $order->get_items() as $item ) {
        if ( ! has_term( $event_category, 'product_cat', $item->get_product_id() ) ) {
            return $status;
        }
    }

    return 'completed';

}, 10, 3 );

/**
 * Circle for Friends – event ticket version of the "Completed order" email
 */

// Check whether every item in the order is in the Events category
function cff_is_event_order( $order ) {
    $event_category = 'event'; // ← category slug
    if ( ! $order instanceof WC_Order ) return false;
    $items = $order->get_items();
    if ( empty( $items ) ) return false;
    foreach ( $items as $item ) {
        if ( ! has_term( $event_category, 'product_cat', $item->get_product_id() ) ) return false;
    }
    return true;
}

// 1. Subject line
add_filter( 'woocommerce_email_subject_customer_completed_order', function( $subject, $order ) {
    if ( ! cff_is_event_order( $order ) ) return $subject;
    $items = $order->get_items();
    if ( count( $items ) === 1 ) {
        $item = reset( $items );
        return 'Booking confirmed: ' . $item->get_name();
    }
    return 'Your Circle for Friends event booking is confirmed';
}, 10, 2 );

// 2. Heading
add_filter( 'woocommerce_email_heading_customer_completed_order', function( $heading, $order ) {
    return cff_is_event_order( $order ) ? 'You’re booked in!' : $heading;
}, 10, 2 );

// 3. Intro text (only while an event "Completed order" email is being built)
add_action( 'woocommerce_email_header', function( $heading, $email = null ) {
    $GLOBALS['cff_event_email'] = $email && 'customer_completed_order' === $email->id && cff_is_event_order( $email->object );
}, 1, 2 );

add_action( 'woocommerce_email_footer', function() {
    $GLOBALS['cff_event_email'] = false;
}, 99 );

add_filter( 'gettext', function( $translation, $text, $domain ) {
    if ( 'woocommerce' !== $domain || empty( $GLOBALS['cff_event_email'] ) ) return $translation;
    switch ( $text ) {
        case 'We have finished processing your order.':
            return 'Thank you for your booking – your place is confirmed. This email is your proof of purchase. No tickets will be sent out, so please keep it for your records.';
        case 'Here’s a reminder of what you’ve ordered:':
        case "Here's a reminder of what you've ordered:":
            return 'Here are your booking details:';
    }
    return $translation;
}, 10, 3 );

// 4. Attendee & dietary requirements box (below the order summary)
add_action( 'woocommerce_email_after_order_table', function( $order, $sent_to_admin, $plain_text, $email ) {
    if ( $sent_to_admin || 'customer_completed_order' !== $email->id || ! cff_is_event_order( $order ) ) return;

    if ( $plain_text ) {
        echo "\nATTENDEE NAMES & DIETARY REQUIREMENTS\n"
           . "If you didn't provide the names of your guests or any dietary requirements when booking, please email info@circleforfriends.com.au before the event's RSVP date. You'll find the RSVP date on your invitation or on the event page on our website.\n\n";
        return;
    }

    echo '<div style="margin:24px 0; padding:16px 20px; background:#f5f7fb; border-left:4px solid #22388c;">'
       . '<p style="margin:0 0 8px;"><strong>Attendee names &amp; dietary requirements</strong></p>'
       . '<p style="margin:0;">If you didn’t provide the names of your guests or any dietary requirements when booking, please email '
       . '<a href="mailto:info@circleforfriends.com.au" style="color:#22388c;">info@circleforfriends.com.au</a> '
       . 'before the event’s RSVP date. You’ll find the RSVP date on your invitation or on the event page on our website.</p>'
       . '</div>';
}, 10, 4 );
