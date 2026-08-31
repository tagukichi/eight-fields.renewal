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
	$ef_en    = get_post_meta( $ef_id, 'ef_service_en', true );
	$ef_catch = get_post_meta( $ef_id, 'ef_service_catch', true );
	$ef_sub   = get_post_meta( $ef_id, 'ef_service_sub', true );
	$ef_no    = ef_service_position( $ef_id );

	ef_page_hero(
		array(
			'title'    => get_the_title(),
			'en'       => $ef_en,
			'lead'     => get_the_excerpt(),
			'image_id' => get_post_thumbnail_id(),
		)
	);
	ef_breadcrumbs();
	?>

	<?php
	// A service page is a designed template, so a builder's own grid is set
	// aside and its text rendered as prose in the intro column. `ef_plain_body`
	// reads the builder's data without letting the builder lay it out; nothing
	// is written back, so the layout survives if this is ever switched off.
	$ef_ignore = ef_ignores_page_builder( 'service' );
	$ef_body   = $ef_ignore ? ef_plain_body( $ef_id ) : '';
	$ef_media  = has_post_thumbnail();
	$ef_split  = $ef_media;
	?>

	<!-- INTRO -->
	<section class="ef-section">
		<div class="ef-container<?php echo $ef_split ? '' : ' ef-container--narrow'; ?>">
			<div class="<?php echo $ef_split ? 'ef-split' : ''; ?>">
				<?php if ( $ef_split ) : ?>
					<div class="ef-split__media" data-reveal>
						<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => get_the_title() ) ); ?>
						<?php if ( $ef_no ) : ?>
							<span class="ef-split__badge"><b><?php echo esc_html( sprintf( '%02d', $ef_no ) ); ?></b><span><?php echo esc_html( $ef_en ); ?></span></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div data-reveal data-reveal-delay="1">
					<?php if ( $ef_en ) : ?>
						<span class="ef-eyebrow"><?php echo esc_html( $ef_en ); ?></span>
					<?php endif; ?>
					<h2 class="ef-h2"><?php echo esc_html( $ef_catch ? $ef_catch : get_the_title() ); ?></h2>
					<?php if ( $ef_sub ) : ?>
						<p class="ef-lead"><?php echo esc_html( $ef_sub ); ?></p>
					<?php endif; ?>

					<?php if ( $ef_ignore && $ef_body ) : ?>
						<div class="ef-article ef-mt-24">
							<?php echo wp_kses_post( $ef_body ); ?>
						</div>
					<?php elseif ( ! $ef_ignore ) : ?>
						<div class="ef-article ef-mt-24">
							<?php the_content(); ?>
						</div>
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
	$ef_how = ef_service_how( $ef_id );
	if ( $ef_how ) :
		?>
		<!-- HOW IT WORKS -->
		<section class="ef-section ef-section--tight ef-section--sand">
			<div class="ef-container">
				<h2 class="ef-bandtitle" data-reveal><?php echo esc_html( $ef_how['title'] ); ?></h2>
				<div class="ef-how" data-reveal data-reveal-delay="1">
					<?php if ( $ef_how['image_id'] ) : ?>
						<div class="ef-how__media">
							<?php echo wp_get_attachment_image( $ef_how['image_id'], 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="ef-how__text"><?php echo wp_kses_post( wpautop( $ef_how['text'] ) ); ?></div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ef_merits = ef_service_merits( $ef_id );
	if ( $ef_merits ) :
		?>
		<!-- MERIT -->
		<section class="ef-section">
			<div class="ef-container">
				<h2 class="ef-bandtitle" data-reveal>
					<?php
					$ef_merit_title = ef_field( 'ef_merit_title', $ef_id );
					echo esc_html(
						$ef_merit_title
							? $ef_merit_title
							/* translators: %s: service name */
							: sprintf( __( '%sの導入で得られるメリット', 'eight-fields' ), get_the_title() )
					);
					?>
				</h2>

				<div class="ef-merits">
					<?php
					$ef_i = 0;
					foreach ( $ef_merits as $ef_merit ) :
						++$ef_i;
						$ef_has_media = ! empty( $ef_merit['image_id'] );
						?>
						<div class="ef-meritrow<?php echo $ef_has_media ? '' : ' ef-meritrow--solo'; ?>" data-reveal>
							<div>
								<span class="ef-meritrow__no">MERIT <?php echo esc_html( sprintf( '%02d', $ef_i ) ); ?></span>
								<h3 class="ef-meritrow__title"><?php echo esc_html( $ef_merit['title'] ); ?></h3>
								<?php if ( ! empty( $ef_merit['sub'] ) ) : ?>
									<p class="ef-meritrow__sub"><?php echo nl2br( esc_html( $ef_merit['sub'] ) ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $ef_merit['text'] ) ) : ?>
									<div class="ef-meritrow__text"><?php echo wp_kses_post( wpautop( $ef_merit['text'] ) ); ?></div>
								<?php endif; ?>
							</div>
							<?php if ( $ef_has_media ) : ?>
								<div class="ef-meritrow__media<?php echo $ef_merit['contain'] ? ' ef-merit__media--contain' : ''; ?>">
									<?php echo wp_get_attachment_image( $ef_merit['image_id'], 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ef_outro_title = ef_field( 'ef_service_outro_title', $ef_id );
	$ef_outro       = ef_field( 'ef_service_outro', $ef_id );
	$ef_recommend   = ef_service_recommend( $ef_id );

	if ( ( $ef_outro_title && $ef_outro ) || $ef_recommend ) :
		?>
		<!-- CLOSING -->
		<section class="ef-section ef-section--tight">
			<div class="ef-container ef-container--narrow">
				<?php if ( $ef_outro_title && $ef_outro ) : ?>
					<div data-reveal>
						<h2 class="ef-h3 ef-underline"><?php echo esc_html( $ef_outro_title ); ?></h2>
						<div class="ef-article ef-mt-24"><?php echo wp_kses_post( wpautop( $ef_outro ) ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( $ef_recommend ) : ?>
					<div class="ef-recommend<?php echo ( $ef_outro_title && $ef_outro ) ? ' ef-mt-48' : ''; ?>" data-reveal data-reveal-delay="1">
						<h3 class="ef-recommend__title"><?php ef_icon( 'bulb' ); ?><?php echo esc_html( $ef_recommend['title'] ); ?></h3>
						<ul class="ef-recommend__list">
							<?php foreach ( $ef_recommend['items'] as $ef_item ) : ?>
								<li><?php echo esc_html( $ef_item ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</section>
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
