<?php
/**
 * Plugin Name: Google Reviews Widget
 * Plugin URI:  https://github.com/galanakisbm/googlereviews
 * Description: Displays Google reviews via the Google Places API with caching, filtering, and Elementor support.
 * Version:     1.0.0
 * Author:      galanakisbm
 * Author URI:  https://github.com/galanakisbm
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: google-reviews-widget
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GRW_VERSION',     '1.0.0' );
define( 'GRW_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'GRW_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'GRW_TEXT_DOMAIN', 'google-reviews-widget' );

// ─── i18n ────────────────────────────────────────────────────────────────────
add_action( 'init', 'grw_load_textdomain' );
function grw_load_textdomain() {
	load_plugin_textdomain(
		GRW_TEXT_DOMAIN,
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

// ─── Enqueue front-end assets ─────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'grw_enqueue_styles' );
function grw_enqueue_styles() {
	wp_enqueue_style(
		'google-reviews-widget',
		GRW_PLUGIN_URL . 'assets/css/google-reviews-widget.css',
		[],
		GRW_VERSION
	);
}

// ─── Register standard widget ─────────────────────────────────────────────────
add_action( 'widgets_init', 'grw_register_widget' );
function grw_register_widget() {
	require_once GRW_PLUGIN_DIR . 'includes/class-google-reviews-widget.php';
	register_widget( 'GRW_Widget' );
}

// ─── Register Elementor widget ────────────────────────────────────────────────
add_action( 'elementor/widgets/register', 'grw_register_elementor_widget' );
function grw_register_elementor_widget( $widgets_manager ) {
	require_once GRW_PLUGIN_DIR . 'includes/class-google-reviews-elementor.php';
	$widgets_manager->register( new GRW_Elementor_Widget() );
}

// ─── Admin settings page ──────────────────────────────────────────────────────
add_action( 'admin_menu', 'grw_admin_menu' );
function grw_admin_menu() {
	add_options_page(
		esc_html__( 'Google Reviews Settings', GRW_TEXT_DOMAIN ),
		esc_html__( 'Google Reviews', GRW_TEXT_DOMAIN ),
		'manage_options',
		'google-reviews-widget',
		'grw_settings_page'
	);
}

add_action( 'admin_init', 'grw_register_settings' );
function grw_register_settings() {
	register_setting(
		'grw_settings_group',
		'grw_api_key',
		[
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		]
	);
	register_setting(
		'grw_settings_group',
		'grw_place_id',
		[
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		]
	);

	add_settings_section(
		'grw_main_section',
		esc_html__( 'API Configuration', GRW_TEXT_DOMAIN ),
		null,
		'google-reviews-widget'
	);

	add_settings_field(
		'grw_api_key',
		esc_html__( 'Google API Key', GRW_TEXT_DOMAIN ),
		'grw_field_api_key',
		'google-reviews-widget',
		'grw_main_section'
	);
	add_settings_field(
		'grw_place_id',
		esc_html__( 'Google Place ID', GRW_TEXT_DOMAIN ),
		'grw_field_place_id',
		'google-reviews-widget',
		'grw_main_section'
	);
}

function grw_field_api_key() {
	$value = esc_attr( get_option( 'grw_api_key', '' ) );
	echo '<input type="text" id="grw_api_key" name="grw_api_key" value="' . $value . '" class="regular-text" />';
	echo '<p class="description">' . esc_html__( 'Your Google Cloud Places API key.', GRW_TEXT_DOMAIN ) . '</p>';
}

function grw_field_place_id() {
	$value = esc_attr( get_option( 'grw_place_id', '' ) );
	echo '<input type="text" id="grw_place_id" name="grw_place_id" value="' . $value . '" class="regular-text" />';
	echo '<p class="description">' . esc_html__( 'The unique Place ID for your business (e.g. ChIJN1t_tDeuEmsRUsoyG83frY4).', GRW_TEXT_DOMAIN ) . '</p>';
}

function grw_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Google Reviews Widget Settings', GRW_TEXT_DOMAIN ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'grw_settings_group' );
			do_settings_sections( 'google-reviews-widget' );
			submit_button();
			?>
		</form>
		<hr>
		<h2><?php echo esc_html__( 'Clear Cache', GRW_TEXT_DOMAIN ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( 'grw_clear_cache', 'grw_cache_nonce' ); ?>
			<input type="hidden" name="grw_action" value="clear_cache" />
			<?php submit_button( esc_html__( 'Clear Reviews Cache', GRW_TEXT_DOMAIN ), 'secondary' ); ?>
		</form>
	</div>
	<?php
}

// Handle cache-clear form submission.
add_action( 'admin_init', 'grw_handle_cache_clear' );
function grw_handle_cache_clear() {
	if (
		isset( $_POST['grw_action'] ) &&
		'clear_cache' === $_POST['grw_action'] &&
		isset( $_POST['grw_cache_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['grw_cache_nonce'] ) ), 'grw_clear_cache' ) &&
		current_user_can( 'manage_options' )
	) {
		grw_delete_all_transients();
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-success is-dismissible"><p>' .
				esc_html__( 'Google Reviews cache cleared.', GRW_TEXT_DOMAIN ) .
				'</p></div>';
		} );
	}
}

// ─── Google Places API helper ─────────────────────────────────────────────────

/**
 * Fetch reviews from the Google Places API (or from transient cache).
 *
 * @param string $api_key  Google Places API key.
 * @param string $place_id Google Place ID.
 * @return array|WP_Error  Array of review objects or WP_Error on failure.
 */
function grw_get_reviews( $api_key, $place_id ) {
	if ( empty( $api_key ) || empty( $place_id ) ) {
		return new WP_Error( 'missing_credentials', __( 'API key or Place ID is missing.', GRW_TEXT_DOMAIN ) );
	}

	$transient_key = 'grw_reviews_' . md5( $api_key . $place_id );
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$url = add_query_arg(
		[
			'place_id' => rawurlencode( $place_id ),
			'fields'   => 'reviews',
			'key'      => $api_key,
			'language' => get_locale(),
		],
		'https://maps.googleapis.com/maps/api/place/details/json'
	);

	$response = wp_remote_get(
		$url,
		[
			'timeout'   => 10,
			'sslverify' => true,
		]
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( empty( $data ) || ! isset( $data['result']['reviews'] ) ) {
		return new WP_Error( 'api_error', __( 'Could not retrieve reviews from Google Places API.', GRW_TEXT_DOMAIN ) );
	}

	$reviews = $data['result']['reviews'];
	set_transient( $transient_key, $reviews, 12 * HOUR_IN_SECONDS );

	return $reviews;
}

/**
 * Delete all GRW transients.
 */
function grw_delete_all_transients() {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_grw_%' OR option_name LIKE '_transient_timeout_grw_%'"
	);
}

/**
 * Filter and limit review array.
 *
 * @param array $reviews     Raw reviews from API.
 * @param int   $num_reviews Maximum number to return.
 * @param int   $min_rating  Minimum star rating.
 * @return array
 */
function grw_filter_reviews( array $reviews, $num_reviews = 5, $min_rating = 1 ) {
	$filtered = array_filter(
		$reviews,
		function ( $review ) use ( $min_rating ) {
			return isset( $review['rating'] ) && (int) $review['rating'] >= (int) $min_rating;
		}
	);

	return array_slice( array_values( $filtered ), 0, (int) $num_reviews );
}

/**
 * Render a review card.
 *
 * @param array $review Review array from the Places API.
 * @return string HTML output.
 */
function grw_render_review( array $review ) {
	$author = isset( $review['author_name'] ) ? esc_html( $review['author_name'] ) : '';
	$rating = isset( $review['rating'] )      ? (int) $review['rating']            : 0;
	$text   = isset( $review['text'] )        ? esc_html( $review['text'] )        : '';
	$time   = isset( $review['time'] )        ? (int) $review['time']              : 0;
	$photo  = isset( $review['profile_photo_url'] ) ? esc_url( $review['profile_photo_url'] ) : '';

	$stars_html = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$class       = $i <= $rating ? 'grw-star grw-star--filled' : 'grw-star grw-star--empty';
		$stars_html .= '<span class="' . esc_attr( $class ) . '" aria-hidden="true">&#9733;</span>';
	}

	$date_str = $time ? esc_html( date_i18n( get_option( 'date_format' ), $time ) ) : '';

	$avatar_html = '';
	if ( $photo ) {
		$avatar_html = '<img class="grw-author-photo" src="' . $photo . '" alt="' . $author . '" loading="lazy" width="40" height="40" />';
	} else {
		$avatar_html = '<span class="grw-author-initials" aria-hidden="true">' . esc_html( mb_substr( $author, 0, 1 ) ) . '</span>';
	}

	ob_start();
	?>
	<div class="grw-review-card">
		<div class="grw-review-header">
			<div class="grw-author-avatar"><?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="grw-author-info">
				<span class="grw-author-name"><?php echo $author; ?></span>
				<?php if ( $date_str ) : ?>
					<span class="grw-review-date"><?php echo $date_str; ?></span>
				<?php endif; ?>
			</div>
		</div>
		<div class="grw-stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', GRW_TEXT_DOMAIN ), $rating ) ); ?>">
			<?php echo $stars_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $text ) : ?>
			<p class="grw-review-text"><?php echo $text; ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
