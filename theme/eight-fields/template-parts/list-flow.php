<?php
/**
 * The numbered STEP list.
 *
 * @package eight-fields
 *
 * @var array $args {
 *     @type string $context 'home' or 'service'.
 * }
 */

$ef_steps = ef_flow_steps( isset( $args['context'] ) ? $args['context'] : 'home' );
if ( ! $ef_steps ) {
	return;
}
?>
<ol class="ef-flow">
	<?php
	$ef_i = 0;
	foreach ( $ef_steps as $ef_step ) :
		++$ef_i;
		?>
		<li class="ef-flow__item">
			<span class="ef-flow__no">STEP<b><?php echo esc_html( sprintf( '%02d', $ef_i ) ); ?></b></span>
			<div>
				<h3 class="ef-flow__title"><?php echo esc_html( $ef_step['title'] ); ?></h3>
				<p class="ef-flow__text"><?php echo esc_html( $ef_step['text'] ); ?></p>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
