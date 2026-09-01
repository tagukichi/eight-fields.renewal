<?php
/**
 * Fill the service pages' ACF fields with the copy from the current site.
 *
 * The body of each service page was transcribed from eight-fields.co.jp and
 * ships with the theme. This screen writes it into the「本文セクション」repeater
 * so the text is there to edit in the ACF panel, rather than having to be typed
 * back in by hand.
 *
 * It is deliberately separate from 初期セットアップ: it touches nothing but the
 * six service posts' custom fields — no pages, no menus, no site settings.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the screen under 外観.
 */
function ef_add_service_text_page() {
	add_theme_page(
		__( 'サービス本文の流し込み', 'eight-fields' ),
		__( 'サービス本文の流し込み', 'eight-fields' ),
		'edit_theme_options',
		'ef-service-text',
		'ef_render_service_text_page'
	);
}
add_action( 'admin_menu', 'ef_add_service_text_page' );

/**
 * Find the service post a seed entry belongs to.
 *
 * The slug is the reliable handle, but a site whose services were made by hand
 * may have named them differently, so the title and the English label are tried
 * as well before giving up.
 *
 * @param array $svc Seed service entry.
 * @return int Post ID, or 0.
 */
function ef_match_service( $svc ) {
	$id = ef_find_by_slug( 'service', $svc['slug'] );
	if ( $id ) {
		return $id;
	}

	$by_title = get_posts(
		array(
			'post_type'      => 'service',
			'title'          => $svc['title'],
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);
	if ( $by_title ) {
		return (int) $by_title[0];
	}

	if ( ! empty( $svc['en'] ) ) {
		$found = get_posts(
			array(
				'post_type'      => 'service',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'meta_key'       => 'ef_service_en', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- six posts, admin screen.
				'meta_value'     => $svc['en'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- as above.
			)
		);
		if ( $found ) {
			return (int) $found[0];
		}
	}

	return 0;
}

/**
 * What the fill would do to each service, without doing it.
 *
 * @return array|WP_Error Rows of service state.
 */
function ef_plan_service_text() {
	$seed = ef_seed_data();
	if ( is_wp_error( $seed ) ) {
		return $seed;
	}

	$rows = array();

	foreach ( $seed['services'] as $svc ) {
		$id  = ef_match_service( $svc );
		$now = $id ? (int) get_post_meta( $id, 'ef_sections', true ) : 0;

		$rows[] = array(
			'title'   => $svc['title'],
			'slug'    => $svc['slug'],
			'id'      => $id,
			'edit'    => $id ? get_edit_post_link( $id, '' ) : '',
			'seeded'  => count( $svc['sections'] ),
			'current' => $now,
		);
	}

	return $rows;
}

/**
 * Write the bundled copy into the services' ACF fields.
 *
 * @param bool $overwrite Replace text that is already in the fields.
 * @return array|WP_Error Rows of array( title, rows, note ).
 */
function ef_fill_service_text( $overwrite = false ) {
	$seed = ef_seed_data();
	if ( is_wp_error( $seed ) ) {
		return $seed;
	}

	$done = array();

	foreach ( $seed['services'] as $svc ) {
		$id = ef_match_service( $svc );

		if ( ! $id ) {
			$done[] = array(
				'title' => $svc['title'],
				'rows'  => 0,
				'note'  => __( 'この名前・スラッグのサービスが見つかりませんでした。', 'eight-fields' ),
			);
			continue;
		}

		if ( ! $svc['sections'] ) {
			$done[] = array(
				'title' => $svc['title'],
				'rows'  => 0,
				'note'  => __( 'テーマ側に流し込む本文がありません。', 'eight-fields' ),
			);
			continue;
		}

		$before  = (int) get_post_meta( $id, 'ef_sections', true );
		$written = ef_seed_sections( $id, $svc['sections'], $overwrite );

		// The short fields above the body are part of the same copy.
		ef_seed_meta( $id, 'ef_service_en', $svc['en'], $overwrite );
		ef_seed_meta( $id, 'ef_service_catch', $svc['catch'], $overwrite );
		ef_seed_meta( $id, 'ef_service_sub', $svc['sub'], $overwrite );

		foreach ( $svc['faq'] as $i => $faq ) {
			$n = $i + 1;
			ef_seed_meta( $id, "ef_faq{$n}_q", $faq['q'], $overwrite );
			ef_seed_meta( $id, "ef_faq{$n}_a", $faq['a'], $overwrite );
		}

		$done[] = array(
			'title' => $svc['title'],
			'rows'  => $written,
			'note'  => $written
				? ''
				: sprintf(
					/* translators: %d: number of section rows already in the field */
					__( 'すでに %d 件入っているのでそのままにしました。', 'eight-fields' ),
					$before
				),
		);
	}

	return $done;
}

/**
 * Render the screen.
 */
function ef_render_service_text_page() {
	$done = null;

	if ( isset( $_POST['ef_text_nonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ef_text_nonce'] ) ), 'ef_fill_service_text' )
		&& current_user_can( 'edit_theme_options' ) ) {
		$done = ef_fill_service_text( ! empty( $_POST['ef_text_overwrite'] ) );
	}

	$plan = ef_plan_service_text();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'サービス本文の流し込み', 'eight-fields' ); ?></h1>

		<?php if ( is_wp_error( $done ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $done->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $done ) ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( '流し込みました。', 'eight-fields' ); ?></strong></p>
				<ul class="ul-disc">
					<?php foreach ( $done as $row ) : ?>
						<li>
							<?php echo esc_html( $row['title'] ); ?>：
							<?php
							if ( $row['rows'] ) {
								printf(
									/* translators: %d: number of section rows written */
									esc_html__( '%d 件のセクションを入れました。', 'eight-fields' ),
									(int) $row['rows']
								);
							} else {
								echo esc_html( $row['note'] );
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( is_wp_error( $plan ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $plan->get_error_message() ); ?></p></div>
			<?php
			return;
		endif;
		?>

		<p style="max-width:820px">
			<?php esc_html_e( '現行サイトから書き起こした各サービスの本文を、サービスの編集画面にある「本文セクション」に入れます。入れたあとは、いつもどおり編集画面から直せます。', 'eight-fields' ); ?>
		</p>

		<table class="widefat striped" style="max-width:820px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'サービス', 'eight-fields' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'スラッグ', 'eight-fields' ); ?></th>
					<th style="width:110px"><?php esc_html_e( '現在', 'eight-fields' ); ?></th>
					<th style="width:110px"><?php esc_html_e( '入れる数', 'eight-fields' ); ?></th>
					<th style="width:230px"><?php esc_html_e( '動作', 'eight-fields' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $plan as $row ) : ?>
					<tr>
						<td>
							<?php if ( $row['edit'] ) : ?>
								<a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $row['title'] ); ?>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( $row['slug'] ); ?></code></td>
						<td><?php echo esc_html( $row['id'] ? (string) $row['current'] : '—' ); ?></td>
						<td><?php echo esc_html( (string) $row['seeded'] ); ?></td>
						<td>
							<?php if ( ! $row['id'] ) : ?>
								<span style="color:#b32d2e"><?php esc_html_e( '見つかりません', 'eight-fields' ); ?></span>
							<?php elseif ( ! $row['seeded'] ) : ?>
								<span style="color:#646970"><?php esc_html_e( 'テーマ側に本文なし', 'eight-fields' ); ?></span>
							<?php elseif ( $row['current'] ) : ?>
								<span style="color:#8a6d00"><?php esc_html_e( 'すでに入っています（上書き時のみ）', 'eight-fields' ); ?></span>
							<?php else : ?>
								<span style="color:#1d6f42"><?php esc_html_e( '入れます', 'eight-fields' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'この画面がすること・しないこと', 'eight-fields' ); ?></h2>
		<ul class="ul-disc" style="max-width:820px">
			<li><?php esc_html_e( '変更するのはサービスのカスタムフィールドだけです。タイトル・本文エディタ・アイキャッチ画像・公開状態は触りません。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( '固定ページ・メニュー・サイト設定も変更しません。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'セクション内の画像は空のままです。編集画面から選んでください。', 'eight-fields' ); ?></li>
			<li><?php esc_html_e( 'すでに入力済みの項目は、上書きにチェックしない限りそのままです。', 'eight-fields' ); ?></li>
		</ul>

		<form method="post">
			<?php wp_nonce_field( 'ef_fill_service_text', 'ef_text_nonce' ); ?>

			<p style="margin:20px 0 8px">
				<label>
					<input type="checkbox" name="ef_text_overwrite" value="1">
					<strong><?php esc_html_e( 'すでに入っている本文も入れ替える', 'eight-fields' ); ?></strong>
				</label>
			</p>
			<p class="description" style="max-width:820px;margin-bottom:20px">
				<?php esc_html_e( '編集画面で直したあとにチェックして実行すると、その修正はテーマ側の文面に戻ります。', 'eight-fields' ); ?>
			</p>

			<p class="submit">
				<button type="submit" class="button button-primary button-hero">
					<?php esc_html_e( '本文をACFに入れる', 'eight-fields' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}
