<?php
/**
 * Navigation walkers.
 *
 * The design's header uses a hover dropdown, and the mobile drawer uses a flat
 * two-level list with English sub-labels — neither matches the default walker
 * output, so both get their own.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Desktop navigation: adds the caret to items that have children.
 */
class EF_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a sub-menu.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="ef-nav__sub">';
	}

	/**
	 * Render one item.
	 *
	 * @param string   $output Output buffer.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = implode( ' ', array_filter( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ) );
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		$output .= '<li class="' . esc_attr( $class_names ) . '">';

		$atts          = array();
		$atts['href']  = ! empty( $item->url ) ? $item->url : '';
		$atts['class'] = ( 0 === $depth ) ? 'ef-nav__link' : 'ef-nav__sublink';
		$atts          = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $key => $value ) {
			if ( '' === $value ) {
				continue;
			}
			$attributes .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		// A menu item can end up with no URL — pointing at a post type archive
		// that the post type does not actually have, for one. An <a> without an
		// href is not focusable and not clickable, so use a span for that case
		// rather than emitting a link that is not one.
		$tag = isset( $atts['href'] ) && '' !== $atts['href'] ? 'a' : 'span';

		$output .= '<' . $tag . $attributes . '>' . esc_html( $title );
		if ( $has_children && 0 === $depth ) {
			$output .= ef_icon( 'caret', false );
		}
		$output .= '</' . $tag . '>';
	}
}

/**
 * Mobile drawer: top level rows, with an accordion for any item that has
 * children. Tapping the row opens the accordion rather than navigating; the
 * parent page is offered as the first link inside it.
 */
class EF_Drawer_Walker extends Walker_Nav_Menu {

	/**
	 * Whether the open top-level item wrapped its children in an accordion panel.
	 *
	 * @var bool
	 */
	private $panel_open = false;

	/**
	 * Open a sub-menu.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div><ul class="ef-drawer__sublist">';
	}

	/**
	 * Close a sub-menu.
	 *
	 * @param string   $output Output buffer.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div>';
	}

	/**
	 * Render one item.
	 *
	 * @param string   $output Output buffer.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$url   = ! empty( $item->url ) ? $item->url : '';

		if ( $depth > 0 ) {
			// A service in the sub-menu carries its own icon, as on the cards.
			$icon = '';
			if ( 'service' === $item->object && $item->object_id ) {
				$svg = ef_service_icon( get_post_field( 'post_name', $item->object_id ) );
				if ( $svg ) {
					$icon = '<span class="ef-ico">' . $svg . '</span>';
				}
			}

			$output .= '<li><a class="ef-drawer__sublink" href="' . esc_url( $url ) . '">'
				. $icon . esc_html( $title ) . '</a>';
			return;
		}

		// The menu item description doubles as the small English label.
		$en = $item->description ? '<small>' . esc_html( $item->description ) . '</small>' : '';

		if ( $this->has_children ) {
			$this->panel_open = true;
			$panel            = 'ef-dsub-' . $item->ID;
			/* translators: %s: menu item label */
			$toggle_label = sprintf( __( '%sのサブメニューを開閉', 'eight-fields' ), $title );
			$output      .= '<li>'
				. '<div class="ef-drawer__row">'
				. ( $url
					? '<a class="ef-drawer__link" href="' . esc_url( $url ) . '">'
						. '<span>' . esc_html( $title ) . $en . '</span></a>'
					: '<span class="ef-drawer__link">'
						. '<span>' . esc_html( $title ) . $en . '</span></span>' )
				. '<button class="ef-drawer__toggle" type="button" data-drawer-toggle'
				. ' aria-expanded="false" aria-controls="' . esc_attr( $panel ) . '">'
				. '<span class="ef-drawer__caret"></span>'
				. '<span class="ef-sr">' . esc_html( $toggle_label ) . '</span>'
				. '</button>'
				. '</div>'
				. '<div class="ef-drawer__sub" id="' . esc_attr( $panel ) . '" hidden>';
			return;
		}

		$tag     = $url ? 'a' : 'span';
		$href    = $url ? ' href="' . esc_url( $url ) . '"' : '';
		$output .= '<li><' . $tag . ' class="ef-drawer__link"' . $href . '>'
			. '<span>' . esc_html( $title ) . $en . '</span>'
			. ef_icon( 'arrow', false ) . '</' . $tag . '>';
	}

	/**
	 * Close an item, shutting the accordion wrapper when there was one.
	 *
	 * @param string   $output Output buffer.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->panel_open ) {
			$output          .= '</div>';
			$this->panel_open = false;
		}
		$output .= '</li>';
	}
}

/**
 * Shown when no menu has been assigned to the `primary` location yet.
 *
 * @param array $args Menu args.
 */
function ef_nav_fallback( $args ) {
	$drawer = isset( $args['menu_class'] ) && false !== strpos( $args['menu_class'], 'drawer' );
	$items  = array(
		array( __( '会社概要', 'eight-fields' ), home_url( '/company/' ), 'COMPANY' ),
		array( __( 'ごあいさつ', 'eight-fields' ), home_url( '/greeting/' ), 'GREETING' ),
		array( __( 'サービス', 'eight-fields' ), get_post_type_archive_link( 'service' ), 'SERVICE' ),
		array( __( 'お知らせ', 'eight-fields' ), home_url( '/news/' ), 'NEWS' ),
		array( __( 'お問い合わせ', 'eight-fields' ), home_url( '/contact/' ), 'CONTACT' ),
	);

	$services = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	echo '<ul class="' . esc_attr( $args['menu_class'] ) . '">';
	foreach ( $items as $item ) {
		$is_service = 'SERVICE' === $item[2];

		if ( ! $drawer ) {
			echo '<li class="ef-nav__item"><a class="ef-nav__link" href="' . esc_url( $item[1] ) . '">'
				. esc_html( $item[0] ) . '</a></li>';
			continue;
		}

		if ( $is_service && $services ) {
			echo '<li><div class="ef-drawer__row">'
				. '<a class="ef-drawer__link" href="' . esc_url( $item[1] ) . '"><span>'
				. esc_html( $item[0] ) . '<small>' . esc_html( $item[2] ) . '</small></span></a>'
				. '<button class="ef-drawer__toggle" type="button" data-drawer-toggle'
				. ' aria-expanded="false" aria-controls="ef-dsub-service">'
				. '<span class="ef-drawer__caret"></span>'
				. '<span class="ef-sr">' . esc_html__( 'サービスのサブメニューを開閉', 'eight-fields' ) . '</span>'
				. '</button></div>'
				. '<div class="ef-drawer__sub" id="ef-dsub-service" hidden><div><ul class="ef-drawer__sublist">';
			foreach ( $services as $service ) {
				echo '<li><a class="ef-drawer__sublink" href="' . esc_url( get_permalink( $service ) ) . '">'
					. esc_html( get_the_title( $service ) ) . '</a></li>';
			}
			echo '</ul></div></div></li>';
			continue;
		}

		echo '<li><a class="ef-drawer__link" href="' . esc_url( $item[1] ) . '"><span>'
			. esc_html( $item[0] ) . '<small>' . esc_html( $item[2] ) . '</small></span>'
			. ef_icon( 'arrow', false ) . '</a></li>';
	}
	echo '</ul>';
}
