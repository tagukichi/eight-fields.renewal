<?php
/**
 * Search form.
 *
 * @package eight-fields
 */

?>
<form class="ef-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
	style="display:flex;gap:10px;">
	<label class="ef-sr" for="ef-s"><?php esc_html_e( 'サイト内を検索', 'eight-fields' ); ?></label>
	<input class="ef-input" id="ef-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'キーワードを入力', 'eight-fields' ); ?>">
	<button class="ef-btn ef-btn--dark ef-btn--sm" type="submit"><?php esc_html_e( '検索', 'eight-fields' ); ?></button>
</form>
