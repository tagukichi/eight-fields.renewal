<?php
/**
 * The body of a service page: a stack of sections.
 *
 * @package eight-fields
 *
 * @var array $args {
 *     @type array[] $sections Rows from ef_service_sections().
 * }
 */

$ef_sections = isset( $args['sections'] ) ? (array) $args['sections'] : array();
if ( ! $ef_sections ) {
	return;
}

// Merit sections carry a running number, so they need their own counter rather
// than the loop index — a band heading in between must not advance it.
$ef_merit_no = 0;
?>
<?php foreach ( $ef_sections as $ef_sec ) : ?>
	<?php
	$ef_merit  = 'merit' === $ef_sec['style'];
	$ef_media  = ! empty( $ef_sec['image_id'] );
	$ef_body   = trim( $ef_sec['text'] ) || $ef_sec['list'];
	if ( $ef_merit ) {
		++$ef_merit_no;
	}
	?>
	<section class="ef-sec" data-reveal>
		<?php if ( $ef_sec['heading'] ) : ?>
			<?php if ( 'band' === $ef_sec['style'] ) : ?>
				<h2 class="ef-sec__band"><?php echo esc_html( $ef_sec['heading'] ); ?></h2>
			<?php else : ?>
				<div>
					<?php if ( $ef_merit ) : ?>
						<span class="ef-sec__no">MERIT <?php echo esc_html( sprintf( '%02d', $ef_merit_no ) ); ?></span>
					<?php endif; ?>
					<h2 class="ef-sec__plain"><?php echo esc_html( $ef_sec['heading'] ); ?></h2>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $ef_body || $ef_media ) : ?>
			<div class="ef-sec__body<?php echo ( $ef_media && $ef_body ) ? '' : ' ef-sec__body--solo'; ?>">
				<?php if ( $ef_body ) : ?>
					<div>
						<?php if ( trim( $ef_sec['text'] ) ) : ?>
							<div class="ef-sec__text"><?php echo wp_kses_post( wpautop( $ef_sec['text'] ) ); ?></div>
						<?php endif; ?>

						<?php if ( $ef_sec['list'] && ! $ef_sec['boxed'] ) : ?>
							<ul class="ef-sec__list">
								<?php foreach ( $ef_sec['list'] as $ef_item ) : ?>
									<li><?php echo esc_html( $ef_item ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $ef_media ) : ?>
					<div class="ef-sec__media<?php echo $ef_sec['contain'] ? ' ef-sec__media--contain' : ''; ?><?php echo 'left' === $ef_sec['side'] ? ' ef-sec__media--left' : ''; ?>">
						<?php echo wp_get_attachment_image( $ef_sec['image_id'], 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $ef_sec['list'] && $ef_sec['boxed'] ) : ?>
			<div class="ef-sec__list--boxed">
				<?php if ( ! empty( $ef_sec['list_heading'] ) ) : ?>
					<h3 class="ef-sec__listtitle"><?php ef_icon( 'bulb' ); ?><?php echo esc_html( $ef_sec['list_heading'] ); ?></h3>
				<?php endif; ?>
			<ul class="ef-sec__list">
				<?php foreach ( $ef_sec['list'] as $ef_item ) : ?>
					<li><?php echo esc_html( $ef_item ); ?></li>
				<?php endforeach; ?>
			</ul>
			</div>
		<?php endif; ?>
	</section>
<?php endforeach; ?>
