<?php
/**
 * A read-only report of what the site is actually doing.
 *
 * When the service pages misbehave the cause is almost never in the templates —
 * it is in which plugin registered the post type, what arguments it used, and
 * whether the rewrite rules were written out afterwards. None of that is
 * visible from the WordPress admin, so this collects it in one place and makes
 * it copyable.
 *
 * Nothing here changes anything.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the diagnostics screen under 外観.
 */
function ef_add_diagnostics_page() {
	add_theme_page(
		__( 'サイト診断', 'eight-fields' ),
		__( 'サイト診断', 'eight-fields' ),
		'edit_theme_options',
		'ef-diagnostics',
		'ef_render_diagnostics_page'
	);
}
add_action( 'admin_menu', 'ef_add_diagnostics_page' );

/**
 * Post rows in the database grouped by type, including types nothing registers.
 *
 * A post type that no longer has a registration still has its rows sitting in
 * the database — they are invisible in the admin, not deleted. That distinction
 * is the whole question when a custom post type "disappears", so it is reported
 * first.
 *
 * @return array[] Rows of array( type, count, registered, label ).
 */
function ef_diag_post_types() {
	global $wpdb;

	$counts = $wpdb->get_results(
		"SELECT post_type, COUNT(*) AS total
		 FROM {$wpdb->posts}
		 WHERE post_status NOT IN ( 'auto-draft', 'inherit' )
		   AND post_type NOT IN ( 'revision', 'nav_menu_item' )
		 GROUP BY post_type
		 ORDER BY total DESC"
	);

	$rows = array();
	foreach ( $counts as $row ) {
		$object = get_post_type_object( $row->post_type );
		$rows[] = array(
			'type'       => $row->post_type,
			'count'      => (int) $row->total,
			'registered' => (bool) $object,
			'label'      => $object ? $object->labels->name : '',
			'visible'    => $object ? ! empty( $object->show_ui ) : false,
		);
	}

	return $rows;
}

/**
 * The rewrite rules that currently point at the service post type.
 *
 * @return array Rules keyed by pattern.
 */
function ef_diag_service_rules() {
	$rules = get_option( 'rewrite_rules' );
	if ( ! is_array( $rules ) ) {
		return array();
	}

	$found = array();
	foreach ( $rules as $pattern => $target ) {
		if ( false !== strpos( $target, 'post_type=service' ) || false !== strpos( $target, 'service=' ) ) {
			$found[ $pattern ] = $target;
		}
	}

	return $found;
}

/**
 * What each service page is actually built with, and what renders it.
 *
 * A builder that takes over the page template (Elementor's Canvas and Full
 * Width layouts do) means the theme's own service template never runs — so no
 * amount of theme work changes what is shown. That is invisible from the admin,
 * so it is reported here per service.
 *
 * @return array[] Rows of array( title, edit, template, builder, sections ).
 */
function ef_diag_services() {
	$rows = array();

	foreach ( get_posts( array( 'post_type' => 'service', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $post ) {
		$builders = array();
		if ( get_post_meta( $post->ID, 'panels_data', true ) ) {
			$builders[] = 'SiteOrigin';
		}
		if ( 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true ) ) {
			$builders[] = 'Elementor';
		}
		if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
			$builders[] = 'Beaver Builder';
		}

		// A page template set on the post wins over the theme's single-service.php.
		$template = get_post_meta( $post->ID, '_wp_page_template', true );

		$rows[] = array(
			'title'    => get_the_title( $post ),
			'edit'     => get_edit_post_link( $post->ID ),
			'template' => $template && 'default' !== $template ? $template : 'single-service.php',
			'builder'  => $builders,
			'sections' => count( ef_service_sections( $post->ID ) ),
			'catch'    => (string) get_post_meta( $post->ID, 'ef_service_catch', true ),
		);
	}

	return $rows;
}

/**
 * Render the diagnostics screen.
 */
function ef_render_diagnostics_page() {
	$object   = get_post_type_object( 'service' );
	$types    = ef_diag_post_types();
	$rules    = ef_diag_service_rules();
	$orphans  = array_filter(
		$types,
		function ( $row ) {
			return ! $row['registered'];
		}
	);
	$hidden   = array_filter(
		$types,
		function ( $row ) {
			return $row['registered'] && ! $row['visible'];
		}
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'サイト診断', 'eight-fields' ); ?></h1>
		<p><?php esc_html_e( 'サービスページの表示がおかしいときに、原因の切り分けに使う画面です。この画面は状態を読み取るだけで、何も変更しません。', 'eight-fields' ); ?></p>

		<?php if ( $orphans ) : ?>
			<div class="notice notice-error">
				<p><strong><?php esc_html_e( '管理画面から見えなくなっている投稿があります。', 'eight-fields' ); ?></strong></p>
				<p><?php esc_html_e( 'これらの投稿タイプは、いまどのテーマ・プラグインからも登録されていません。データは残っていますが、登録が復活するまで管理画面にも表示されません。', 'eight-fields' ); ?></p>
				<ul class="ul-disc">
					<?php foreach ( $orphans as $row ) : ?>
						<li>
							<code><?php echo esc_html( $row['type'] ); ?></code>
							<?php
							printf(
								/* translators: %d: number of posts */
								esc_html__( ' — %d件', 'eight-fields' ),
								(int) $row['count']
							);
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $hidden ) : ?>
			<div class="notice notice-warning">
				<p><strong><?php esc_html_e( '登録はされていますが、管理メニューに出ない設定になっている投稿タイプがあります。', 'eight-fields' ); ?></strong></p>
				<ul class="ul-disc">
					<?php foreach ( $hidden as $row ) : ?>
						<li><code><?php echo esc_html( $row['type'] ); ?></code>（show_ui が無効）</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'サービス各ページの状態', 'eight-fields' ); ?></h2>
		<p><?php esc_html_e( '「テンプレート」が single-service.php 以外になっている場合、テーマのデザインは使われません。ページビルダーが独自のテンプレートを適用していないかご確認ください。', 'eight-fields' ); ?></p>
		<table class="widefat striped" style="max-width:920px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'サービス', 'eight-fields' ); ?></th>
					<th style="width:190px"><?php esc_html_e( 'テンプレート', 'eight-fields' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'ページビルダー', 'eight-fields' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'セクション数', 'eight-fields' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( ef_diag_services() as $ef_svc ) : ?>
					<tr>
						<td>
							<?php if ( $ef_svc['edit'] ) : ?>
								<a href="<?php echo esc_url( $ef_svc['edit'] ); ?>"><?php echo esc_html( $ef_svc['title'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $ef_svc['title'] ); ?>
							<?php endif; ?>
						</td>
						<td>
							<code><?php echo esc_html( $ef_svc['template'] ); ?></code>
							<?php if ( 'single-service.php' !== $ef_svc['template'] ) : ?>
								<br><span style="color:#b32d2e"><?php esc_html_e( '← テーマのデザインが使われません', 'eight-fields' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $ef_svc['builder'] ? esc_html( implode( ', ', $ef_svc['builder'] ) ) : esc_html__( 'なし', 'eight-fields' ); ?>
						</td>
						<td>
							<?php echo (int) $ef_svc['sections']; ?>
							<?php if ( ! $ef_svc['sections'] ) : ?>
								<br><span style="color:#8a6d00"><?php esc_html_e( '未入力', 'eight-fields' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'カスタム投稿タイプ service', 'eight-fields' ); ?></h2>
		<table class="widefat striped" style="max-width:820px">
			<tbody>
				<tr>
					<th style="width:220px"><?php esc_html_e( '登録状況', 'eight-fields' ); ?></th>
					<td>
						<?php if ( ! $object ) : ?>
							<span style="color:#b32d2e"><?php esc_html_e( '登録されていません', 'eight-fields' ); ?></span>
						<?php elseif ( ef_service_registered_by_theme() ) : ?>
							<?php esc_html_e( 'このテーマが登録しています', 'eight-fields' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'テーマ以外（ACF・CPT UI などのプラグイン）が登録しています', 'eight-fields' ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ( $object ) : ?>
					<tr>
						<th><?php esc_html_e( 'アーカイブ（has_archive）', 'eight-fields' ); ?></th>
						<td><code><?php echo esc_html( $object->has_archive ? $object->has_archive : 'false' ); ?></code>
							<?php if ( 'service_' !== $object->has_archive ) : ?>
								<span style="color:#b32d2e"><?php esc_html_e( '← service_ である必要があります', 'eight-fields' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'パーマリンク（rewrite slug）', 'eight-fields' ); ?></th>
						<td><code><?php echo esc_html( isset( $object->rewrite['slug'] ) ? $object->rewrite['slug'] : 'なし' ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'サポート項目', 'eight-fields' ); ?></th>
						<td><code><?php echo esc_html( implode( ', ', array_keys( get_all_post_type_supports( 'service' ) ) ) ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( '表示フラグ', 'eight-fields' ); ?></th>
						<td>
							<code>
								public=<?php echo $object->public ? 'true' : 'false'; ?>,
								show_ui=<?php echo $object->show_ui ? 'true' : 'false'; ?>,
								show_in_menu=<?php echo $object->show_in_menu ? 'true' : 'false'; ?>,
								publicly_queryable=<?php echo $object->publicly_queryable ? 'true' : 'false'; ?>
							</code>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'データベース内の投稿件数', 'eight-fields' ); ?></h2>
		<table class="widefat striped" style="max-width:820px">
			<thead>
				<tr>
					<th style="width:220px"><?php esc_html_e( '投稿タイプ', 'eight-fields' ); ?></th>
					<th style="width:100px"><?php esc_html_e( '件数', 'eight-fields' ); ?></th>
					<th><?php esc_html_e( '状態', 'eight-fields' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $types as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( $row['type'] ); ?></code></td>
						<td><?php echo (int) $row['count']; ?></td>
						<td>
							<?php if ( ! $row['registered'] ) : ?>
								<span style="color:#b32d2e"><?php esc_html_e( '未登録（管理画面に出ません）', 'eight-fields' ); ?></span>
							<?php elseif ( ! $row['visible'] ) : ?>
								<span style="color:#8a6d00"><?php esc_html_e( '登録済み・管理画面には非表示', 'eight-fields' ); ?></span>
							<?php else : ?>
								<?php echo esc_html( $row['label'] ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'サービス用のURLルール', 'eight-fields' ); ?></h2>
		<?php if ( ! get_option( 'permalink_structure' ) ) : ?>
			<div class="notice notice-error inline">
				<p><?php esc_html_e( 'パーマリンクが「基本」のままです。「設定 → パーマリンク」で「投稿名」を選んで保存してください。', 'eight-fields' ); ?></p>
			</div>
		<?php elseif ( ! $rules ) : ?>
			<div class="notice notice-error inline">
				<p><?php esc_html_e( 'サービス用のURLルールが1件もありません。「設定 → パーマリンク」を開いて「変更を保存」を押すと作り直されます。', 'eight-fields' ); ?></p>
			</div>
		<?php else : ?>
			<p><?php echo esc_html( sprintf( /* translators: %d: rule count */ __( '%d件のルールが登録されています。', 'eight-fields' ), count( $rules ) ) ); ?></p>
			<table class="widefat striped" style="max-width:820px">
				<tbody>
					<?php foreach ( array_slice( $rules, 0, 12, true ) as $pattern => $target ) : ?>
						<tr>
							<td><code><?php echo esc_html( $pattern ); ?></code></td>
							<td><code><?php echo esc_html( $target ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h2><?php esc_html_e( '環境', 'eight-fields' ); ?></h2>
		<table class="widefat striped" style="max-width:820px">
			<tbody>
				<tr><th style="width:220px"><?php esc_html_e( 'WordPress', 'eight-fields' ); ?></th><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
				<tr><th>PHP</th><td><?php echo esc_html( PHP_VERSION ); ?></td></tr>
				<tr><th><?php esc_html_e( 'テーマ', 'eight-fields' ); ?></th><td><?php echo esc_html( wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'パーマリンク設定', 'eight-fields' ); ?></th><td><code><?php echo esc_html( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : '基本（未設定）' ); ?></code></td></tr>
				<tr>
					<th><?php esc_html_e( '有効なプラグイン', 'eight-fields' ); ?></th>
					<td>
						<?php
						$active = (array) get_option( 'active_plugins', array() );
						if ( ! $active ) {
							esc_html_e( 'なし', 'eight-fields' );
						} else {
							echo '<code>' . esc_html( implode( '</code>, <code>', $active ) ) . '</code>';
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'この内容をコピーして共有する', 'eight-fields' ); ?></h2>
		<p><?php esc_html_e( '下の内容をそのままお送りいただければ、原因を特定できます。', 'eight-fields' ); ?></p>
		<textarea readonly rows="16" style="width:100%;max-width:820px;font-family:monospace;font-size:12px"><?php
			echo esc_textarea( ef_diagnostics_text() );
		?></textarea>
	</div>
	<?php
}

/**
 * The same report as plain text, for pasting into a message.
 *
 * @return string
 */
function ef_diagnostics_text() {
	$object = get_post_type_object( 'service' );
	$lines  = array();

	$lines[] = 'WordPress: ' . get_bloginfo( 'version' ) . ' / PHP: ' . PHP_VERSION;
	$lines[] = 'Theme: ' . wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' )
		. ' (assets ?ver=' . ( defined( 'EF_THEME_VERSION' ) ? EF_THEME_VERSION : '?' ) . ')';
	$lines[] = 'Permalink: ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : '(plain)' );
	$lines[] = '';

	$lines[] = '[service post type]';
	if ( ! $object ) {
		$lines[] = 'NOT REGISTERED';
	} else {
		$lines[] = 'registered by: ' . ( ef_service_registered_by_theme() ? 'theme' : 'plugin' );
		$lines[] = 'has_archive: ' . var_export( $object->has_archive, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$lines[] = 'rewrite slug: ' . ( isset( $object->rewrite['slug'] ) ? $object->rewrite['slug'] : '(none)' );
		$lines[] = 'supports: ' . implode( ',', array_keys( get_all_post_type_supports( 'service' ) ) );
		$lines[] = sprintf(
			'public=%s show_ui=%s show_in_menu=%s publicly_queryable=%s',
			$object->public ? 'y' : 'n',
			$object->show_ui ? 'y' : 'n',
			$object->show_in_menu ? 'y' : 'n',
			$object->publicly_queryable ? 'y' : 'n'
		);
	}
	$lines[] = '';

	$lines[] = '[services]';
	foreach ( ef_diag_services() as $svc ) {
		$lines[] = sprintf(
			'  %-22s template=%-22s builder=%-24s sections=%d catch=%s',
			$svc['title'],
			$svc['template'],
			$svc['builder'] ? implode( '+', $svc['builder'] ) : '-',
			$svc['sections'],
			$svc['catch'] ? 'set' : '-'
		);
	}
	$lines[] = '';

	$lines[] = '[posts in database]';
	foreach ( ef_diag_post_types() as $row ) {
		$lines[] = sprintf(
			'%-24s %4d  %s',
			$row['type'],
			$row['count'],
			$row['registered'] ? ( $row['visible'] ? 'ok' : 'registered, hidden from admin' ) : 'NOT REGISTERED'
		);
	}
	$lines[] = '';

	$rules   = ef_diag_service_rules();
	$lines[] = '[service rewrite rules] ' . count( $rules );
	foreach ( array_slice( $rules, 0, 10, true ) as $pattern => $target ) {
		$lines[] = '  ' . $pattern . '  =>  ' . $target;
	}
	$lines[] = '';

	$lines[] = '[active plugins]';
	foreach ( (array) get_option( 'active_plugins', array() ) as $plugin ) {
		$lines[] = '  ' . $plugin;
	}

	return implode( "\n", $lines );
}
