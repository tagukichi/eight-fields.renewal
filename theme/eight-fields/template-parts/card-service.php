<?php
/**
 * Service card used in grids.
 *
 * @package eight-fields
 */

$ef_no  = isset( $args['no'] ) ? (int) $args['no'] : 0;
$ef_lag = ( $ef_no % 3 ) + 1;
?>
<article class="ef-card ef-scard" data-reveal data-reveal-delay="<?php echo esc_attr( $ef_lag ); ?>">
	<a href="<?php the_permalink(); ?>" style="display:contents;color:inherit;">
		<div class="ef-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'ef-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => get_the_title() ) ); ?>
			<?php endif; ?>
			<?php if ( get_post_meta( get_the_ID(), 'ef_service_en', true ) ) : ?>
				<span class="ef-card__badge"><?php echo esc_html( get_post_meta( get_the_ID(), 'ef_service_en', true ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="ef-card__body">
			<?php if ( $ef_no ) : ?>
				<span class="ef-scard__no"><?php echo esc_html( sprintf( '%02d', $ef_no ) ); ?></span>
			<?php endif; ?>
			<h3 class="ef-card__title"><?php the_title(); ?></h3>
			<p class="ef-card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<p class="ef-card__foot"><span class="ef-link"><?php esc_html_e( '詳しく見る', 'eight-fields' ); ?></span></p>
		</div>
	</a>
</article>
