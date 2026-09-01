<?php
/**
 * Custom post type: service.
 *
 * If `service` is already registered by a plugin (CPT UI などで登録済みの場合)、
 * この登録はスキップされます。テーマ単体でも動くようにするための保険です。
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the `service` post type when nothing else has.
 */
function ef_register_service_post_type() {
	if ( post_type_exists( 'service' ) ) {
		return;
	}

	$GLOBALS['ef_service_owned'] = true;

	register_post_type(
		'service',
		array(
			'label'         => __( 'サービス', 'eight-fields' ),
			'labels'        => array(
				'name'          => __( 'サービス', 'eight-fields' ),
				'singular_name' => __( 'サービス', 'eight-fields' ),
				'add_new_item'  => __( 'サービスを追加', 'eight-fields' ),
				'edit_item'     => __( 'サービスを編集', 'eight-fields' ),
				'all_items'     => __( 'サービス一覧', 'eight-fields' ),
			),
			'public'        => true,
			'has_archive'   => 'service_',
			'menu_icon'     => 'dashicons-lightbulb',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'rewrite'       => array(
				'slug'       => 'service',
				'with_front' => false,
			),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'ef_register_service_post_type' );

/**
 * Flush rewrite rules once after the theme is switched on.
 */
function ef_flush_rewrite_rules() {
	ef_register_service_post_type();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'ef_flush_rewrite_rules' );

/**
 * Check the `service` post type actually in effect against what the templates need.
 *
 * When a plugin (ACF, CPT UI …) registers `service` first, the theme steps aside
 * and that registration is what the site runs on. The templates depend on a few
 * of its settings — the archive slug the design's URLs are built from, and the
 * fields the cards read — so a mismatch shows up as a 404 or an empty card
 * rather than an error. This turns that into something readable.
 *
 * @return array {
 *     @type string   $owner    'theme' or 'plugin'.
 *     @type array[]  $problems Rows of array( label, expected, actual ).
 * }
 */
function ef_service_cpt_report() {
	$object = get_post_type_object( 'service' );
	if ( ! $object ) {
		return array(
			'owner'    => 'none',
			'problems' => array(
				array(
					__( 'カスタム投稿タイプ', 'eight-fields' ),
					'service',
					__( '登録されていません', 'eight-fields' ),
				),
			),
		);
	}

	$owner    = ef_service_registered_by_theme() ? 'theme' : 'plugin';
	$problems = array();

	// An archive at some other slug still works — every link in the theme is
	// built with get_post_type_archive_link(), so it follows whatever is set.
	// Only having no archive at all breaks things.
	if ( ! $object->has_archive ) {
		$problems[] = array(
			__( 'アーカイブ（has_archive）', 'eight-fields' ),
			__( '有効', 'eight-fields' ),
			__( 'アーカイブなし', 'eight-fields' ),
			__( 'サービス一覧ページが404になり、ヘッダーとハンバーガーの「サービス」もリンクになりません。', 'eight-fields' ),
		);
	}

	$slug = isset( $object->rewrite['slug'] ) ? $object->rewrite['slug'] : '';
	if ( 'service' !== $slug ) {
		$problems[] = array(
			__( '個別ページのURL（rewrite slug）', 'eight-fields' ),
			'service',
			$slug ? $slug : __( '未設定', 'eight-fields' ),
			__( '各サービスのURLが現行サイトと変わります。', 'eight-fields' ),
		);
	}

	// The card text and the 01〜06 ordering come from these two, and ACF leaves
	// both off by default — the page still renders, just emptier than intended.
	

	$needed = array(
		'title'           => __( 'タイトル', 'eight-fields' ),
		'editor'          => __( '本文', 'eight-fields' ),
		'excerpt'         => __( '抜粋（カードの説明文）', 'eight-fields' ),
		'thumbnail'       => __( 'アイキャッチ画像', 'eight-fields' ),
		'page-attributes' => __( 'ページ属性（並び順）', 'eight-fields' ),
	);
	$hurts = array(
		'title'           => __( 'サービス名が入力できません。', 'eight-fields' ),
		'editor'          => __( 'サービス詳細の本文が入力できません。', 'eight-fields' ),
		'excerpt'         => __( 'サービスカードの説明文が空になります。', 'eight-fields' ),
		'thumbnail'       => __( 'カードとページ上部の写真が出ません。', 'eight-fields' ),
		'page-attributes' => __( '01〜06 の並び順が制御できません。', 'eight-fields' ),
	);
	foreach ( $needed as $feature => $label ) {
		if ( ! post_type_supports( 'service', $feature ) ) {
			$problems[] = array(
				/* translators: %s: the editor panel name */
				sprintf( __( 'サポート項目：%s', 'eight-fields' ), $label ),
				__( '有効', 'eight-fields' ),
				__( '無効', 'eight-fields' ),
				$hurts[ $feature ],
			);
		}
	}

	// A post type registered with these switched off vanishes from the admin
	// menu and 404s on the front end — the two symptoms look unrelated but come
	// from the same setting, so they are worth naming separately.
	$flags = array(
		'public'        => __( '公開する（public）', 'eight-fields' ),
		'show_ui'       => __( '管理画面で編集できる（show_ui）', 'eight-fields' ),
		'show_in_menu'  => __( '管理メニューに表示（show_in_menu）', 'eight-fields' ),
		'publicly_queryable' => __( 'フロントで表示（publicly_queryable）', 'eight-fields' ),
	);
	foreach ( $flags as $flag => $label ) {
		if ( empty( $object->$flag ) ) {
			$problems[] = array(
				$label,
				__( '有効', 'eight-fields' ),
				__( '無効', 'eight-fields' ),
				__( 'サービスが管理画面やサイト上に表示されません。', 'eight-fields' ),
			);
		}
	}

	return array(
		'owner'    => $owner,
		'problems' => $problems,
	);
}

/**
 * Whether the theme is the one that registered `service`.
 *
 * Set while registering, so this reports what actually happened this request
 * rather than inferring it from the post type object.
 *
 * @return bool
 */
function ef_service_registered_by_theme() {
	return ! empty( $GLOBALS['ef_service_owned'] );
}

/**
 * Print the `service` compatibility report, when there is something to say.
 *
 * Shown on the setup screen and above the service list, which are the two
 * places someone would be looking when the archive turns out to be a 404.
 */
function ef_render_service_cpt_notice() {
	$report = ef_service_cpt_report();

	if ( 'theme' === $report['owner'] || ! $report['problems'] ) {
		return;
	}

	echo '<div class="notice notice-warning"><p><strong>';
	esc_html_e( 'カスタム投稿タイプ「service」は、ほかのプラグイン（ACF など）で登録されています。', 'eight-fields' );
	echo '</strong></p><p>';
	esc_html_e( 'テーマは二重登録を避けるため自分では登録しません。デザインどおりに表示するには、プラグイン側の設定を次のように合わせてください。', 'eight-fields' );
	echo '</p><table class="widefat striped" style="max-width:920px;margin-bottom:12px"><thead><tr>';
	echo '<th>' . esc_html__( '設定', 'eight-fields' ) . '</th>';
	echo '<th style="width:150px">' . esc_html__( '必要な値', 'eight-fields' ) . '</th>';
	echo '<th style="width:150px">' . esc_html__( '現在の値', 'eight-fields' ) . '</th>';
	echo '<th>' . esc_html__( 'このままだと', 'eight-fields' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $report['problems'] as $row ) {
		echo '<tr><td>' . esc_html( $row[0] ) . '</td>';
		echo '<td><code>' . esc_html( $row[1] ) . '</code></td>';
		echo '<td style="color:#b32d2e">' . esc_html( $row[2] ) . '</td>';
		echo '<td>' . esc_html( isset( $row[3] ) ? $row[3] : '' ) . '</td></tr>';
	}

	echo '</tbody></table><p>';
	esc_html_e( '設定を変えたあとは「設定 → パーマリンク」で一度保存してください。', 'eight-fields' );
	echo '</p></div>';
}

/**
 * Also warn on the service list screen, where the problem shows up first.
 */
function ef_service_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'service' !== $screen->post_type || 'edit' !== $screen->base ) {
		return;
	}
	ef_render_service_cpt_notice();
}
add_action( 'admin_notices', 'ef_service_admin_notice' );
