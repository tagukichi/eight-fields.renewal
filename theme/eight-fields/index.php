<?php
/**
 * News list — the posts page (/news/) and every unmatched archive.
 *
 * @package eight-fields
 */

get_header();

$ef_title = __( 'お知らせ', 'eight-fields' );
$ef_lead  = __( '休業日のご案内、補助金や制度の情報、施工事例などをお届けします。', 'eight-fields' );

if ( is_category() ) {
	$ef_title = single_cat_title( '', false );
	$ef_lead  = wp_strip_all_tags( category_description() );
} elseif ( is_search() ) {
	/* translators: %s: search term */
	$ef_title = sprintf( __( '「%s」の検索結果', 'eight-fields' ), get_search_query() );
	$ef_lead  = '';
}

ef_page_hero(
	array(
		'title'     => $ef_title,
		'en'        => is_search() ? 'SEARCH' : 'NEWS',
		'lead'      => $ef_lead,
		'image_url' => get_theme_file_uri( '/assets/img/maintenance.jpg' ),
	)
);
ef_breadcrumbs();
?>

<section class="ef-section">
	<div class="ef-container">

		<?php
		$ef_cats = get_categories( array( 'hide_empty' => true ) );
		if ( $ef_cats && ! is_search() ) :
			?>
			<div class="ef-tabs" role="tablist" aria-label="<?php esc_attr_e( 'カテゴリで絞り込む', 'eight-fields' ); ?>" data-reveal>
				<a class="ef-tab<?php echo is_category() ? '' : ' is-active'; ?>"
					href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'すべて', 'eight-fields' ); ?></a>
				<?php foreach ( $ef_cats as $ef_cat ) : ?>
					<a class="ef-tab<?php echo is_category( $ef_cat->term_id ) ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( get_category_link( $ef_cat ) ); ?>"><?php echo esc_html( $ef_cat->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<ul class="ef-news" data-reveal data-reveal-delay="1">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/item-news' );
				endwhile;
				?>
			</ul>
			<?php ef_pagination(); ?>
		<?php else : ?>
			<p class="ef-center ef-mt-32"><?php esc_html_e( '該当するお知らせはありません。', 'eight-fields' ); ?></p>
		<?php endif; ?>

	</div>
</section>

<?php
get_footer();
