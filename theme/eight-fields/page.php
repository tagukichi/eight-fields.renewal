<?php
/**
 * Generic page — used by 会社概要 / ごあいさつ / お問い合わせ など。
 *
 * The hero image comes from the page's featured image; the English label from
 * the `ef_page_en` custom field.
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
	?>

	<section class="ef-section">
		<div class="ef-container ef-container--narrow">
			<div class="ef-article" data-reveal>
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
