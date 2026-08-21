<?php
/**
 * 404.
 *
 * @package eight-fields
 */

get_header();

ef_page_hero(
	array(
		'title' => __( 'ページが見つかりませんでした', 'eight-fields' ),
		'en'    => '404 NOT FOUND',
		'lead'  => __( 'お探しのページは移動または削除された可能性があります。お手数ですが、下記からお探しください。', 'eight-fields' ),
	)
);
ef_breadcrumbs();
?>

<section class="ef-section">
	<div class="ef-container ef-container--narrow ef-center">
		<div class="ef-actions ef-actions--center">
			<a class="ef-btn ef-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'トップページへ', 'eight-fields' ); ?></a>
			<a class="ef-btn ef-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>"><?php esc_html_e( 'サービス一覧', 'eight-fields' ); ?></a>
			<a class="ef-btn ef-btn--outline" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'お問い合わせ', 'eight-fields' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
