<?php
/**
 * Single service.
 *
 * The page body is edited in the block editor; this template supplies the hero,
 * breadcrumb, and the "other services" grid around it.
 *
 * @package eight-fields
 */

get_header();

while ( have_posts() ) :
	the_post();

	ef_page_hero(
		array(
			'title'    => get_the_title(),
			'en'       => get_post_meta( get_the_ID(), 'ef_service_en', true ),
			'lead'     => get_the_excerpt(),
			'image_id' => get_post_thumbnail_id(),
		)
	);
	ef_breadcrumbs();
	?>

	<section class="ef-section">
		<div class="ef-container ef-container--narrow">
			<div class="ef-article" data-reveal>
				<?php the_content(); ?>
			</div>
			<div class="ef-actions ef-actions--center ef-mt-64">
				<a class="ef-btn ef-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php esc_html_e( 'この設備について相談する', 'eight-fields' ); ?>
				</a>
			</div>
		</div>
	</section>

	<?php
	$ef_others = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	if ( $ef_others ) :
		?>
		<section class="ef-section ef-section--sand">
			<div class="ef-container">
				<div class="ef-head" data-reveal>
					<span class="ef-eyebrow">Other services</span>
					<h2 class="ef-h2"><?php esc_html_e( 'ほかのサービス', 'eight-fields' ); ?></h2>
				</div>
				<div class="ef-grid ef-grid--3">
					<?php
					$ef_i = 0;
					foreach ( $ef_others as $ef_post ) :
						setup_postdata( $GLOBALS['post'] = $ef_post ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						++$ef_i;
						get_template_part( 'template-parts/card-service', null, array( 'no' => $ef_i ) );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;

endwhile;

get_footer();
