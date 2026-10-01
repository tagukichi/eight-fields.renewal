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

		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( 0 === $depth ) {
			// The dropdown hangs off this class: it is what positions the panel
			// and what reveals it on hover. Without it the sub-menu is in the
			// markup but never visible.
			$classes[] = 'ef-nav__item';

			$current = array_intersect(
				$classes,
				array(
					'current-menu-item',
					'current_page_item',
					'current-menu-parent',
					'current_page_parent',
					'current-menu-ancestor',
					'current_page_ancestor',
				)
			);
			if ( $current ) {
				$classes[] = 'is-current';
			}

			// Home is a drawer-only row in the design; on desktop the logo is
			// the way back.
			if ( ! empty( $item->url ) && untrailingslashit( $item->url ) === untrailingslashit( home_url( '/' ) ) ) {
				$classes[] = 'ef-nav__item--home';
			}
		}

		$class_names = implode( ' ', array_filter( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ) );

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

		// A service in the dropdown carries its own icon, as it does on the
		// cards and in the drawer.
		$icon = '';
		if ( $depth > 0 && 'service' === $item->object && $item->object_id ) {
			$svg = ef_service_icon( get_post_field( 'post_name', $item->object_id ) );
			if ( $svg ) {
				$icon = '<span class="ef-ico">' . $svg . '</span>';
			}
		}

		$output .= '<' . $tag . $attributes . '>' . $icon . esc_html( $title );
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
 * What should hang under a top-level menu item that has no sub-menu of its own.
 *
 * WordPress menus are a structure in their own right: making a page a child of
 * another page does nothing to the menu, and the editor has to drag the item
 * into place as well. Two cases are common enough here to be worth doing
 * automatically, so that building the site the obvious way gives the design's
 * dropdown without a second step:
 *
 * - サービス, which opens the six services, as the design has it;
 * - a page whose children are published pages — 会社概要 with a 会社案内 under
 *   it, say.
 *
 * An item that already has its own sub-menu in the menu is left alone: what the
 * editor arranged by hand wins over anything worked out here.
 *
 * @param WP_Post $item Top-level menu item.
 * @return WP_Post[] The posts to hang under it, newest structure first.
 */
function ef_nav_auto_children_for( $item ) {
	if ( post_type_exists( 'service' ) ) {
		$archive    = get_post_type_archive_link( 'service' );
		$is_service = ( 'post_type_archive' === $item->type && 'service' === $item->object )
			|| ( $archive && ! empty( $item->url ) && untrailingslashit( $item->url ) === untrailingslashit( $archive ) );

		if ( $is_service ) {
			return get_posts(
				array(
					'post_type'      => 'service',
					'posts_per_page' => -1,
					'orderby'        => array(
						'menu_order' => 'ASC',
						'date'       => 'ASC',
					),
				)
			);
		}
	}

	if ( 'post_type' === $item->type && 'page' === $item->object && ! empty( $item->object_id ) ) {
		return get_posts(
			array(
				'post_type'      => 'page',
				'post_parent'    => (int) $item->object_id,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);
	}

	return array();
}

/**
 * One menu item standing in for a post, in the shape the walkers read.
 *
 * @param WP_Post $post      The post the item points at.
 * @param int     $parent_id Menu item ID it hangs under.
 * @param int     $queried   The post being viewed, if any.
 * @return stdClass
 */
function ef_nav_child_item( $post, $parent_id, $queried ) {
	$child                        = new stdClass();
	$child->ID                    = (int) $post->ID;
	$child->db_id                 = (int) $post->ID;
	$child->menu_item_parent      = (string) $parent_id;
	$child->object_id             = (int) $post->ID;
	$child->object                = $post->post_type;
	$child->type                  = 'post_type';
	$child->type_label            = '';
	$child->title                 = get_the_title( $post );
	$child->url                   = get_permalink( $post );
	$child->target                = '';
	$child->attr_title            = '';
	$child->description           = '';
	$child->xfn                   = '';
	$child->post_parent           = (int) $post->post_parent;
	$child->menu_order            = (int) $post->menu_order;
	$child->classes               = array( '' );
	$child->current               = ( $queried === (int) $post->ID );
	$child->current_item_ancestor = false;
	$child->current_item_parent   = false;

	if ( $child->current ) {
		$child->classes[] = 'current-menu-item';
	}

	return $child;
}

/**
 * Give every top-level item without a sub-menu the children it implies.
 *
 * @param array    $items Menu items.
 * @param stdClass $args  Menu args.
 * @return array
 */
function ef_nav_auto_children( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	// WordPress keys the items by menu order, from 1 and with gaps. Inserting by
	// position needs positions, so the keys are dropped; the walker builds the
	// tree from each item's parent, not from the keys.
	$items = array_values( $items );

	$has_own = array();
	foreach ( $items as $item ) {
		if ( ! empty( $item->menu_item_parent ) ) {
			$has_own[ (int) $item->menu_item_parent ] = true;
		}
	}

	$queried = is_singular() ? get_queried_object_id() : 0;
	$inserts = array();

	foreach ( $items as $index => $item ) {
		if ( ! empty( $item->menu_item_parent ) || isset( $has_own[ (int) $item->ID ] ) ) {
			continue;
		}

		$posts = ef_nav_auto_children_for( $item );
		if ( ! $posts ) {
			continue;
		}

		$children = array();
		foreach ( $posts as $post ) {
			$child = ef_nav_child_item( $post, (int) $item->ID, $queried );
			if ( $child->current ) {
				$items[ $index ]->current_item_parent = true;
				$items[ $index ]->classes[]           = 'current-menu-parent';
			}
			$children[] = $child;
		}

		$items[ $index ]->classes[] = 'menu-item-has-children';
		$inserts[ $index + 1 ]      = $children;
	}

	// From the end, so the positions worked out above stay valid as the array
	// grows underneath them.
	krsort( $inserts );
	foreach ( $inserts as $at => $children ) {
		array_splice( $items, $at, 0, $children );
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'ef_nav_auto_children', 10, 2 );

/**
 * Shown when no menu has been assigned to the `primary` location yet.
 *
 * @param array $args Menu args.
 */
function ef_nav_fallback( $args ) {
	$drawer = isset( $args['menu_class'] ) && false !== strpos( $args['menu_class'], 'drawer' );

	// label, url, English label, page slug (サービス has an archive, not a page).
	$items = array(
		array( __( '会社概要', 'eight-fields' ), home_url( '/company/' ), 'COMPANY', 'company' ),
		array( __( 'ごあいさつ', 'eight-fields' ), home_url( '/greeting/' ), 'GREETING', 'greeting' ),
		array( __( 'サービス', 'eight-fields' ), get_post_type_archive_link( 'service' ), 'SERVICE', '' ),
		array( __( 'お知らせ', 'eight-fields' ), home_url( '/news/' ), 'NEWS', 'news' ),
		array( __( 'お問い合わせ', 'eight-fields' ), home_url( '/contact/' ), 'CONTACT', 'contact' ),
	);

	echo '<ul class="' . esc_attr( $args['menu_class'] ) . '">';

	foreach ( $items as $item ) {
		list( $label, $url, $en, $slug ) = $item;

		// The same two cases the assigned menu gets: the services, and a page's
		// own published children.
		if ( '' === $slug ) {
			$children = get_posts(
				array(
					'post_type'      => 'service',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);
		} else {
			$page     = get_page_by_path( $slug );
			$children = $page ? get_posts(
				array(
					'post_type'      => 'page',
					'post_parent'    => $page->ID,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
				)
			) : array();
		}

		if ( ! $drawer ) {
			if ( ! $children ) {
				echo '<li class="ef-nav__item"><a class="ef-nav__link" href="' . esc_url( $url ) . '">'
					. esc_html( $label ) . '</a></li>';
				continue;
			}

			echo '<li class="ef-nav__item"><a class="ef-nav__link" href="' . esc_url( $url ) . '">'
				. esc_html( $label ) . ef_icon( 'caret', false ) . '</a>'
				. '<ul class="ef-nav__sub">';
			foreach ( $children as $child ) {
				$svg  = 'service' === $child->post_type ? ef_service_icon( $child->post_name ) : '';
				$icon = $svg ? '<span class="ef-ico">' . $svg . '</span>' : '';
				echo '<li><a class="ef-nav__sublink" href="' . esc_url( get_permalink( $child ) ) . '">'
					. $icon . esc_html( get_the_title( $child ) ) . '</a></li>';
			}
			echo '</ul></li>';
			continue;
		}

		if ( ! $children ) {
			echo '<li><a class="ef-drawer__link" href="' . esc_url( $url ) . '"><span>'
				. esc_html( $label ) . '<small>' . esc_html( $en ) . '</small></span>'
				. ef_icon( 'arrow', false ) . '</a></li>';
			continue;
		}

		$panel = 'ef-dsub-' . ( '' === $slug ? 'service' : $slug );
		/* translators: %s: menu item label */
		$toggle = sprintf( __( '%sのサブメニューを開閉', 'eight-fields' ), $label );

		echo '<li><div class="ef-drawer__row">'
			. '<a class="ef-drawer__link" href="' . esc_url( $url ) . '"><span>'
			. esc_html( $label ) . '<small>' . esc_html( $en ) . '</small></span></a>'
			. '<button class="ef-drawer__toggle" type="button" data-drawer-toggle'
			. ' aria-expanded="false" aria-controls="' . esc_attr( $panel ) . '">'
			. '<span class="ef-drawer__caret"></span>'
			. '<span class="ef-sr">' . esc_html( $toggle ) . '</span>'
			. '</button></div>'
			. '<div class="ef-drawer__sub" id="' . esc_attr( $panel ) . '" hidden><div><ul class="ef-drawer__sublist">';
		foreach ( $children as $child ) {
			$svg  = 'service' === $child->post_type ? ef_service_icon( $child->post_name ) : '';
			$icon = $svg ? '<span class="ef-ico">' . $svg . '</span>' : '';
			echo '<li><a class="ef-drawer__sublink" href="' . esc_url( get_permalink( $child ) ) . '">'
				. $icon . esc_html( get_the_title( $child ) ) . '</a></li>';
		}
		echo '</ul></div></div></li>';
	}

	echo '</ul>';
}
