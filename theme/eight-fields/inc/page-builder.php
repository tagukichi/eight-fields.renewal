<?php
/**
 * Read a body that was authored in a page builder, without the builder.
 *
 * Service pages are laid out by the theme — photo, catch line, merits, flow,
 * FAQ. A builder's own rows and columns fight that, so on `service` the theme
 * takes the text the builder is holding and renders it in the design's own
 * prose style instead.
 *
 * Nothing is written back: the builder's data stays untouched, so switching
 * this off restores the original layout.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether builder layouts should be ignored for a post type.
 *
 * @param string $post_type Post type.
 * @return bool
 */
function ef_ignores_page_builder( $post_type ) {
	// Service pages are a designed template; a page is whatever the editor makes
	// of it, so builders keep working there.
	return (bool) apply_filters( 'ef_ignores_page_builder', 'service' === $post_type, $post_type );
}

/**
 * The body of a post as plain prose, with any builder scaffolding removed.
 *
 * Runs the standard content filters (shortcodes, paragraphs, smart quotes) but
 * not `the_content`, which is where a builder would re-insert its layout.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string HTML, or '' when there is no body.
 */
function ef_plain_body( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$raw = (string) get_post_field( 'post_content', $post_id );

	// SiteOrigin writes its rendered layout into post_content; when it has not,
	// the text still lives in the builder's own data.
	if ( '' === trim( wp_strip_all_tags( $raw ) ) ) {
		$raw = ef_siteorigin_text( $post_id );
	}

	if ( '' === trim( $raw ) ) {
		return '';
	}

	// Unwrap before paragraphing, so wpautop() sees ordinary content and does
	// not leave empty paragraphs where the grid used to be.
	$html = do_blocks( $raw );
	$html = ef_strip_builder_wrappers( $html );
	$html = wptexturize( $html );
	$html = do_shortcode( $html );
	$html = wpautop( $html );
	$html = shortcode_unautop( $html );

	// Whitespace left behind by the removed wrappers can still become an empty
	// paragraph, which shows up as a stray gap.
	$html = preg_replace( '#<p>(\s|&nbsp;)*</p>#i', '', $html );

	return trim( $html );
}

/**
 * Remove a builder's layout wrappers, keeping everything inside them.
 *
 * The wrappers carry the grid; the content inside is ordinary HTML. Dropping
 * only the wrappers leaves the text, headings and images in document order.
 *
 * @param string $html Rendered HTML.
 * @return string
 */
function ef_strip_builder_wrappers( $html ) {
	if ( false === strpos( $html, '<div' ) ) {
		return $html;
	}

	$wrappers = array(
		// SiteOrigin Page Builder.
		'panel-layout',
		'panel-grid',
		'panel-grid-cell',
		'panel-widget-style',
		'so-panel',
		'textwidget',
		'siteorigin-widget-tinymce',
		// Elementor.
		'elementor-section',
		'elementor-container',
		'elementor-column',
		'elementor-column-wrap',
		'elementor-widget-wrap',
		'elementor-widget-container',
		'elementor-element',
	);

	$previous = '';
	$guard    = 0;

	// Wrappers nest, so unwrap repeatedly until nothing changes. The guard stops
	// a pathological document from looping.
	while ( $previous !== $html && $guard < 12 ) {
		$previous = $html;
		++$guard;

		foreach ( $wrappers as $class ) {
			$html = preg_replace(
				'#<div[^>]*\bclass="[^"]*\b' . preg_quote( $class, '#' ) . '\b[^"]*"[^>]*>(.*?)</div>#is',
				'$1',
				$html
			);
		}
	}

	return $html;
}

/**
 * The text SiteOrigin Page Builder is holding for a post.
 *
 * Walks the stored widgets in the order they appear on the page and pulls out
 * the fields that carry copy. Widgets with no text of their own (spacers,
 * sliders) contribute nothing.
 *
 * @param int $post_id Post ID.
 * @return string HTML.
 */
function ef_siteorigin_text( $post_id ) {
	$panels = get_post_meta( $post_id, 'panels_data', true );
	if ( ! is_array( $panels ) || empty( $panels['widgets'] ) ) {
		return '';
	}

	$widgets = $panels['widgets'];

	// `grid` is the row and `cell` the column; sorting by them keeps reading
	// order, which is what a single-column render needs.
	usort(
		$widgets,
		function ( $a, $b ) {
			$ai = isset( $a['panels_info'] ) ? $a['panels_info'] : array();
			$bi = isset( $b['panels_info'] ) ? $b['panels_info'] : array();

			$keys = array( 'grid', 'cell', 'cell_index' );
			foreach ( $keys as $key ) {
				$av = isset( $ai[ $key ] ) ? (int) $ai[ $key ] : 0;
				$bv = isset( $bi[ $key ] ) ? (int) $bi[ $key ] : 0;
				if ( $av !== $bv ) {
					return $av - $bv;
				}
			}
			return 0;
		}
	);

	$out = '';
	foreach ( $widgets as $widget ) {
		$out .= ef_siteorigin_widget_text( $widget );
	}

	return $out;
}

/**
 * The copy inside one SiteOrigin widget.
 *
 * @param array $widget Widget data.
 * @return string HTML.
 */
function ef_siteorigin_widget_text( $widget ) {
	if ( ! is_array( $widget ) ) {
		return '';
	}

	$out = '';

	// A headline widget keeps its two lines apart; render them as headings.
	if ( ! empty( $widget['headline'] ) ) {
		$out .= '<h2>' . wp_kses_post( $widget['headline'] ) . '</h2>';
	}
	if ( ! empty( $widget['sub_headline'] ) ) {
		$out .= '<p>' . wp_kses_post( $widget['sub_headline'] ) . '</p>';
	}

	// Editor and text widgets keep their HTML in one of these.
	foreach ( array( 'text', 'content' ) as $key ) {
		if ( ! empty( $widget[ $key ] ) && is_string( $widget[ $key ] ) ) {
			$out .= wp_kses_post( $widget[ $key ] );
		}
	}

	// An image widget stores an attachment ID.
	if ( ! empty( $widget['image'] ) && is_numeric( $widget['image'] ) ) {
		$img = wp_get_attachment_image( (int) $widget['image'], 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		if ( $img ) {
			$out .= '<p>' . $img . '</p>';
		}
	}

	return $out;
}
