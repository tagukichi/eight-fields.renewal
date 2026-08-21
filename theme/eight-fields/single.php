<?php
/**
 * Single post (news).
 *
 * @package eight-fields
 */

get_header();

while ( have_posts() ) :
	the_post();
	list( $ef_cat_slug, $ef_cat_label ) = ef_post_category();
	?>

	<section class="ef-phero">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ef-phero__media"><?php the_post_thumbnail( 'ef-hero', array( 'alt' => '' ) ); ?></div>
		<?php endif; ?>
		<div class="ef-container">
			<p class="ef-phero__en">NEWS</p>
			<div style="display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin-top:14px;">
				<time class="ef-news__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" style="color:rgba(255,255,255,.72);">
					<?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
				</time>
				<span class="ef-news__cat ef-news__cat--<?php echo esc_attr( $ef_cat_slug ); ?>"><?php echo esc_html( $ef_cat_label ); ?></span>
			</div>
			<h1 class="ef-phero__title" style="margin-top:14px;"><?php the_title(); ?></h1>
		</div>
	</section>

	<?php ef_breadcrumbs(); ?>

	<section class="ef-section">
		<div class="ef-container ef-container--narrow">
			<div class="ef-article" data-reveal>
				<?php the_content(); ?>
			</div>

			<div class="ef-actions ef-actions--center ef-mt-64" style="padding-top:36px;border-top:1px solid var(--ef-line-soft);">
				<?php
				$ef_prev = get_previous_post();
				$ef_next = get_next_post();
				?>
				<?php if ( $ef_prev ) : ?>
					<a class="ef-btn ef-btn--outline ef-btn--sm" href="<?php echo esc_url( get_permalink( $ef_prev ) ); ?>"><?php esc_html_e( '前の記事', 'eight-fields' ); ?></a>
				<?php else : ?>
					<span class="ef-btn ef-btn--outline ef-btn--sm" style="opacity:.4;pointer-events:none;"><?php esc_html_e( '前の記事', 'eight-fields' ); ?></span>
				<?php endif; ?>

				<a class="ef-btn ef-btn--dark ef-btn--sm" href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'お知らせ一覧', 'eight-fields' ); ?></a>

				<?php if ( $ef_next ) : ?>
					<a class="ef-btn ef-btn--outline ef-btn--sm" href="<?php echo esc_url( get_permalink( $ef_next ) ); ?>"><?php esc_html_e( '次の記事', 'eight-fields' ); ?></a>
				<?php else : ?>
					<span class="ef-btn ef-btn--outline ef-btn--sm" style="opacity:.4;pointer-events:none;"><?php esc_html_e( '次の記事', 'eight-fields' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
