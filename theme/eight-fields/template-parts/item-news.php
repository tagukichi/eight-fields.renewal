<?php
/**
 * One row in the news list.
 *
 * @package eight-fields
 */

list( $ef_cat_slug, $ef_cat_label ) = ef_post_category();
?>
<li class="ef-news__item" data-cat="<?php echo esc_attr( $ef_cat_slug ); ?>">
	<a class="ef-news__link" href="<?php the_permalink(); ?>">
		<time class="ef-news__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
		</time>
		<span class="ef-news__cat ef-news__cat--<?php echo esc_attr( $ef_cat_slug ); ?>">
			<?php echo esc_html( $ef_cat_label ); ?>
		</span>
		<span class="ef-news__title"><?php the_title(); ?></span>
		<span class="ef-news__arrow"><?php ef_icon( 'arrow' ); ?></span>
	</a>
</li>
