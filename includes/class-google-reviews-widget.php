<?php
/**
 * Standard WordPress Widget: Google Reviews.
 *
 * @package Google_Reviews_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GRW_Widget
 */
class GRW_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'grw_widget',
			esc_html__( 'Google Reviews', GRW_TEXT_DOMAIN ),
			[
				'description' => esc_html__( 'Displays Google reviews from the Places API.', GRW_TEXT_DOMAIN ),
				'classname'   => 'grw-widget',
			]
		);
	}

	/**
	 * Front-end display.
	 *
	 * @param array $args     Widget area arguments.
	 * @param array $instance Saved widget settings.
	 */
	public function widget( $args, $instance ) {
		$title       = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$api_key     = isset( $instance['api_key'] ) ? $instance['api_key'] : get_option( 'grw_api_key', '' );
		$place_id    = isset( $instance['place_id'] ) ? $instance['place_id'] : get_option( 'grw_place_id', '' );
		$num_reviews = isset( $instance['num_reviews'] ) ? (int) $instance['num_reviews'] : 5;
		$min_rating  = isset( $instance['min_rating'] ) ? (int) $instance['min_rating'] : 1;

		$reviews = grw_get_reviews( $api_key, $place_id );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<div class="grw-reviews-container">';

		if ( is_wp_error( $reviews ) ) {
			echo '<p class="grw-error">' . esc_html( $reviews->get_error_message() ) . '</p>';
		} else {
			$filtered = grw_filter_reviews( $reviews, $num_reviews, $min_rating );

			if ( empty( $filtered ) ) {
				echo '<p class="grw-no-reviews">' . esc_html__( 'No reviews found.', GRW_TEXT_DOMAIN ) . '</p>';
			} else {
				foreach ( $filtered as $review ) {
					echo grw_render_review( $review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		echo '</div>'; // .grw-reviews-container

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved settings.
	 */
	public function form( $instance ) {
		$title       = isset( $instance['title'] )       ? $instance['title']             : esc_html__( 'Google Reviews', GRW_TEXT_DOMAIN );
		$api_key     = isset( $instance['api_key'] )     ? $instance['api_key']           : '';
		$place_id    = isset( $instance['place_id'] )    ? $instance['place_id']          : '';
		$num_reviews = isset( $instance['num_reviews'] ) ? (int) $instance['num_reviews'] : 5;
		$min_rating  = isset( $instance['min_rating'] )  ? (int) $instance['min_rating']  : 1;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', GRW_TEXT_DOMAIN ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'api_key' ) ); ?>">
				<?php esc_html_e( 'Google API Key (overrides global setting):', GRW_TEXT_DOMAIN ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'api_key' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'api_key' ) ); ?>"
				type="text" value="<?php echo esc_attr( $api_key ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'place_id' ) ); ?>">
				<?php esc_html_e( 'Google Place ID (overrides global setting):', GRW_TEXT_DOMAIN ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'place_id' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'place_id' ) ); ?>"
				type="text" value="<?php echo esc_attr( $place_id ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'num_reviews' ) ); ?>">
				<?php esc_html_e( 'Number of reviews (1–5):', GRW_TEXT_DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'num_reviews' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'num_reviews' ) ); ?>">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $num_reviews, $i ); ?>>
						<?php echo esc_html( $i ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'min_rating' ) ); ?>">
				<?php esc_html_e( 'Minimum rating (stars):', GRW_TEXT_DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'min_rating' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'min_rating' ) ); ?>">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $min_rating, $i ); ?>>
						<?php echo esc_html( $i ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array Sanitized settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = [];
		$instance['title']       = sanitize_text_field( $new_instance['title'] );
		$instance['api_key']     = sanitize_text_field( $new_instance['api_key'] );
		$instance['place_id']    = sanitize_text_field( $new_instance['place_id'] );
		$instance['num_reviews'] = min( 5, max( 1, (int) $new_instance['num_reviews'] ) );
		$instance['min_rating']  = min( 5, max( 1, (int) $new_instance['min_rating'] ) );
		return $instance;
	}
}
