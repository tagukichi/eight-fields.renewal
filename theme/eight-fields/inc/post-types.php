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
