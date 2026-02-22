<?php
/**
 * Elementor Widget: Google Reviews.
 *
 * @package Google_Reviews_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Class GRW_Elementor_Widget
 */
class GRW_Elementor_Widget extends Widget_Base {

	/**
	 * Widget name (slug).
	 *
	 * @return string
	 */
	public function get_name() {
		return 'grw_google_reviews';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Google Reviews', GRW_TEXT_DOMAIN );
	}

	/**
	 * Widget icon (Elementor icon class).
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-google';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Widget keywords for search.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'google', 'reviews', 'rating', 'places' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ── Content section ──────────────────────────────────────────────────
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', GRW_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'api_key',
			[
				'label'       => esc_html__( 'Google API Key', GRW_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Uses global setting if empty', GRW_TEXT_DOMAIN ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'place_id',
			[
				'label'       => esc_html__( 'Google Place ID', GRW_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Uses global setting if empty', GRW_TEXT_DOMAIN ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'num_reviews',
			[
				'label'   => esc_html__( 'Number of Reviews', GRW_TEXT_DOMAIN ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 5,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->add_control(
			'min_rating',
			[
				'label'   => esc_html__( 'Minimum Rating (stars)', GRW_TEXT_DOMAIN ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 5,
				'step'    => 1,
				'default' => 1,
			]
		);

		$this->end_controls_section();

		// ── Style section: card ───────────────────────────────────────────────
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Review Card', GRW_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'     => esc_html__( 'Card Background', GRW_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .grw-review-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', GRW_TEXT_DOMAIN ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 40 ],
					'%'  => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .grw-review-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style section: typography ─────────────────────────────────────────
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography', GRW_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'author_typography',
				'label'    => esc_html__( 'Author Name', GRW_TEXT_DOMAIN ),
				'selector' => '{{WRAPPER}} .grw-author-name',
			]
		);

		$this->add_control(
			'author_color',
			[
				'label'     => esc_html__( 'Author Color', GRW_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .grw-author-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'review_text_typography',
				'label'    => esc_html__( 'Review Text', GRW_TEXT_DOMAIN ),
				'selector' => '{{WRAPPER}} .grw-review-text',
			]
		);

		$this->add_control(
			'review_text_color',
			[
				'label'     => esc_html__( 'Review Text Color', GRW_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .grw-review-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'star_color',
			[
				'label'     => esc_html__( 'Star Color (filled)', GRW_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .grw-star--filled' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$api_key     = ! empty( $settings['api_key'] )  ? sanitize_text_field( $settings['api_key'] )  : get_option( 'grw_api_key', '' );
		$place_id    = ! empty( $settings['place_id'] ) ? sanitize_text_field( $settings['place_id'] ) : get_option( 'grw_place_id', '' );
		$num_reviews = isset( $settings['num_reviews'] ) ? min( 5, max( 1, (int) $settings['num_reviews'] ) ) : 5;
		$min_rating  = isset( $settings['min_rating'] )  ? min( 5, max( 1, (int) $settings['min_rating'] ) )  : 1;

		$reviews = grw_get_reviews( $api_key, $place_id );

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

		echo '</div>';
	}

	/**
	 * Render widget output in the Elementor editor (live preview).
	 */
	protected function content_template() {
		?>
		<div class="grw-reviews-container grw-elementor-preview">
			<div class="grw-review-card">
				<div class="grw-review-header">
					<div class="grw-author-avatar">
						<span class="grw-author-initials">G</span>
					</div>
					<div class="grw-author-info">
						<span class="grw-author-name">Google User</span>
						<span class="grw-review-date">January 1, 2025</span>
					</div>
				</div>
				<div class="grw-stars">
					<span class="grw-star grw-star--filled">&#9733;</span>
					<span class="grw-star grw-star--filled">&#9733;</span>
					<span class="grw-star grw-star--filled">&#9733;</span>
					<span class="grw-star grw-star--filled">&#9733;</span>
					<span class="grw-star grw-star--filled">&#9733;</span>
				</div>
				<p class="grw-review-text"><?php echo esc_html__( 'This is an example review. Your actual reviews will appear here once the widget is configured with a valid API key and Place ID.', GRW_TEXT_DOMAIN ); ?></p>
			</div>
		</div>
		<?php
	}
}
