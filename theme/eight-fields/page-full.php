<?php
/**
 * Template Name: 全幅（セクション自由組み）
 *
 * Same as page.php but without the narrow article column, so the block editor
 * can lay out full-width sections (会社概要トップやサービス紹介ページ向け).
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

	the_content();

endwhile;

get_footer();
