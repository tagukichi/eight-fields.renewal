<?php
/**
 * Editorial copy that belongs to the design rather than to a post.
 *
 * The sections below (reasons, values, flow, numbers, contact routes) are part
 * of the layout: they have a fixed number of slots and a fixed shape, so they
 * would be fragile as block-editor content. Keeping them here means one place
 * to edit the wording, and each set passes through a filter so a child theme or
 * a small plugin can override it without touching the templates.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Headline figures shown in the first view.
 *
 * Each row is array( label, value, unit ). A numeric `count` makes the value
 * animate up from zero on first paint.
 *
 * @return array[]
 */
function ef_hero_stats() {
	return (array) apply_filters(
		'ef_hero_stats',
		array(
			array(
				'label' => __( '施工実績', 'eight-fields' ),
				'value' => '10,000',
				'count' => 10000,
				'unit'  => __( '棟以上', 'eight-fields' ),
			),
			array(
				'label' => __( '対応エリア', 'eight-fields' ),
				'value' => '7',
				'count' => 7,
				'unit'  => __( '都県', 'eight-fields' ),
			),
			array(
				'label' => __( '取扱サービス', 'eight-fields' ),
				'value' => '6',
				'count' => 6,
				'unit'  => __( '分野', 'eight-fields' ),
			),
			array(
				'label' => __( '営業＝施工', 'eight-fields' ),
				'value' => __( '自社一貫', 'eight-fields' ),
				'unit'  => __( '体制', 'eight-fields' ),
			),
		)
	);
}

/**
 * The "数字で見る" figures.
 *
 * @return array[]
 */
function ef_numbers() {
	return (array) apply_filters(
		'ef_numbers',
		array(
			array(
				'label' => __( '施工実績', 'eight-fields' ),
				'value' => '10,000',
				'count' => 10000,
				'unit'  => __( '棟以上', 'eight-fields' ),
			),
			array(
				'label' => __( '対応エリア', 'eight-fields' ),
				'value' => '7',
				'count' => 7,
				'unit'  => __( '都県', 'eight-fields' ),
			),
			array(
				'label' => __( 'スタッフ数', 'eight-fields' ),
				'value' => '28',
				'count' => 28,
				'unit'  => __( '名', 'eight-fields' ),
			),
			array(
				'label' => __( '設立', 'eight-fields' ),
				'value' => '2023',
				'unit'  => __( '年', 'eight-fields' ),
			),
		)
	);
}

/**
 * The three reasons on the front page.
 *
 * @return array[] Rows of array( no, title, text ).
 */
function ef_reasons() {
	return (array) apply_filters(
		'ef_reasons',
		array(
			array(
				'no'    => 'REASON 01',
				'title' => __( '営業から施工まで、自社で一貫', 'eight-fields' ),
				'text'  => __( '下請けに流さないため中間マージンが発生せず、安心で安い。打ち合わせの内容がそのまま現場に伝わるので、「聞いていた話と違う」が起きません。', 'eight-fields' ),
			),
			array(
				'no'    => 'REASON 02',
				'title' => __( '6分野をまたいで最適解を出せる', 'eight-fields' ),
				'text'  => __( '太陽光・蓄電池・オール電化・塗装・EV/V2H・メンテナンス。特定商材を推すのではなく、ご家庭の使い方と予算から組み合わせを設計します。', 'eight-fields' ),
			),
			array(
				'no'    => 'REASON 03',
				'title' => __( 'つけたあとも、ずっと相談できる', 'eight-fields' ),
				'text'  => __( 'アフターサービスあり。設備の点検はもちろん、ご自宅で気になる箇所やメンテナンスもまとめてご相談いただけます。グループの金山製作所と連携して対応します。', 'eight-fields' ),
			),
		)
	);
}

/**
 * The three values on the greeting page.
 *
 * @return array[] Rows of array( no, title, text ).
 */
function ef_values() {
	return (array) apply_filters(
		'ef_values',
		array(
			array(
				'no'    => 'VALUE 01',
				'title' => __( '誠実と誇り', 'eight-fields' ),
				'text'  => __( 'できることとできないことを、はっきりお伝えします。売るための説明ではなく、判断していただくための説明を。', 'eight-fields' ),
			),
			array(
				'no'    => 'VALUE 02',
				'title' => __( '一人一人の出会いに感謝', 'eight-fields' ),
				'text'  => __( '工事が終わってからが、お付き合いの始まり。設備以外のお困りごとも、気軽に声をかけていただける関係を目指しています。', 'eight-fields' ),
			),
			array(
				'no'    => 'VALUE 03',
				'title' => __( '環境活動の推奨', 'eight-fields' ),
				'text'  => __( 'エネルギーを自分でつくり、ためて、使う暮らしへ。地域・日本の信頼に生きる活動として、無理のない一歩をご提案します。', 'eight-fields' ),
			),
		)
	);
}

/**
 * The steps from first contact to handover.
 *
 * The front page explains the journey in full; a service page repeats it in a
 * shorter form beside the product copy, so the two sets are worded differently.
 *
 * @param string $context 'home' or 'service'.
 * @return array[] Rows of array( title, text ).
 */
function ef_flow_steps( $context = 'home' ) {
	if ( 'service' === $context ) {
		$steps = array(
			array( __( 'お問い合わせ', 'eight-fields' ), __( 'フォームまたはお電話で。ご希望の連絡方法と時間帯をお知らせください。', 'eight-fields' ) ),
			array( __( '現地調査・ヒアリング', 'eight-fields' ), __( '設置環境とご家庭の使い方を確認します。ここまで無料です。', 'eight-fields' ) ),
			array( __( 'ご提案・お見積り', 'eight-fields' ), __( 'シミュレーションと利用できる補助金をあわせてご説明します。', 'eight-fields' ) ),
			array( __( 'ご契約・各種申請', 'eight-fields' ), __( '電力会社への申請や補助金申請は当社が代行します。', 'eight-fields' ) ),
			array( __( '施工', 'eight-fields' ), __( '自社の工事スタッフが対応。工程は事前にご案内します。', 'eight-fields' ) ),
			array( __( 'お引き渡し・アフター', 'eight-fields' ), __( '使い方をご説明し、以降の点検までサポートします。', 'eight-fields' ) ),
		);
	} else {
		$steps = array(
			array( __( 'お問い合わせ', 'eight-fields' ), __( 'フォームまたはお電話で。ご希望の連絡方法・時間帯をお知らせください。', 'eight-fields' ) ),
			array( __( 'ヒアリング・現地調査', 'eight-fields' ), __( '電気の使い方や屋根の状態を確認します。所要 60〜90分程度、費用はかかりません。', 'eight-fields' ) ),
			array( __( 'ご提案・お見積り', 'eight-fields' ), __( '発電量と光熱費のシミュレーション、利用できる補助金を含めてご説明します。', 'eight-fields' ) ),
			array( __( 'ご契約・各種申請', 'eight-fields' ), __( '電力会社への連系申請や補助金申請は当社が代行します。', 'eight-fields' ) ),
			array( __( '施工・お引き渡し', 'eight-fields' ), __( '自社の工事スタッフが施工。運転開始後の使い方までご説明します。', 'eight-fields' ) ),
			array( __( 'アフターサポート', 'eight-fields' ), __( '定期点検・不具合対応。設備以外のお困りごともご相談ください。', 'eight-fields' ) ),
		);
	}

	$rows = array();
	foreach ( $steps as $step ) {
		$rows[] = array(
			'title' => $step[0],
			'text'  => $step[1],
		);
	}

	return (array) apply_filters( 'ef_flow_steps', $rows, $context );
}

/**
 * The 企業理念 statement shown on 会社概要.
 *
 * @return string HTML with `<br>` allowed.
 */
function ef_philosophy() {
	$default = __( '私たちは誠実と誇りを全ての企業活動の原点とし、<br>一人一人の出会いに感謝し、環境活動を推奨とし、<br>地域・日本の信頼に生きる活動を行います。', 'eight-fields' );
	return (string) apply_filters( 'ef_philosophy', ef_info( 'philosophy', $default ) );
}

/**
 * Trim whitespace, including the full-width space Japanese copy uses.
 *
 * @param string $text Text.
 * @return string
 */
function ef_trim( $text ) {
	return (string) preg_replace( '/^[\s\x{3000}]+|[\s\x{3000}]+$/u', '', (string) $text );
}

/**
 * A run of items separated by a mark, rendered as a wrapped list of chips.
 *
 * 会社概要 lists the products handled, the areas covered and the trading
 * partners as one long run of 読点 or 中黒. Read as a sentence that is a wall
 * of text; as chips the same content can be scanned. A separator inside
 * parentheses belongs to its item rather than ending it, so the split follows
 * bracket depth.
 *
 * A trailing 「他」 qualifies the whole list rather than being a member of it,
 * so it is set after the chips instead of inside one.
 *
 * @param string $text       The run of items.
 * @param string $separators Characters that separate them.
 * @return string Markup, or '' when the text is a single item and nothing
 *                would be gained.
 */
function ef_tag_list( $text, $separators = '、' ) {
	$text = ef_trim( wp_strip_all_tags( (string) $text ) );
	if ( '' === $text ) {
		return '';
	}

	$marks = preg_split( '//u', $separators, -1, PREG_SPLIT_NO_EMPTY );
	$chars = preg_split( '//u', $text, -1, PREG_SPLIT_NO_EMPTY );

	$items = array();
	$item  = '';
	$depth = 0;

	foreach ( $chars as $char ) {
		if ( '（' === $char || '(' === $char ) {
			++$depth;
		} elseif ( '）' === $char || ')' === $char ) {
			$depth = max( 0, $depth - 1 );
		} elseif ( 0 === $depth && in_array( $char, $marks, true ) ) {
			$items[] = $item;
			$item    = '';
			continue;
		}
		$item .= $char;
	}
	$items[] = $item;

	$items = array_values( array_filter( array_map( 'ef_trim', $items ), 'strlen' ) );
	if ( count( $items ) < 2 ) {
		return '';
	}

	$more = '';
	$last = $items[ count( $items ) - 1 ];
	if ( preg_match( '/^(.*?)[\s\x{3000}]*(他|ほか|など)$/u', $last, $matches ) ) {
		$more = $matches[2];
		if ( '' !== ef_trim( $matches[1] ) ) {
			$items[ count( $items ) - 1 ] = ef_trim( $matches[1] );
		} else {
			array_pop( $items );
		}
	}

	$out = '<ul class="ef-tags">';
	foreach ( $items as $one ) {
		$out .= '<li>' . esc_html( $one ) . '</li>';
	}
	if ( '' !== $more ) {
		$out .= '<li class="ef-tags__more">' . esc_html( $more ) . '</li>';
	}
	$out .= '</ul>';

	return $out;
}

/**
 * A list rendered as chips, falling back to the plain run when it is one item.
 *
 * @param string $text       The run of items.
 * @param string $separators Characters that separate them.
 * @return string Markup.
 */
function ef_tags_or_text( $text, $separators ) {
	$tags = ef_tag_list( $text, $separators );
	return '' !== $tags ? $tags : esc_html( ef_trim( $text ) );
}

/**
 * The headline figures shown above the 会社概要 tables.
 *
 * A page of rows gives every fact the same weight. These four are the ones a
 * visitor is actually looking for, so they are set above the tables where they
 * can be read at a glance. The founding date follows the Customizer, so the
 * strip cannot fall out of step with the table under it.
 *
 * @return array[] Rows of array( label, value, unit ).
 */
function ef_company_highlights() {
	$founded = ef_info( 'founded', '2023年10月17日' );
	$year    = $founded;
	$month   = '';
	if ( preg_match( '/^(\d{4})年(\d{1,2})月/u', $founded, $matches ) ) {
		$year  = $matches[1];
		$month = '年' . $matches[2] . '月';
	}

	return (array) apply_filters(
		'ef_company_highlights',
		array(
			array( __( '設立', 'eight-fields' ), $year, $month ),
			array( __( '施工実績', 'eight-fields' ), '10,000', __( '棟以上', 'eight-fields' ) ),
			array( __( '従業員数', 'eight-fields' ), '28', __( '名', 'eight-fields' ) ),
			array( __( '対応エリア', 'eight-fields' ), '7', __( '都県', 'eight-fields' ) ),
		)
	);
}

/**
 * The 会社概要 tables, in groups.
 *
 * One table of thirteen rows reads as a list to be got through. Split into
 * 会社情報 / 事業内容 / 取引先 the same rows become three short tables a
 * visitor can find their way around.
 *
 * Rows whose value lives in the Customizer (商号・設立・代表者・事業所) are
 * built from it, so editing the company details in one place updates the table,
 * the footer and the access panel together.
 *
 * @return array[] Groups of array( title, rows ), each row array( label, value ).
 *                 Values may contain markup.
 */
function ef_company_profile_groups() {
	$office = sprintf(
		'〒%1$s　%2$s<br>TEL：%3$s／FAX：%4$s',
		esc_html( ef_info( 'zip', '131-0042' ) ),
		esc_html( ef_info( 'address', '東京都墨田区東墨田2-12-20' ) ),
		esc_html( ef_info( 'tel', '03-6670-5540' ) ),
		esc_html( ef_info( 'fax', '03-6323-8861' ) )
	);

	$group = sprintf(
		'%s（東京都知事登録 第290199号）',
		esc_html( ef_info( 'group', '有限会社 金山製作所' ) )
	)
	. '<span class="ef-table__note">'
	. esc_html__( '新エネルギー工事：太陽光発電システム販売工事／オール電化販売工事／蓄電池・HEMS販売工事／オフグリッド設計／LED工事', 'eight-fields' )
	. '<br>'
	. esc_html__( '電気・水道工事：一般電気工事（住宅設備）／一般水道工事（住宅・水廻り）／エアコン工事／給排水工事／給湯器販売交換工事／ドローンによる家屋・設備調査、点検', 'eight-fields' )
	. '</span>';

	return (array) apply_filters(
		'ef_company_profile_groups',
		array(
			array(
				'title' => __( '会社情報', 'eight-fields' ),
				'rows'  => array(
					array( __( '商号', 'eight-fields' ), esc_html( get_bloginfo( 'name' ) ) ),
					array( __( '設立年月日', 'eight-fields' ), esc_html( ef_info( 'founded', '2023年10月17日' ) ) ),
					array( __( '代表者', 'eight-fields' ), esc_html( sprintf( __( '代表取締役　%s', 'eight-fields' ), ef_info( 'ceo', '金山 準' ) ) ) ),
					array( __( '従業員数', 'eight-fields' ), esc_html__( '営業8名／工事20名（2026年1月現在）', 'eight-fields' ) ),
					array( __( '事業所', 'eight-fields' ), $office ),
				),
			),
			array(
				'title' => __( '事業内容', 'eight-fields' ),
				'rows'  => array(
					array(
						__( '取扱商品', 'eight-fields' ),
						ef_tags_or_text( __( '太陽光発電システム、蓄電システム、エコキュート、IHクッキングヒーター、システムバス、太陽熱温水器、水素吸入器、LEDライト、通信機器、リフォーム（外壁塗装、屋根修繕）、家庭用エアコン、ガスコンロ・ガス給湯器', 'eight-fields' ), '、' ),
					),
					array(
						__( '対応エリア', 'eight-fields' ),
						ef_tags_or_text( __( '東京都／千葉県／神奈川県／埼玉県／茨城県／栃木県／群馬県', 'eight-fields' ), '／/' ),
					),
					array(
						__( '保有資格', 'eight-fields' ),
						ef_tags_or_text( __( '第二種電気工事士／瓦屋根工事技士／給水設備工事主任技術者　他', 'eight-fields' ), '／/' ),
					),
					array( __( '実績数', 'eight-fields' ), esc_html__( '一万棟以上', 'eight-fields' ) ),
					array( __( 'アフターサービス', 'eight-fields' ), esc_html__( '有り', 'eight-fields' ) ),
					array(
						__( 'オリジナルサービス', 'eight-fields' ),
						esc_html__( '今回のご提案以外でも、ご自宅で気になる箇所、メンテナンス等すべて対応可能です。', 'eight-fields' )
							. '<br>'
							. esc_html__( 'その理由は、営業会社＝施工会社のため。営業から施工まで一括で行うために、安心で安いとご好評頂いております。', 'eight-fields' ),
					),
				),
			),
			array(
				'title' => __( '取引先・グループ会社', 'eight-fields' ),
				'rows'  => array(
					array(
						__( '取引先', 'eight-fields' ),
						ef_tags_or_text( __( 'みずほ銀行・東京信用金庫・長府産業・ニチコン・長州産業・Qセルズ・カナディアン・パナソニック・SHARP・エクソル・DMM・高島（株）・（株）ハジメ　他', 'eight-fields' ), '・' ),
					),
					array( __( 'グループ会社', 'eight-fields' ), $group ),
				),
			),
		)
	);
}

/**
 * A value rendered as chips when it is plainly a list, as text otherwise.
 *
 * Used for rows that come from the page body, where there is no telling in
 * advance which of them are enumerations.
 *
 * @param string $value Row value.
 * @return string Markup.
 */
function ef_maybe_tags( $value ) {
	$value = ef_trim( $value );

	// A sentence is prose even when it contains a 読点.
	if ( preg_match( '/[。\r\n]/u', $value ) ) {
		return esc_html( $value );
	}

	foreach ( array( '、', '／/', '・' ) as $separators ) {
		$found = 0;
		foreach ( preg_split( '//u', $separators, -1, PREG_SPLIT_NO_EMPTY ) as $mark ) {
			$found += substr_count( $value, $mark );
		}
		if ( $found < 2 ) {
			continue;
		}
		$tags = ef_tag_list( $value, $separators );
		if ( '' !== $tags ) {
			return $tags;
		}
	}

	return esc_html( $value );
}

/**
 * The 会社概要 rows a page's own body is holding, when it holds a list at all.
 *
 * A 会社概要 page carried over from an older site usually keeps its details as
 * lines of 「ラベル：値」 in the editor. Printed as they are they are the wall
 * of text this template exists to replace, and the theme's own table beside
 * them says the same thing twice. Read here instead, the editor's rows become
 * the designed table, so the client's copy stays the source and nothing is
 * duplicated.
 *
 * Returns nothing unless the body really is such a list — a page of prose is
 * left alone to be printed as prose.
 *
 * @param int|null $post_id Page ID. Defaults to the current post.
 * @return array[] Rows of array( label, value ), or an empty array.
 */
function ef_body_profile_rows( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$raw = (string) get_post_field( 'post_content', $post_id );
	if ( '' === ef_trim( wp_strip_all_tags( $raw ) ) && function_exists( 'ef_siteorigin_text' ) ) {
		$raw = ef_siteorigin_text( $post_id );
	}
	if ( '' === ef_trim( $raw ) ) {
		return array();
	}

	// Every block end and every <br> is a line break; the rest is text.
	$text = preg_replace( '#<(br|/p|/div|/li|/tr|/h[1-6]|/td)[^>]*>#i', "\n", $raw );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

	$rows  = array();
	$other = 0;

	foreach ( preg_split( '/\R/u', $text ) as $line ) {
		$line = ef_trim( $line );
		if ( '' === $line ) {
			continue;
		}

		if ( preg_match( '/^([^\s\/.：:]{2,24})[：:][\s\x{3000}]*(.+)$/u', $line, $matches ) ) {
			$value = ef_trim( $matches[2] );
			// A URL is not a labelled row.
			if ( '' !== $value && 0 !== strpos( $value, '//' ) ) {
				$rows[] = array( ef_trim( $matches[1] ), $value );
				continue;
			}
		}

		// A short heading above the list — 「会社概要」 — is not a stray line.
		if ( ! $rows && mb_strlen( $line ) <= 12 ) {
			continue;
		}
		++$other;
	}

	// Take the section over only when the body really is a table of rows.
	if ( count( $rows ) < 3 || $other * 3 > count( $rows ) ) {
		return array();
	}

	return (array) apply_filters( 'ef_body_profile_rows', $rows, $post_id );
}

/**
 * Sort loose 会社概要 rows into the same groups the theme's own table uses.
 *
 * A row whose label is not recognised keeps its place at the end rather than
 * being dropped, so nothing the editor wrote disappears.
 *
 * @param array[] $rows Rows of array( label, value ).
 * @return array[] Groups of array( title, rows ).
 */
function ef_group_profile_rows( $rows ) {
	$map = array(
		'会社情報'             => array( '商号', '社名', '会社名', '名称', '設立', '創業', '資本金', '代表', '役員', '従業員', '社員', 'スタッフ', '事業所', '所在地', '本社', '支店', '電話', 'TEL', 'FAX' ),
		'事業内容'             => array( '事業', '取扱', '商品', '商材', 'サービス', 'エリア', '資格', '許可', '登録', '実績', '施工', 'アフター' ),
		'取引先・グループ会社' => array( '取引', '銀行', 'グループ', '関連会社', '加盟' ),
	);

	$groups = array();
	foreach ( array_keys( $map ) as $title ) {
		$groups[ $title ] = array();
	}
	$groups['その他'] = array();

	foreach ( $rows as $row ) {
		$label = (string) $row[0];
		$found = 'その他';

		foreach ( $map as $title => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( false !== strpos( $label, $keyword ) ) {
					$found = $title;
					break 2;
				}
			}
		}

		$groups[ $found ][] = array( $label, ef_maybe_tags( $row[1] ) );
	}

	$out = array();
	foreach ( $groups as $title => $group_rows ) {
		if ( $group_rows ) {
			$out[] = array(
				'title' => $title,
				'rows'  => $group_rows,
			);
		}
	}

	// One group is the whole table; a heading over it would say nothing.
	if ( 1 === count( $out ) ) {
		$out[0]['title'] = '';
	}

	return $out;
}

/**
 * The groups the 会社概要 template should show.
 *
 * The page's own rows win when it has them, so editing the page edits the
 * table. With an empty page the theme's own set is used.
 *
 * @param int|null $post_id Page ID.
 * @return array[] Groups of array( title, rows ).
 */
function ef_profile_groups_for_page( $post_id = null ) {
	$rows = ef_body_profile_rows( $post_id );
	return $rows ? ef_group_profile_rows( $rows ) : ef_company_profile_groups();
}

/**
 * Every 会社概要 row as one flat list.
 *
 * Kept for anything that wants the table without its grouping.
 *
 * @return array[] Rows of array( label, value ). Values may contain markup.
 */
function ef_company_profile() {
	$rows = array();
	foreach ( ef_company_profile_groups() as $group ) {
		foreach ( $group['rows'] as $row ) {
			$rows[] = $row;
		}
	}
	return (array) apply_filters( 'ef_company_profile', $rows );
}

/**
 * The head-office rows beside the map.
 *
 * @return array[] Rows of array( label, value ). Values may contain markup.
 */
function ef_access_rows() {
	return (array) apply_filters(
		'ef_access_rows',
		array(
			array(
				__( '所在地', 'eight-fields' ),
				'〒' . esc_html( ef_info( 'zip', '131-0042' ) ) . '<br>' . esc_html( ef_info( 'address', '東京都墨田区東墨田2-12-20' ) ),
			),
			array(
				__( '電話', 'eight-fields' ),
				'<a href="tel:' . esc_attr( ef_tel_digits() ) . '">' . esc_html( ef_info( 'tel', '03-6670-5540' ) ) . '</a>',
			),
			array( __( 'FAX', 'eight-fields' ), esc_html( ef_info( 'fax', '03-6323-8861' ) ) ),
			array(
				__( '受付時間', 'eight-fields' ),
				esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ) . '<br>' . esc_html__( '（土日祝は要予約で対応可）', 'eight-fields' ),
			),
		)
	);
}

/**
 * The "組み合わせると、効果が変わります" steps on the service index.
 *
 * @return array[] Rows of array( no, title, text ).
 */
function ef_combination_steps() {
	return (array) apply_filters(
		'ef_combination_steps',
		array(
			array(
				'no'    => 'MAKE',
				'title' => __( '太陽光でつくる', 'eight-fields' ),
				'text'  => __( '昼間の電気を自給。余った分は電力会社が買い取ります。', 'eight-fields' ),
			),
			array(
				'no'    => 'STORE',
				'title' => __( '蓄電池でためる', 'eight-fields' ),
				'text'  => __( '使いきれなかった分を夜に回し、停電時の備えにも。', 'eight-fields' ),
			),
			array(
				'no'    => 'USE',
				'title' => __( 'オール電化で使う', 'eight-fields' ),
				'text'  => __( '給湯・調理も電気に集約。ガス基本料金の見直しにも。', 'eight-fields' ),
			),
			array(
				'no'    => 'KEEP',
				'title' => __( '塗装・点検で保つ', 'eight-fields' ),
				'text'  => __( '屋根と外壁を健全に保ち、設備を長く使える状態に。', 'eight-fields' ),
			),
		)
	);
}
