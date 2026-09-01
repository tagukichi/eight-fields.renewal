<?php
/**
 * Single service.
 *
 * Matches the fixed design: an intro beside the photo, up to three merits, the
 * steps to installation, FAQ, and the other services.
 *
 * The body copy is the post content; everything else comes from the
 * 「サービスページの内容」 panel in the editor. Sections with nothing filled in
 * are skipped rather than rendered empty.
 *
 * @package eight-fields
 */

get_header();

while ( have_posts() ) :
	the_post();

	$ef_id    = get_the_ID();

	// No hero band on a service page: the photograph belongs beside the opening
	// copy below, and the title is set there too.
	ef_breadcrumbs();
	?>

	<?php
	// The opening block: the service photograph, and beside it the name, the
	// catch line and the lead. The copy comes from the site's own WYSIWYG field
	// when there is one, so the editor edits it in the box they already use.
	$ef_intro = ef_service_intro( $ef_id );
	$ef_media = has_post_thumbnail();
	?>

	<!-- INTRO -->
	<section class="ef-section">
		<div class="ef-container">
			<div class="ef-intro<?php echo $ef_media ? '' : ' ef-intro--solo'; ?>">
				<?php if ( $ef_media ) : ?>
					<div class="ef-intro__media" data-reveal>
						<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async', 'alt' => get_the_title() ) ); ?>
					</div>
				<?php endif; ?>

				<div class="ef-intro__body" data-reveal data-reveal-delay="1">
					<h1 class="ef-intro__name"><?php the_title(); ?></h1>

					<?php if ( $ef_intro['html'] ) : ?>
						<div class="ef-intro__copy"><?php echo wp_kses_post( $ef_intro['html'] ); ?></div>
					<?php else : ?>
						<?php if ( $ef_intro['catch'] ) : ?>
							<p class="ef-intro__catch"><?php echo esc_html( $ef_intro['catch'] ); ?></p>
						<?php endif; ?>
						<?php if ( $ef_intro['sub'] ) : ?>
							<p class="ef-intro__sub"><?php echo esc_html( $ef_intro['sub'] ); ?></p>
						<?php endif; ?>
						<?php if ( $ef_intro['body'] ) : ?>
							<div class="ef-intro__copy"><?php echo wp_kses_post( $ef_intro['body'] ); ?></div>
						<?php endif; ?>
					<?php endif; ?>

					<div class="ef-actions ef-mt-32">
						<a class="ef-btn ef-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<?php esc_html_e( 'この設備について相談する', 'eight-fields' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php
	$ef_sections = ef_service_sections( $ef_id );
	if ( $ef_sections ) :
		?>
		<!-- SECTIONS -->
		<div class="ef-section">
			<div class="ef-container">
				<?php get_template_part( 'template-parts/service-sections', null, array( 'sections' => $ef_sections ) ); ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- FLOW -->
	<section class="ef-section">
		<div class="ef-container">
			<div class="ef-split ef-split--top">
				<div data-reveal>
					<span class="ef-eyebrow">Flow</span>
					<h2 class="ef-h2"><?php esc_html_e( '導入までの流れ', 'eight-fields' ); ?></h2>
					<p class="ef-lead">
						<?php esc_html_e( 'ご相談・現地調査・お見積りまでは無料です。営業から施工まで自社で行うため、打ち合わせの内容がそのまま現場に伝わります。', 'eight-fields' ); ?>
					</p>
				</div>
				<div data-reveal data-reveal-delay="1">
					<?php get_template_part( 'template-parts/list-flow', null, array( 'context' => 'service' ) ); ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	$ef_faq = ef_service_faq( $ef_id );
	if ( $ef_faq ) :
		?>
		<!-- FAQ -->
		<section class="ef-section ef-section--sand">
			<div class="ef-container ef-container--narrow">
				<div class="ef-head ef-head--center" data-reveal>
					<span class="ef-eyebrow">FAQ</span>
					<h2 class="ef-h2"><?php esc_html_e( 'よくあるご質問', 'eight-fields' ); ?></h2>
				</div>
				<div class="ef-faq" data-reveal data-reveal-delay="1">
					<?php foreach ( $ef_faq as $ef_i => $ef_row ) : ?>
						<?php $ef_panel = 'faq-' . $ef_id . '-' . $ef_i; ?>
						<div class="ef-faq__item">
							<h3><button class="ef-faq__q" type="button" data-faq-q
									aria-expanded="false" aria-controls="<?php echo esc_attr( $ef_panel ); ?>">
								<span><?php echo esc_html( $ef_row['q'] ); ?></span><span class="ef-faq__mark"></span>
							</button></h3>
							<div class="ef-faq__a" id="<?php echo esc_attr( $ef_panel ); ?>" hidden>
								<div class="ef-faq__inner"><div><?php echo wp_kses_post( wpautop( $ef_row['a'] ) ); ?></div></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ef_others = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => 5,
			'post__not_in'   => array( $ef_id ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	if ( $ef_others ) :
		?>
		<!-- OTHER SERVICES -->
		<section class="ef-section">
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
						get_template_part( 'template-parts/card-service', null, array( 'no' => ef_service_position( $ef_post->ID ) ) );
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
