<?php
/**
 * One-click setup: create the pages, services and menus the design expects.
 *
 * A fresh WordPress has none of the structure the templates rely on — no
 * 会社概要 page, no `service` posts, no menus — so the site looks broken until
 * someone builds it by hand. This screen does that build in one press, using
 * the same content the design proposal was made from.
 *
 * It is safe to run more than once: existing posts are matched by slug and
 * updated, never duplicated, and a page whose body has been edited keeps its
 * body.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the setup screen under 外観.
 */
function ef_add_setup_page() {
	add_theme_page(
		__( '初期セットアップ', 'eight-fields' ),
		__( '初期セットアップ', 'eight-fields' ),
		'edit_theme_options',
		'ef-setup',
		'ef_render_setup_page'
	);
}
add_action( 'admin_menu', 'ef_add_setup_page' );

/**
 * Read the bundled seed file.
 *
 * @return array|WP_Error
 */
function ef_seed_data() {
	$path = get_template_directory() . '/data/seed.json';
	if ( ! file_exists( $path ) ) {
		return new WP_Error( 'ef_seed_missing', __( 'data/seed.json が見つかりません。', 'eight-fields' ) );
	}

	$data = json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- bundled theme file.
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'ef_seed_invalid', __( 'data/seed.json を読み込めませんでした。', 'eight-fields' ) );
	}

	return $data;
}

/**
 * Copy one of the theme's bundled images into the media library.
 *
 * The same file is only ever imported once — a second call finds the existing
 * attachment by the marker meta and reuses it.
 *
 * @param string $filename File name inside assets/img.
 * @return int Attachment ID, or 0 on failure.
 */
function ef_import_image( $filename ) {
	if ( ! $filename ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'fields'         => 'ids',
			'meta_key'       => '_ef_source',       // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $filename,          // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = get_template_directory() . '/assets/img/' . $filename;
	if ( ! file_exists( $source ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_bits( $filename, null, file_get_contents( $source ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- bundled theme file.
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $filename )['type'],
			'post_title'     => pathinfo( $filename, PATHINFO_FILENAME ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_ef_source', $filename );

	return (int) $id;
}

/**
 * Turn seeded paragraphs into block-editor content.
 *
 * @param array $paragraphs Paragraph strings; inline <strong>/<em> is kept.
 * @return string
 */
function ef_seed_blocks( $paragraphs ) {
	$allowed = array(
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
	);

	$out = '';
	foreach ( (array) $paragraphs as $para ) {
		$out .= "<!-- wp:paragraph -->\n<p>" . wp_kses( $para, $allowed ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}

	return trim( $out );
}

/**
 * Create or update one post, matched by slug.
 *
 * @param string $type Post type.
 * @param string $slug Post slug.
 * @param array  $data Fields for wp_insert_post().
 * @return int Post ID.
 */
function ef_upsert_post( $type, $slug, $data ) {
	$existing = get_posts(
		array(
			'post_type'        => $type,
			'name'             => $slug,
			'posts_per_page'   => 1,
			'post_status'      => 'any',
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	$data['post_type']   = $type;
	$data['post_name']   = $slug;
	$data['post_status'] = 'publish';

	if ( $existing ) {
		$data['ID'] = (int) $existing[0];
		// Body copy is the editor's to own once the site is live; only fill it
		// in while the post is still empty.
		$current = get_post( $data['ID'] );
		if ( trim( $current->post_content ) ) {
			unset( $data['post_content'] );
		}
		return (int) wp_update_post( $data );
	}

	return (int) wp_insert_post( $data );
}

/**
 * Build the whole site structure from the seed file.
 *
 * @return array|WP_Error Counts on success.
 */
function ef_run_setup() {
	$seed = ef_seed_data();
	if ( is_wp_error( $seed ) ) {
		return $seed;
	}

	$made  = array(
		'pages'    => 0,
		'services' => 0,
		'images'   => 0,
	);
	$byslug = array();

	// -- pages ---------------------------------------------------------------
	foreach ( $seed['pages'] as $page ) {
		$id = ef_upsert_post(
			'page',
			$page['slug'],
			array(
				'post_title'   => $page['title'],
				'post_content' => ef_seed_blocks( $page['body'] ),
			)
		);
		if ( ! $id ) {
			continue;
		}

		++$made['pages'];
		$byslug[ $page['slug'] ] = $id;

		update_post_meta( $id, 'ef_page_en', $page['en'] );
		update_post_meta( $id, 'ef_page_lead', $page['lead'] );

		if ( ! empty( $page['image'] ) && ! has_post_thumbnail( $id ) ) {
			$img = ef_import_image( $page['image'] );
			if ( $img ) {
				set_post_thumbnail( $id, $img );
				++$made['images'];
			}
		}
	}

	// -- services ------------------------------------------------------------
	foreach ( $seed['services'] as $svc ) {
		$id = ef_upsert_post(
			'service',
			$svc['slug'],
			array(
				'post_title'   => $svc['title'],
				'post_excerpt' => $svc['excerpt'],
				'post_content' => ef_seed_blocks( $svc['content'] ),
				'menu_order'   => $svc['menu_order'],
			)
		);
		if ( ! $id ) {
			continue;
		}

		++$made['services'];

		update_post_meta( $id, 'ef_service_en', $svc['en'] );
		update_post_meta( $id, 'ef_service_catch', $svc['catch'] );
		update_post_meta( $id, 'ef_service_sub', $svc['sub'] );
		update_post_meta( $id, 'ef_service_fit', $svc['fit'] );
		update_post_meta( $id, 'ef_service_outro_title', $svc['outro_title'] );
		update_post_meta( $id, 'ef_service_outro', implode( "\n\n", $svc['outro'] ) );

		if ( ! has_post_thumbnail( $id ) ) {
			$img = ef_import_image( $svc['image'] );
			if ( $img ) {
				set_post_thumbnail( $id, $img );
				++$made['images'];
			}
		}

		foreach ( $svc['merits'] as $i => $merit ) {
			$n = $i + 1;
			update_post_meta( $id, "ef_merit{$n}_title", $merit['title'] );
			update_post_meta( $id, "ef_merit{$n}_text", $merit['text'] );
			update_post_meta( $id, "ef_merit{$n}_fit", $merit['fit'] );

			if ( $merit['image'] ) {
				$img = ef_import_image( $merit['image'] );
				if ( $img ) {
					update_post_meta( $id, "ef_merit{$n}_image", $img );
					++$made['images'];
				}
			}
		}

		foreach ( $svc['faq'] as $i => $row ) {
			$n = $i + 1;
			update_post_meta( $id, "ef_faq{$n}_q", $row['q'] );
			update_post_meta( $id, "ef_faq{$n}_a", $row['a'] );
		}
	}

	// -- reading settings ----------------------------------------------------
	if ( isset( $byslug['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $byslug['home'] );
	}
	if ( isset( $byslug['news'] ) ) {
		update_option( 'page_for_posts', $byslug['news'] );
	}

	ef_build_menus( $byslug );

	// Service permalinks only resolve once the CPT's rules are written out.
	flush_rewrite_rules();

	return $made;
}

/**
 * Create the header and drawer menus, if they do not exist yet.
 *
 * @param array $byslug Page IDs keyed by slug.
 */
function ef_build_menus( $byslug ) {
	$name     = __( 'メインメニュー', 'eight-fields' );
	$existing = wp_get_nav_menu_object( $name );
	if ( $existing ) {
		// Someone has already arranged the menu; leave their order alone.
		ef_assign_menu( $existing->term_id );
		return;
	}

	$menu_id = wp_create_nav_menu( $name );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	// The small English word under each drawer label is the menu item's
	// description, so it stays editable from 外観 → メニュー.
	$items = array(
		array( 'home', 'home', 'HOME' ),
		array( 'page', 'company', 'COMPANY' ),
		array( 'page', 'greeting', 'GREETING' ),
		array( 'archive', 'service', 'SERVICE' ),
		array( 'page', 'news', 'NEWS' ),
		array( 'page', 'contact', 'CONTACT' ),
	);

	$order = 0;
	foreach ( $items as $item ) {
		++$order;

		if ( 'home' === $item[0] ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'       => __( 'ホーム', 'eight-fields' ),
					'menu-item-url'         => home_url( '/' ),
					'menu-item-type'        => 'custom',
					'menu-item-description' => $item[2],
					'menu-item-status'      => 'publish',
					'menu-item-position'    => $order,
				)
			);
			continue;
		}

		if ( 'archive' === $item[0] ) {
			$parent = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'       => __( 'サービス', 'eight-fields' ),
					'menu-item-type'        => 'post_type_archive',
					'menu-item-object'      => 'service',
					'menu-item-description' => $item[2],
					'menu-item-status'      => 'publish',
					'menu-item-position'    => $order,
				)
			);

			// The drawer opens the six services under this row, which the walker
			// builds from the menu's own children rather than from the post type.
			// Adding them here keeps the menu screen the place to change them.
			if ( ! is_wp_error( $parent ) ) {
				$services = get_posts(
					array(
						'post_type'      => 'service',
						'posts_per_page' => -1,
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
					)
				);
				foreach ( $services as $child ) {
					++$order;
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object-id' => $child->ID,
							'menu-item-object'    => 'service',
							'menu-item-type'      => 'post_type',
							'menu-item-parent-id' => $parent,
							'menu-item-status'    => 'publish',
							'menu-item-position'  => $order,
						)
					);
				}
			}
			continue;
		}

		if ( empty( $byslug[ $item[1] ] ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id'   => $byslug[ $item[1] ],
				'menu-item-object'      => 'page',
				'menu-item-type'        => 'post_type',
				'menu-item-description' => $item[2],
				'menu-item-status'      => 'publish',
				'menu-item-position'    => $order,
			)
		);
	}

	ef_assign_menu( $menu_id );
}

/**
 * Point every registered menu location at one menu.
 *
 * @param int $menu_id Menu term ID.
 */
function ef_assign_menu( $menu_id ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( array_keys( get_registered_nav_menus() ) as $location ) {
		if ( empty( $locations[ $location ] ) ) {
			$locations[ $location ] = (int) $menu_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Render the setup screen.
 */
function ef_render_setup_page() {
	$done = null;

	if ( isset( $_POST['ef_setup_nonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ef_setup_nonce'] ) ), 'ef_run_setup' )
		&& current_user_can( 'edit_theme_options' ) ) {
		$done = ef_run_setup();
	}

	$seed = ef_seed_data();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'エイトフィールズ 初期セットアップ', 'eight-fields' ); ?></h1>

		<?php if ( is_wp_error( $done ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $done->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $done ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: page count, 2: service count, 3: image count */
						esc_html__( '完了しました。固定ページ %1$d 件、サービス %2$d 件、画像 %3$d 点を登録しました。', 'eight-fields' ),
						(int) $done['pages'],
						(int) $done['services'],
						(int) $done['images']
					);
					?>
				</p>
				<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'サイトを表示', 'eight-fields' ); ?></a></p>
			</div>
		<?php endif; ?>

		<p><?php esc_html_e( 'デザイン案と同じ構成のページ・サービス・メニューをまとめて作成します。', 'eight-fields' ); ?></p>

		<h2><?php esc_html_e( '作成されるもの', 'eight-fields' ); ?></h2>
		<ul class="ul-disc">
			<li><?php esc_html_e( '固定ページ：ホーム／会社概要／ごあいさつ／お知らせ／お問い合わせ', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'サービス（CPT）：6件（キャッチコピー・メリット・よくあるご質問つき）', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'アイキャッチ画像：テーマ同梱の写真をメディアライブラリに取り込みます', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'メニュー：ヘッダーとハンバーガーに「メインメニュー」を設定', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '表示設定：ホームをトップページ、お知らせを投稿ページに設定', 'eight-fields' ); ?></li>
		</ul>

		<h2><?php esc_html_e( '注意', 'eight-fields' ); ?></h2>
		<ul class="ul-disc">
			<li><?php esc_html_e( '何度実行しても同じページが増えることはありません（スラッグで照合して更新します）。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '本文をすでに編集したページは、本文を上書きしません。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'メニューを並べ替えたあとは、その順序を保ちます。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '写真はデザイン案の仮素材です。公開前に差し替えてください。', 'eight-fields' ); ?></li>
		</ul>

		<?php if ( is_wp_error( $seed ) ) : ?>
			<div class="notice notice-error inline"><p><?php echo esc_html( $seed->get_error_message() ); ?></p></div>
		<?php else : ?>
			<form method="post">
				<?php wp_nonce_field( 'ef_run_setup', 'ef_setup_nonce' ); ?>
				<p class="submit">
					<button type="submit" class="button button-primary button-hero">
						<?php esc_html_e( '初期コンテンツを作成する', 'eight-fields' ); ?>
					</button>
				</p>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
