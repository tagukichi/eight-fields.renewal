<?php
/**
 * The "数字で見る" card row.
 *
 * @package eight-fields
 *
 * @var array $args {
 *     @type array[] $items Rows of array( label, value, unit, count? ).
 * }
 */

$ef_items = isset( $args['items'] ) ? (array) $args['items'] : ef_numbers();
if ( ! $ef_items ) {
	return;
}
?>
<div class="ef-stats" data-reveal data-reveal-delay="1">
	<?php
	$ef_i = 0;
	foreach ( $ef_items as $ef_item ) :
		++$ef_i;
		$ef_count = isset( $ef_item['count'] ) ? $ef_item['count'] : '';
		?>
		<div class="ef-stats__item">
			<span class="ef-stats__no"><?php echo esc_html( sprintf( '%02d', $ef_i ) ); ?></span>
			<p class="ef-stats__label"><?php echo esc_html( $ef_item['label'] ); ?></p>
			<?php // The two spans must stay adjacent: the number is set at ~46px, so a newline between them renders as a wide space. ?>
			<p class="ef-stats__value"><span<?php echo '' !== $ef_count ? ' data-count="' . esc_attr( $ef_count ) . '"' : ''; ?>><?php echo esc_html( $ef_item['value'] ); ?></span><span><?php echo esc_html( $ef_item['unit'] ); ?></span></p>
		</div>
	<?php endforeach; ?>
</div>
