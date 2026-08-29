<?php
/**
 * Template Name: ごあいさつ
 *
 * Matches the fixed design for /greeting/: the representative's message beside
 * the portrait, then the three values. Picked up automatically for a page with
 * the slug `greeting`.
 *
 * The message body comes from the page's own editor content, so it can be
 * rewritten without touching this file; the heading and signature are drawn
 * from the Customizer's company details.
 *
 * @package eight-fields
 */

get_header();

while ( have_posts() ) :
	the_post();

	ef_page_hero(
		array(
			'title'    => get_the_title(),
			'en'       => get_post_meta( get_the_ID(), 'ef_page_en', true ),
			'lead'     => get_post_meta( get_the_ID(), 'ef_page_lead', true ),
			'image_id' => get_post_thumbnail_id(),
		)
	);
	ef_breadcrumbs();

	$ef_ceo     = ef_info( 'ceo', '金山 準' );
	$ef_name    = get_bloginfo( 'name' );
	$ef_heading = get_post_meta( get_the_ID(), 'ef_message_title', true );
	?>

	<section class="ef-section">
		<div class="ef-container">
			<div class="ef-split">
				<div class="ef-split__media" data-reveal>
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
					<?php else : ?>
						<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/ceo.jpg' ) ); ?>"
							alt="<?php echo esc_attr( sprintf( '%1$s 代表取締役社長 %2$s', $ef_name, $ef_ceo ) ); ?>"
							loading="lazy" decoding="async">
					<?php endif; ?>
					<span class="ef-split__badge"><b><?php esc_html_e( 'ご縁', 'eight-fields' ); ?></b><span><?php esc_html_e( 'を大切に', 'eight-fields' ); ?></span></span>
				</div>
				<div data-reveal data-reveal-delay="1">
					<span class="ef-eyebrow">Message</span>
					<h2 class="ef-h2">
						<?php
						if ( $ef_heading ) {
							echo wp_kses( $ef_heading, array( 'br' => array() ) );
						} else {
							esc_html_e( '全てのご縁を大切に、', 'eight-fields' );
							echo '<br>';
							esc_html_e( '日々精進して参ります。', 'eight-fields' );
						}
						?>
					</h2>
					<div class="ef-article ef-mt-32">
						<?php the_content(); ?>
					</div>
					<div class="ef-signature">
						<small><?php echo esc_html( $ef_name ); ?></small>
						<small><?php esc_html_e( '代表取締役社長', 'eight-fields' ); ?></small>
						<b><?php echo esc_html( $ef_ceo ); ?></b>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ef-section ef-section--sand">
		<div class="ef-container">
			<div class="ef-head ef-head--center" data-reveal>
				<span class="ef-eyebrow">Our stance</span>
				<h2 class="ef-h2"><?php esc_html_e( '私たちが大切にしていること', 'eight-fields' ); ?></h2>
			</div>
			<?php get_template_part( 'template-parts/cards-feature', null, array( 'items' => ef_values() ) ); ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
