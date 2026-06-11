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
					<?php esc_html_e( 'Number of tickets', 'understrap-child' ); ?>
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

// Ensure only 1 membership can be purchased.
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

//force australia region for all purchases

/**
 * Force WooCommerce default country to Australia.
 */
add_filter( 'woocommerce_get_country_locale_default', function( $locale ) {
	return 'AU';
} );

/**
 * Force checkout billing/shipping country to Australia.
 */
add_filter( 'default_checkout_billing_country', function() {
	return 'AU';
} );

add_filter( 'default_checkout_shipping_country', function() {
	return 'AU';
} );

/**
 * Only allow Australia.
 */
add_filter( 'woocommerce_countries_allowed_countries', function() {
	return array(
		'AU' => 'Australia',
	);
} );

add_filter( 'woocommerce_countries_shipping_countries', function() {
	return array(
		'AU' => 'Australia',
	);
} );


//additional membership product checkout fields
/**
 * Add membership fields to WooCommerce Blocks checkout.
 */
add_action( 'woocommerce_init', function() {

	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}

	woocommerce_register_additional_checkout_field(
	array(
		'id'       => 'cff/member-title',
		'label'    => __( 'Title', 'understrap-child' ),
		'location' => 'address',
		'type'     => 'select',
		'required' => true,
		'index'    => 1,
		'options'  => array(
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
			'required' => true,
		)
	);

	woocommerce_register_additional_checkout_field(
		array(
			'id'       => 'cff/place-of-birth',
			'label'    => __( 'Place of birth/Region', 'understrap-child' ),
			'location' => 'order',
			'type'     => 'text',
			'required' => true,
		)
	);

	woocommerce_register_additional_checkout_field (
		array(
			'id'       => 'cff/date-of-birth',
			'label'    => __( 'Date of birth (dd/mm/yyyy)', 'understrap-child' ),
			'location' => 'order',
			'type'     => 'text',
			'required' => true,
			'attributes' => array(
				'placeholder' => 'dd/mm/yyyy',
				'pattern'     => '(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([0-9]{4})',
				'maxlength'   => '10',
				'inputmode'   => 'numeric',
			),
		)
	);

});


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