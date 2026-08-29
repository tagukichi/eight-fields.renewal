<?php
/**
 * A row of three feature cards — the front page's REASONS and the greeting
 * page's VALUES share this shape.
 *
 * @package eight-fields
 *
 * @var array $args {
 *     @type array[] $items Rows of array( no, title, text ).
 * }
 */

$ef_items = isset( $args['items'] ) ? (array) $args['items'] : array();
if ( ! $ef_items ) {
	return;
}
?>
<div class="ef-grid ef-grid--3">
	<?php
	$ef_i = 0;
	foreach ( $ef_items as $ef_item ) :
		++$ef_i;
		?>
		<div class="ef-feature" data-reveal data-reveal-delay="<?php echo esc_attr( $ef_i ); ?>">
			<span class="ef-feature__no"><?php echo esc_html( $ef_item['no'] ); ?></span>
			<h3 class="ef-feature__title"><?php echo esc_html( $ef_item['title'] ); ?></h3>
			<p class="ef-feature__text"><?php echo esc_html( $ef_item['text'] ); ?></p>
		</div>
	<?php endforeach; ?>
</div>
