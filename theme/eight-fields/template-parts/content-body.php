<?php
/**
 * The post body, in the wrapper its authoring tool needs.
 *
 * Written in the block or classic editor it is running prose, so it gets the
 * narrow article column. Built with a page builder it is a layout in its own
 * right, so it gets the full container instead.
 *
 * @package eight-fields
 */

$ef_builder = ef_uses_page_builder();
?>
<?php if ( $ef_builder ) : ?>
	<div class="ef-builder" data-reveal>
		<?php the_content(); ?>
	</div>
<?php else : ?>
	<div class="ef-container ef-container--narrow">
		<div class="ef-article" data-reveal>
			<?php the_content(); ?>
		</div>
	</div>
<?php endif; ?>
