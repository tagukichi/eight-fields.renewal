<?php
/**
 * One-click setup: create the pages, services and menus the design expects.
 *
 * A fresh WordPress has none of the structure the templates rely on — no
 * 会社概要 page, no `service` posts, no menus — so the site looks broken until
 * someone builds it by hand. This screen does that build in one press, using
 * the same content the design proposal was made from.
 *
 * It is safe to run more than once, and safe to run on a site that already has
 * content: posts are matched by slug and never duplicated, and by default an
 * existing page keeps its title, its body and its settings — only empty fields
 * are filled in. The screen offers an explicit overwrite mode for a site being
 * built from nothing.
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
 * Find a post by slug, whatever its status.
 *
 * @param string $type Post type.
 * @param string $slug Post slug.
 * @return int Post ID, or 0 when there is none.
 */
function ef_find_by_slug( $type, $slug ) {
	$found = get_posts(
		array(
			'post_type'        => $type,
			'name'             => $slug,
			'posts_per_page'   => 1,
			'post_status'      => 'any',
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Create one post, or fill in the gaps on the one that is already there.
 *
 * On a live site a page with the slug `company` is the client's existing 会社概要,
 * with their title and their copy. Overwriting it would be vandalism, so in the
 * default mode an existing post keeps everything it already has and this only
 * fills in fields that are empty. `$overwrite` is for a site being built from
 * nothing, where replacing the placeholder content is the point.
 *
 * @param string $type      Post type.
 * @param string $slug      Post slug.
 * @param array  $data      Fields for wp_insert_post().
 * @param bool   $overwrite Replace fields that already have a value.
 * @return int Post ID.
 */
function ef_upsert_post( $type, $slug, $data, $overwrite = false ) {
	$id = ef_find_by_slug( $type, $slug );

	$data['post_type']   = $type;
	$data['post_name']   = $slug;
	$data['post_status'] = 'publish';

	if ( ! $id ) {
		return (int) wp_insert_post( $data );
	}

	$data['ID'] = $id;

	if ( ! $overwrite ) {
		$current = get_post( $id );

		// Anything the editor has already written stays as it is.
		foreach ( array( 'post_title', 'post_content', 'post_excerpt' ) as $field ) {
			if ( isset( $data[ $field ] ) && trim( $current->$field ) ) {
				unset( $data[ $field ] );
			}
		}

		// A published page must not be dragged back to some other status, and a
		// draft the editor is still working on must not be published behind them.
		unset( $data['post_status'] );

		// Their ordering is theirs.
		if ( isset( $data['menu_order'] ) && (int) $current->menu_order ) {
			unset( $data['menu_order'] );
		}
	}

	return (int) wp_update_post( $data );
}

/**
 * Set a meta value, leaving an existing one alone unless overwriting.
 *
 * @param int    $post_id   Post.
 * @param string $key       Meta key.
 * @param string $value     Value to write.
 * @param bool   $overwrite Replace a value that is already there.
 */
function ef_seed_meta( $post_id, $key, $value, $overwrite = false ) {
	if ( '' === $value ) {
		return;
	}
	if ( ! $overwrite && '' !== (string) get_post_meta( $post_id, $key, true ) ) {
		return;
	}
	update_post_meta( $post_id, $key, $value );
}

/**
 * What running the setup would do, without doing it.
 *
 * The screen shows this before the button so nobody presses it blind on a live
 * site.
 *
 * @return array|WP_Error Rows of array( label, slug, action ), plus notes.
 */
function ef_plan_setup() {
	$seed = ef_seed_data();
	if ( is_wp_error( $seed ) ) {
		return $seed;
	}

	$rows = array();

	foreach ( $seed['pages'] as $page ) {
		$id     = ef_find_by_slug( 'page', $page['slug'] );
		$rows[] = array(
			'kind'    => __( '固定ページ', 'eight-fields' ),
			'label'   => $id ? get_the_title( $id ) : $page['title'],
			'slug'    => $page['slug'],
			'action'  => $id ? 'exists' : 'create',
			'edit'    => $id ? get_edit_post_link( $id ) : '',
		);
	}

	foreach ( $seed['services'] as $svc ) {
		$id     = ef_find_by_slug( 'service', $svc['slug'] );
		$rows[] = array(
			'kind'    => __( 'サービス', 'eight-fields' ),
			'label'   => $id ? get_the_title( $id ) : $svc['title'],
			'slug'    => $svc['slug'],
			'action'  => $id ? 'exists' : 'create',
			'edit'    => $id ? get_edit_post_link( $id ) : '',
		);
	}

	$front = (int) get_option( 'page_on_front' );
	$posts = (int) get_option( 'page_for_posts' );

	return array(
		'rows'      => $rows,
		'has_front' => $front > 0,
		'front'     => $front ? get_the_title( $front ) : '',
		'has_posts' => $posts > 0,
		'posts'     => $posts ? get_the_title( $posts ) : '',
		'has_menu'  => (bool) wp_get_nav_menu_object( __( 'メインメニュー', 'eight-fields' ) ),
	);
}

/**
 * Build the whole site structure from the seed file.
 *
 * @param bool $overwrite Replace content that is already there. Off by default,
 *                        so running this on a live site only fills in gaps.
 * @return array|WP_Error Counts on success.
 */
function ef_run_setup( $overwrite = false ) {
	$seed = ef_seed_data();
	if ( is_wp_error( $seed ) ) {
		return $seed;
	}

	$made   = array(
		'created' => 0,
		'updated' => 0,
		'images'  => 0,
	);
	$byslug = array();

	// -- pages ---------------------------------------------------------------
	foreach ( $seed['pages'] as $page ) {
		$existed = (bool) ef_find_by_slug( 'page', $page['slug'] );

		$id = ef_upsert_post(
			'page',
			$page['slug'],
			array(
				'post_title'   => $page['title'],
				'post_content' => ef_seed_blocks( $page['body'] ),
			),
			$overwrite
		);
		if ( ! $id ) {
			continue;
		}

		$made[ $existed ? 'updated' : 'created' ] += 1;
		$byslug[ $page['slug'] ]                   = $id;

		ef_seed_meta( $id, 'ef_page_en', $page['en'], $overwrite );
		ef_seed_meta( $id, 'ef_page_lead', $page['lead'], $overwrite );

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
		$existed = (bool) ef_find_by_slug( 'service', $svc['slug'] );

		$id = ef_upsert_post(
			'service',
			$svc['slug'],
			array(
				'post_title'   => $svc['title'],
				'post_excerpt' => $svc['excerpt'],
				'post_content' => ef_seed_blocks( $svc['content'] ),
				'menu_order'   => $svc['menu_order'],
			),
			$overwrite
		);
		if ( ! $id ) {
			continue;
		}

		$made[ $existed ? 'updated' : 'created' ] += 1;

		ef_seed_meta( $id, 'ef_service_en', $svc['en'], $overwrite );
		ef_seed_meta( $id, 'ef_service_catch', $svc['catch'], $overwrite );
		ef_seed_meta( $id, 'ef_service_sub', $svc['sub'], $overwrite );
		ef_seed_meta( $id, 'ef_service_fit', $svc['fit'], $overwrite );
		ef_seed_meta( $id, 'ef_service_outro_title', $svc['outro_title'], $overwrite );
		ef_seed_meta( $id, 'ef_service_outro', implode( "\n\n", $svc['outro'] ), $overwrite );

		if ( ! has_post_thumbnail( $id ) ) {
			$img = ef_import_image( $svc['image'] );
			if ( $img ) {
				set_post_thumbnail( $id, $img );
				++$made['images'];
			}
		}

		foreach ( $svc['merits'] as $i => $merit ) {
			$n = $i + 1;
			ef_seed_meta( $id, "ef_merit{$n}_title", $merit['title'], $overwrite );
			ef_seed_meta( $id, "ef_merit{$n}_text", $merit['text'], $overwrite );
			ef_seed_meta( $id, "ef_merit{$n}_fit", $merit['fit'], $overwrite );

			if ( $merit['image'] && ( $overwrite || ! get_post_meta( $id, "ef_merit{$n}_image", true ) ) ) {
				$img = ef_import_image( $merit['image'] );
				if ( $img ) {
					update_post_meta( $id, "ef_merit{$n}_image", $img );
					++$made['images'];
				}
			}
		}

		foreach ( $svc['faq'] as $i => $row ) {
			$n = $i + 1;
			ef_seed_meta( $id, "ef_faq{$n}_q", $row['q'], $overwrite );
			ef_seed_meta( $id, "ef_faq{$n}_a", $row['a'], $overwrite );
		}
	}

	// -- reading settings ----------------------------------------------------
	// A site that already names a front page has one for a reason; taking it
	// over would swap the whole homepage out from under the client.
	if ( isset( $byslug['home'] ) && ( $overwrite || ! get_option( 'page_on_front' ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $byslug['home'] );
	}
	if ( isset( $byslug['news'] ) && ( $overwrite || ! get_option( 'page_for_posts' ) ) ) {
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
		$done = ef_run_setup( ! empty( $_POST['ef_overwrite'] ) );
	}

	$plan = ef_plan_setup();
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
						/* translators: 1: created count, 2: updated count, 3: image count */
						esc_html__( '完了しました。新規作成 %1$d 件、既存を更新 %2$d 件、画像 %3$d 点を登録しました。', 'eight-fields' ),
						(int) $done['created'],
						(int) $done['updated'],
						(int) $done['images']
					);
					?>
				</p>
				<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'サイトを表示', 'eight-fields' ); ?></a></p>
			</div>
		<?php endif; ?>

		<?php if ( is_wp_error( $plan ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $plan->get_error_message() ); ?></p></div>
			<?php
			return;
		endif;
		?>

		<p><?php esc_html_e( 'デザイン案と同じ構成のページ・サービス・メニューをまとめて作成します。', 'eight-fields' ); ?></p>

		<?php ef_render_service_cpt_notice(); ?>

		<h2><?php esc_html_e( '実行するとどうなるか', 'eight-fields' ); ?></h2>
		<table class="widefat striped" style="max-width:820px">
			<thead>
				<tr>
					<th style="width:110px"><?php esc_html_e( '種類', 'eight-fields' ); ?></th>
					<th><?php esc_html_e( '対象', 'eight-fields' ); ?></th>
					<th style="width:130px"><?php esc_html_e( 'スラッグ', 'eight-fields' ); ?></th>
					<th style="width:220px"><?php esc_html_e( '動作', 'eight-fields' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $plan['rows'] as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['kind'] ); ?></td>
						<td>
							<?php if ( $row['edit'] ) : ?>
								<a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['label'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $row['label'] ); ?>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( $row['slug'] ); ?></code></td>
						<td>
							<?php if ( 'create' === $row['action'] ) : ?>
								<span style="color:#1d6f42"><?php esc_html_e( '新しく作成します', 'eight-fields' ); ?></span>
							<?php else : ?>
								<span style="color:#8a6d00"><?php esc_html_e( 'すでにあります（空欄のみ補完）', 'eight-fields' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'サイト設定', 'eight-fields' ); ?></h2>
		<ul class="ul-disc">
			<li>
				<?php if ( $plan['has_front'] ) : ?>
					<?php
					printf(
						/* translators: %s: current front page title */
						esc_html__( 'トップページは現在「%s」に設定されています。上書きしない限り変更しません。', 'eight-fields' ),
						esc_html( $plan['front'] )
					);
					?>
				<?php else : ?>
					<?php esc_html_e( 'トップページが未設定なので、固定ページ「ホーム」を割り当てます。', 'eight-fields' ); ?>
				<?php endif; ?>
			</li>
			<li>
				<?php if ( $plan['has_posts'] ) : ?>
					<?php
					printf(
						/* translators: %s: current posts page title */
						esc_html__( '投稿ページは現在「%s」に設定されています。上書きしない限り変更しません。', 'eight-fields' ),
						esc_html( $plan['posts'] )
					);
					?>
				<?php else : ?>
					<?php esc_html_e( '投稿ページが未設定なので、固定ページ「お知らせ」を割り当てます。', 'eight-fields' ); ?>
				<?php endif; ?>
			</li>
			<li>
				<?php if ( $plan['has_menu'] ) : ?>
					<?php esc_html_e( 'メニュー「メインメニュー」はすでにあります。並び順はそのままにします。', 'eight-fields' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'メニュー「メインメニュー」を作り、未設定の表示位置に割り当てます。', 'eight-fields' ); ?>
				<?php endif; ?>
			</li>
			<li><?php esc_html_e( 'アイキャッチ画像は、まだ設定されていないものにだけ入れます。', 'eight-fields' ); ?></li>
		</ul>

		<h2><?php esc_html_e( '安全のために', 'eight-fields' ); ?></h2>
		<ul class="ul-disc">
			<li><?php esc_html_e( '同じページが増えることはありません（スラッグで照合します）。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'すでにあるページのタイトル・本文・抜粋・並び順・公開状態は変更しません。空欄の項目だけを埋めます。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '既存の投稿・カテゴリー・メディアを削除することはありません。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '写真はデザイン案の仮素材です。公開前に差し替えてください。', 'eight-fields' ); ?></li>
		</ul>

		<form method="post">
			<?php wp_nonce_field( 'ef_run_setup', 'ef_setup_nonce' ); ?>

			<p style="margin:20px 0 8px">
				<label>
					<input type="checkbox" name="ef_overwrite" value="1">
					<strong><?php esc_html_e( 'すでにある内容もデザイン案の内容で上書きする', 'eight-fields' ); ?></strong>
				</label>
			</p>
			<p class="description" style="max-width:820px;margin-bottom:20px">
				<?php esc_html_e( '新しく立てたサイトで、中身をデザイン案どおりに揃えたいときだけチェックしてください。既存サイトのテーマを入れ替えた場合は、チェックを外したまま実行してください。', 'eight-fields' ); ?>
			</p>

			<p class="submit">
				<button type="submit" class="button button-primary button-hero">
					<?php esc_html_e( '初期コンテンツを作成する', 'eight-fields' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}
