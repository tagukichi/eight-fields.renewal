<?php
/**
 * Presentation helpers shared across templates.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A company detail set in the Customizer.
 *
 * @param string $key     Setting key without the `ef_` prefix.
 * @param string $default Fallback value.
 * @return string
 */
function ef_info( $key, $default = '' ) {
	return (string) get_theme_mod( 'ef_' . $key, $default );
}

/**
 * The telephone number with separators stripped, for `tel:` links.
 *
 * @return string
 */
function ef_tel_digits() {
	return preg_replace( '/[^0-9+]/', '', ef_info( 'tel', '03-6670-5540' ) );
}

/**
 * The site logo mark URL — the custom logo when set, else the bundled file.
 *
 * @return string
 */
function ef_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return get_theme_file_uri( '/assets/img/logo-mark.png' );
}

/**
 * The lockup logo (mark + company name).
 *
 * The footer sits on navy, so it needs the white-lettering variant. A custom
 * logo set in the Customizer overrides the light version only — a dark
 * background still needs an asset authored for it.
 *
 * @param bool $on_dark Whether the logo sits on a dark background.
 * @return string
 */
function ef_logo_lockup_url( $on_dark = false ) {
	if ( $on_dark ) {
		return get_theme_file_uri( '/assets/img/logo-lockup-white.png' );
	}

	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return get_theme_file_uri( '/assets/img/logo-lockup.png' );
}

/**
 * Echo one of the theme's inline SVG icons.
 *
 * @param string $name Icon name.
 * @param bool   $echo Whether to echo (default) or return.
 * @return string
 */
function ef_icon( $name, $echo = true ) {
	$icons = array(
		'phone' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.3 3h3l1.5 3.8-2.3 1.4a12 12 0 0 0 5.3 5.3l1.4-2.3L19 12.7v3a2.3 2.3 0 0 1-2.5 2.3A15.5 15.5 0 0 1 4 5.5 2.3 2.3 0 0 1 6.3 3Z" fill="currentColor"/></svg>',
		'mail'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2.5" stroke="currentColor" stroke-width="1.8"/><path d="m3.5 6.5 8.5 6 8.5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'pin'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s7-6.3 7-11.4A7 7 0 0 0 5 10.6C5 15.7 12 22 12 22Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10.4" r="2.6" fill="currentColor"/></svg>',
		'arrow' => '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4.5 12.5 10 7 15.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'up'    => '<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M9 14.5V3.5M3.8 8.7 9 3.5l5.2 5.2" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'caret' => '<svg class="ef-caret" viewBox="0 0 12 8" fill="none" aria-hidden="true"><path d="M1 1.5 6 6.5l5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
		'chat'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 11.4c0 4-4 7.2-9 7.2a10.7 10.7 0 0 1-2.6-.3L4.5 20.5l1-3.7A6.9 6.9 0 0 1 3 11.4C3 7.4 7 4.2 12 4.2s9 3.2 9 7.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="8.6" cy="11.4" r="1.15" fill="currentColor"/><circle cx="12" cy="11.4" r="1.15" fill="currentColor"/><circle cx="15.4" cy="11.4" r="1.15" fill="currentColor"/></svg>',
		'check' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#3B9C6D"/><path d="m7.5 12.3 3 3 6-6.6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	$svg = isset( $icons[ $name ] ) ? $icons[ $name ] : '';
	if ( $echo ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		return '';
	}
	return $svg;
}

/**
 * Breadcrumb trail.
 *
 * @param array $extra Additional crumbs as [ label, url|null ] pairs.
 */
function ef_breadcrumbs( $extra = array() ) {
	$items   = array();
	$items[] = array( __( 'ホーム', 'eight-fields' ), home_url( '/' ) );

	if ( is_singular( 'service' ) ) {
		$items[] = array( __( 'サービス', 'eight-fields' ), get_post_type_archive_link( 'service' ) );
		$items[] = array( get_the_title(), null );
	} elseif ( is_post_type_archive( 'service' ) ) {
		$items[] = array( __( 'サービス', 'eight-fields' ), null );
	} elseif ( is_singular( 'post' ) ) {
		$items[] = array( __( 'お知らせ', 'eight-fields' ), get_permalink( get_option( 'page_for_posts' ) ) );
		$items[] = array( get_the_title(), null );
	} elseif ( is_home() || is_category() || is_archive() ) {
		$items[] = array( __( 'お知らせ', 'eight-fields' ), null );
	} elseif ( is_page() ) {
		$parent = wp_get_post_parent_id( get_the_ID() );
		if ( $parent ) {
			$items[] = array( get_the_title( $parent ), get_permalink( $parent ) );
		}
		$items[] = array( get_the_title(), null );
	} elseif ( is_search() ) {
		$items[] = array( __( '検索結果', 'eight-fields' ), null );
	} elseif ( is_404() ) {
		$items[] = array( __( 'ページが見つかりません', 'eight-fields' ), null );
	}

	foreach ( $extra as $crumb ) {
		$items[] = $crumb;
	}
	?>
	<nav class="ef-crumbs" aria-label="<?php esc_attr_e( 'パンくずリスト', 'eight-fields' ); ?>">
		<div class="ef-container">
			<ol class="ef-crumbs__list">
				<?php foreach ( $items as $item ) : ?>
					<li>
						<?php if ( $item[1] ) : ?>
							<a href="<?php echo esc_url( $item[1] ); ?>"><?php echo esc_html( $item[0] ); ?></a>
						<?php else : ?>
							<span aria-current="page"><?php echo esc_html( $item[0] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</nav>
	<?php
}

/**
 * Page hero used by every template except the front page.
 *
 * @param array $args title / en / lead / image_id / image_url.
 */
function ef_page_hero( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'     => '',
			'en'        => '',
			'lead'      => '',
			'image_id'  => 0,
			'image_url' => '',
		)
	);

	$image = $args['image_url'];
	if ( ! $image && $args['image_id'] ) {
		$src = wp_get_attachment_image_src( $args['image_id'], 'ef-hero' );
		if ( $src ) {
			$image = $src[0];
		}
	}
	?>
	<section class="ef-phero">
		<?php if ( $image ) : ?>
			<div class="ef-phero__media">
				<img src="<?php echo esc_url( $image ); ?>" alt="" decoding="async">
			</div>
		<?php endif; ?>
		<div class="ef-container">
			<?php if ( $args['en'] ) : ?>
				<p class="ef-phero__en"><?php echo esc_html( $args['en'] ); ?></p>
			<?php endif; ?>
			<h1 class="ef-phero__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['lead'] ) : ?>
				<p class="ef-phero__text"><?php echo esc_html( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * The category slug used for news badge colouring.
 *
 * @param int $post_id Post ID.
 * @return array [ slug, label ]
 */
function ef_post_category( $post_id = 0 ) {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) {
		return array( 'info', __( 'お知らせ', 'eight-fields' ) );
	}
	$known = array( 'info', 'works', 'column' );
	$slug  = in_array( $cats[0]->slug, $known, true ) ? $cats[0]->slug : 'info';
	return array( $slug, $cats[0]->name );
}

/**
 * Pagination in the theme's own markup.
 */
function ef_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => __( '前へ', 'eight-fields' ),
			'next_text' => __( '次へ', 'eight-fields' ),
		)
	);
	if ( empty( $links ) ) {
		return;
	}
	echo '<nav class="ef-pager" aria-label="' . esc_attr__( 'ページ送り', 'eight-fields' ) . '">';
	foreach ( $links as $link ) {
		$link = str_replace( 'page-numbers current', 'ef-pager__item is-current', $link );
		$link = str_replace( 'page-numbers dots', 'ef-pager__item is-disabled', $link );
		$link = str_replace( 'page-numbers', 'ef-pager__item', $link );
		echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links output.
	}
	echo '</nav>';
}
