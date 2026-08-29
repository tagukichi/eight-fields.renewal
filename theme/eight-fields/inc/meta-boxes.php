<?php
/**
 * Editing panels for the pieces of a service page that sit outside the body.
 *
 * A service page is mostly layout: a catch line, three merits, three FAQ rows.
 * Those could live as raw custom fields, but the classic custom-fields panel is
 * unpleasant to work in and easy to typo, so they get a proper meta box.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The fields shown in the service meta box.
 *
 * @return array[] Field definitions, keyed by meta key.
 */
function ef_service_fields() {
	$fields = array(
		'ef_service_en'    => array(
			'label' => __( '英字ラベル', 'eight-fields' ),
			'type'  => 'text',
			'help'  => __( 'カードのバッジと見出しに出ます（例：SOLAR）。', 'eight-fields' ),
		),
		'ef_service_catch' => array(
			'label' => __( 'キャッチコピー', 'eight-fields' ),
			'type'  => 'text',
			'help'  => __( '本文の上に大きく出る一文。空欄ならサービス名が入ります。', 'eight-fields' ),
		),
		'ef_service_sub'   => array(
			'label' => __( 'キャッチの補足', 'eight-fields' ),
			'type'  => 'textarea',
			'help'  => __( '任意。キャッチコピーのすぐ下に、少し大きめの文字で出ます。', 'eight-fields' ),
		),
		'ef_service_fit'   => array(
			'label'   => __( '画像の表示方法', 'eight-fields' ),
			'type'    => 'select',
			'options' => array(
				''        => __( '切り抜いて全面に表示（写真向け）', 'eight-fields' ),
				'contain' => __( '全体を収めて表示（白背景の製品画像・図版向け）', 'eight-fields' ),
			),
		),
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		/* translators: %d: merit number */
		$n = sprintf( __( 'メリット%d', 'eight-fields' ), $i );

		$fields[ "ef_merit{$i}_title" ] = array(
			'label'   => $n . __( '：見出し', 'eight-fields' ),
			'type'    => 'text',
			'section' => 1 === $i ? __( 'メリット', 'eight-fields' ) : '',
		);
		$fields[ "ef_merit{$i}_text" ]  = array(
			'label' => $n . __( '：本文', 'eight-fields' ),
			'type'  => 'textarea',
		);
		$fields[ "ef_merit{$i}_image" ] = array(
			'label' => $n . __( '：画像ID', 'eight-fields' ),
			'type'  => 'text',
			'help'  => 1 === $i
				? __( 'メディアライブラリの画像ID。空欄ならアイキャッチ画像を使います。', 'eight-fields' )
				: __( 'メディアライブラリの画像ID。空欄なら画像なしで、上のメリットとは罫線で区切って並びます。', 'eight-fields' ),
		);
		$fields[ "ef_merit{$i}_fit" ]   = array(
			'label'   => $n . __( '：画像の表示方法', 'eight-fields' ),
			'type'    => 'select',
			'options' => array(
				''        => __( '切り抜いて表示（写真向け）', 'eight-fields' ),
				'contain' => __( '全体を収めて表示（図版向け）', 'eight-fields' ),
			),
		);
	}

	$fields['ef_service_outro_title'] = array(
		'label'   => __( '見出し', 'eight-fields' ),
		'type'    => 'text',
		'section' => __( '補足セクション（任意）', 'eight-fields' ),
		'help'    => __( 'FAQ の前に、もう一段落だけ説明を足したいときに使います。空欄なら表示されません。', 'eight-fields' ),
	);
	$fields['ef_service_outro']       = array(
		'label' => __( '本文', 'eight-fields' ),
		'type'  => 'textarea',
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		/* translators: %d: FAQ number */
		$n = sprintf( __( 'よくあるご質問%d', 'eight-fields' ), $i );

		$fields[ "ef_faq{$i}_q" ] = array(
			'label'   => $n . __( '：質問', 'eight-fields' ),
			'type'    => 'text',
			'section' => 1 === $i ? __( 'よくあるご質問', 'eight-fields' ) : '',
		);
		$fields[ "ef_faq{$i}_a" ] = array(
			'label' => $n . __( '：回答', 'eight-fields' ),
			'type'  => 'textarea',
		);
	}

	return $fields;
}

/**
 * Register the meta box and the meta keys behind it.
 */
function ef_register_service_meta() {
	foreach ( ef_service_fields() as $key => $field ) {
		register_post_meta(
			'service',
			$key,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'ef_register_service_meta' );

/**
 * Add the meta box to the service editor.
 */
function ef_add_service_meta_box() {
	add_meta_box(
		'ef_service_details',
		__( 'サービスページの内容', 'eight-fields' ),
		'ef_render_service_meta_box',
		'service',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ef_add_service_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Service being edited.
 */
function ef_render_service_meta_box( $post ) {
	wp_nonce_field( 'ef_save_service_meta', 'ef_service_meta_nonce' );

	echo '<style>.ef-meta p{margin:0 0 18px}.ef-meta label{display:block;font-weight:600;margin-bottom:4px}'
		. '.ef-meta input[type=text],.ef-meta textarea,.ef-meta select{width:100%}'
		. '.ef-meta textarea{min-height:70px}.ef-meta .description{margin-top:4px}'
		. '.ef-meta h4{margin:26px 0 12px;padding-top:18px;border-top:1px solid #dcdcde;font-size:14px}</style>';
	echo '<div class="ef-meta">';

	foreach ( ef_service_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );

		if ( ! empty( $field['section'] ) ) {
			echo '<h4>' . esc_html( $field['section'] ) . '</h4>';
		}

		echo '<p><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>';

		if ( 'textarea' === $field['type'] ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3">'
				. esc_textarea( $value ) . '</textarea>';
		} elseif ( 'select' === $field['type'] ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $field['options'] as $ef_val => $ef_label ) {
				echo '<option value="' . esc_attr( $ef_val ) . '"' . selected( $value, $ef_val, false ) . '>'
					. esc_html( $ef_label ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="'
				. esc_attr( $value ) . '">';
		}

		if ( ! empty( $field['help'] ) ) {
			echo '<span class="description">' . esc_html( $field['help'] ) . '</span>';
		}
		echo '</p>';
	}

	echo '</div>';
}

/**
 * Persist the meta box.
 *
 * @param int $post_id Service being saved.
 */
function ef_save_service_meta( $post_id ) {
	if ( ! isset( $_POST['ef_service_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ef_service_meta_nonce'] ) ), 'ef_save_service_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( ef_service_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_kses_post( wp_unslash( $_POST[ $key ] ) );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_service', 'ef_save_service_meta' );
