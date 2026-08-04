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
	$checkout_url = wc_get_checkout_url();

	$is_membership = has_term( 'membership', 'product_cat', $product_id );
	$is_event      = has_term( 'event', 'product_cat', $product_id );

	$button_text = __( 'Add to Cart', 'understrap-child' );

	if ( $is_membership ) {
		$button_text = __( 'Join Circle for Friends Now', 'understrap-child' );
	} elseif ( $is_event ) {
		$button_text = __( 'Reserve Your Seat', 'understrap-child' );
	}
	?>

	<form class="cart" action="<?php echo esc_url( $checkout_url ); ?>" method="post">

		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" />

		<?php if ( $is_event ) : ?>

			<div class="cff-event-quantity">
				<label for="quantity">
					<?php esc_html_e( 'Select Number of Tickets', 'understrap-child' ); ?>
				</label>

				<?php
				woocommerce_quantity_input(
					array(
						'min_value' => 1,
						'max_value' => 20,
						'input_value' => 1,
					)
				);
				?>
			</div>

			<?php cff_render_event_ticket_fields(); ?>

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
		<p><?php esc_html_e( 'Optional. Add attendee names and dietary requirements now, or leave blank if you do not know yet.', 'understrap-child' ); ?></p>

		<div class="cff-event-ticket-fields__list" data-cff-event-ticket-list></div>

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
remove_action( 'wp_enqueue_scripts', 'cff_enqueue_event_checkout_script' );

// Ensure only 1 members371616p can be purchased.
add_filter(
	'woocommerce_is_sold_individually',
	function( $sold_individually, $product ) {

		if ( has_term( 'membership', 'product_cat', $product->get_id() ) ) {
			return true;
		}

		return false;
	},
	10,
	2
);

add_filter( 'woocommerce_add_to_cart_redirect', function () {
	return wc_get_checkout_url();
} );

// Additional membership product checkout fields.

/**
 * Check if the cart contains a membership product.
 */
function cff_cart_contains_membership_product() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;

		if ( $product_id && has_term( 'membership', 'product_cat', $product_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add checkout body class so we can hide membership-only fields for events.
 */
add_filter(
	'body_class',
	function( $classes ) {
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
			$classes[] = cff_cart_contains_membership_product()
				? 'cff-cart-has-membership'
				: 'cff-cart-has-no-membership';
		}

		return $classes;
	}
);

/**
 * Add membership fields to WooCommerce Blocks checkout.
 */
add_action(
	'woocommerce_init',
	function() {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}

		woocommerce_register_additional_checkout_field(
			array(
				'id'       => 'cff/member-title',
				'label'    => __( 'Title', 'understrap-child' ),
				'location' => 'address',
				'type'     => 'select',
				'required' => false,
				'index'    => 1,
				'options'  => array(
					array(
						'value' => '',
						'label' => __( 'Select a title', 'understrap-child' ),
					),
					array(
						'value' => 'Mr',
						'label' => __( 'Mr', 'understrap-child' ),
					),
					array(
						'value' => 'Mrs',
						'label' => __( 'Mrs', 'understrap-child' ),
					),
					array(
						'value' => 'Miss',
						'label' => __( 'Miss', 'understrap-child' ),
					),
					array(
						'value' => 'Ms',
						'label' => __( 'Ms', 'understrap-child' ),
					),
				),
			)
		);

		woocommerce_register_additional_checkout_field(
			array(
				'id'       => 'cff/occupation',
				'label'    => __( 'Occupation', 'understrap-child' ),
				'location' => 'order',
				'type'     => 'text',
				'required' => false,
			)
		);

		woocommerce_register_additional_checkout_field(
			array(
				'id'       => 'cff/place-of-birth',
				'label'    => __( 'Place of birth/Region', 'understrap-child' ),
				'location' => 'order',
				'type'     => 'text',
				'required' => false,
			)
		);

		woocommerce_register_additional_checkout_field(
			array(
				'id'         => 'cff/date-of-birth',
				'label'      => __( 'Date of birth (dd/mm/yyyy)', 'understrap-child' ),
				'location'   => 'order',
				'type'       => 'text',
				'required'   => false,
				'attributes' => array(
					'placeholder' => 'dd/mm/yyyy',
					'pattern'     => '(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([0-9]{4})',
					'maxlength'   => '10',
					'inputmode'   => 'numeric',
				),
			)
		);

	}
);

/**
 * Validate membership fields only when purchasing a membership.
 */
add_action(
	'woocommerce_validate_additional_field',
	function( WP_Error $errors, $field_key, $field_value ) {
		if ( ! cff_cart_contains_membership_product() ) {
			return;
		}

		$required_fields = array(
			'cff/member-title'    => __( 'Please select your title.', 'understrap-child' ),
			'cff/occupation'      => __( 'Please enter your occupation.', 'understrap-child' ),
			'cff/place-of-birth'  => __( 'Please enter your place of birth.', 'understrap-child' ),
			'cff/date-of-birth'   => __( 'Please enter your date of birth.', 'understrap-child' ),
		);

		if ( isset( $required_fields[ $field_key ] ) && empty( $field_value ) ) {
			$errors->add(
				'cff_required_' . sanitize_key( str_replace( '/', '_', $field_key ) ),
				$required_fields[ $field_key ]
			);
		}

		if ( 'cff/date-of-birth' === $field_key && ! empty( $field_value ) ) {
			$valid_date = preg_match(
				'/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([0-9]{4})$/',
				$field_value
			);

			if ( ! $valid_date ) {
				$errors->add(
					'cff_invalid_date_of_birth',
					__( 'Please enter your date of birth in dd/mm/yyyy format.', 'understrap-child' )
				);
			}
		}
	},
	10,
	3
);

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
