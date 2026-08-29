<?php
/**
 * Front page.
 *
 * @package eight-fields
 */

get_header();
?>

<section class="ef-hero">
	<div class="ef-hero__media">
		<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/hero.jpg' ) ); ?>" alt="" decoding="async" fetchpriority="high">
	</div>
	<div class="ef-hero__glow"></div>

	<div class="ef-slider" data-slider aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'メインビジュアル', 'eight-fields' ); ?>">
		<div class="ef-slider__track">

			<div class="ef-slider__slide is-active" data-slide>
				<picture>
					<source media="(max-width: 700px)"
						srcset="<?php echo esc_url( get_theme_file_uri( '/assets/img/slide-partners-sp.jpg' ) ); ?>">
					<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/slide-partners.jpg' ) ); ?>"
						alt="<?php esc_attr_e( 'エイトフィールズ株式会社 × 有限会社 金山製作所', 'eight-fields' ); ?>"
						fetchpriority="high" decoding="async">
				</picture>
			</div>

			<div class="ef-slider__slide ef-slide--message" data-slide>
				<div class="ef-container ef-container--wide ef-slide__inner">
					<p class="ef-slide__copy">
						<span class="ef-slide__line"><?php esc_html_e( 'つくる・ためる・かしこく使う', 'eight-fields' ); ?></span>
						<span class="ef-slide__line"><?php esc_html_e( 'これからの暮らしへ。', 'eight-fields' ); ?></span>
					</p>
				</div>
				<img class="ef-slide__figure"
					src="<?php echo esc_url( get_theme_file_uri( '/assets/img/slide-family.webp' ) ); ?>"
					alt="" loading="lazy" decoding="async">
			</div>

		</div>

		<div class="ef-slider__dots" role="tablist"
			aria-label="<?php esc_attr_e( 'メインビジュアルの切り替え', 'eight-fields' ); ?>">
			<button type="button" role="tab" aria-selected="true" data-slide-to="0">
				<span class="ef-sr"><?php esc_html_e( '1枚目を表示', 'eight-fields' ); ?></span>
			</button>
			<button type="button" role="tab" aria-selected="false" data-slide-to="1">
				<span class="ef-sr"><?php esc_html_e( '2枚目を表示', 'eight-fields' ); ?></span>
			</button>
		</div>
	</div>

	<div class="ef-container ef-container--wide ef-hero__inner">
		<div class="ef-hero__copy">
			<span class="ef-hero__tag"><?php esc_html_e( '東京・関東一円／施工実績 一万棟以上', 'eight-fields' ); ?></span>
			<h1 class="ef-hero__title">
				<?php esc_html_e( '暮らしのエネルギーを、', 'eight-fields' ); ?><br>
				<span class="ef-mark"><?php esc_html_e( 'まるごと', 'eight-fields' ); ?></span><?php esc_html_e( 'ひとつの窓口へ。', 'eight-fields' ); ?>
			</h1>
			<p class="ef-hero__text">
				<?php esc_html_e( '光熱費の削減、家の修繕、電気自動車のご相談まで。エイトフィールズは「出来ないことは無い」くらい幅広く、皆様に喜んでもらえる商材を扱っております。', 'eight-fields' ); ?>
			</p>
		</div>

		<dl class="ef-hero__meta">
			<?php foreach ( ef_hero_stats() as $ef_stat ) : ?>
				<div class="ef-hero__stat">
					<dt><?php echo esc_html( $ef_stat['label'] ); ?></dt>
					<?php // Keep the value and unit adjacent — a newline between them renders as a wide space at this size. ?>
					<dd><?php if ( isset( $ef_stat['count'] ) ) : ?><span class="ef-num" data-count="<?php echo esc_attr( $ef_stat['count'] ); ?>"><?php echo esc_html( $ef_stat['value'] ); ?></span><?php else : ?><?php echo esc_html( $ef_stat['value'] ); ?><?php endif; ?><span><?php echo esc_html( $ef_stat['unit'] ); ?></span></dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<div class="ef-hero__actions">
			<div class="ef-hero__cta">
				<a class="ef-btn ef-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php ef_icon( 'mail' ); ?><span><?php esc_html_e( '無料相談・お見積り', 'eight-fields' ); ?></span>
				</a>
				<a class="ef-btn ef-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">
					<?php esc_html_e( 'サービスを見る', 'eight-fields' ); ?>
				</a>
			</div>
			<p class="ef-hero__note"><?php esc_html_e( 'ご相談・現地調査・お見積りは無料です。', 'eight-fields' ); ?></p>
		</div>
	</div>
</section>

<!-- ABOUT -->
<section class="ef-section">
	<div class="ef-container">
		<div class="ef-split">
			<div class="ef-split__media ef-split__media--stack" data-reveal>
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/lifestyle.jpg' ) ); ?>" alt="<?php esc_attr_e( '家族で暮らす住まい', 'eight-fields' ); ?>" loading="lazy" decoding="async">
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/ceo.jpg' ) ); ?>" alt="<?php esc_attr_e( 'エイトフィールズ株式会社 代表取締役社長', 'eight-fields' ); ?>" loading="lazy" decoding="async">
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/battery.jpg' ) ); ?>" alt="<?php esc_attr_e( '住宅用蓄電池', 'eight-fields' ); ?>" loading="lazy" decoding="async">
			</div>
			<div data-reveal data-reveal-delay="1">
				<span class="ef-eyebrow">About us</span>
				<h2 class="ef-h2"><?php esc_html_e( '見積りだけで終わらせない。', 'eight-fields' ); ?><br><?php esc_html_e( '建てたあとの家を、ずっと。', 'eight-fields' ); ?></h2>
				<p class="ef-lead">
					<?php
					printf(
						/* translators: %s: emphasised phrase */
						esc_html__( '太陽光や蓄電池を「売る」だけの会社は数多くあります。私たちが違うのは、%s であること。ご提案した本人が現場を知っているから、机上のシミュレーションではなく、その家で本当に成立するプランをお出しできます。', 'eight-fields' ),
						'<strong>' . esc_html__( '営業会社＝施工会社', 'eight-fields' ) . '</strong>'
					);
					?>
				</p>
				<p class="ef-lead">
					<?php esc_html_e( 'そして今回のご提案以外でも、ご自宅で気になる箇所やメンテナンスはすべて対応可能です。電気・水道・塗装・空調まで、窓口をひとつにまとめられる安心をお届けします。', 'eight-fields' ); ?>
				</p>
				<div class="ef-actions ef-mt-32">
					<a class="ef-btn ef-btn--dark" href="<?php echo esc_url( home_url( '/company/' ) ); ?>"><?php esc_html_e( '会社概要を見る', 'eight-fields' ); ?></a>
					<a class="ef-btn ef-btn--outline" href="<?php echo esc_url( home_url( '/greeting/' ) ); ?>"><?php esc_html_e( 'ごあいさつ', 'eight-fields' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- REASONS -->
<section class="ef-section ef-section--sand">
	<div class="ef-container">
		<div class="ef-head ef-head--center" data-reveal>
			<span class="ef-eyebrow">Why EIGHT FIELDS</span>
			<h2 class="ef-h2"><?php esc_html_e( 'エイトフィールズが', 'eight-fields' ); ?><br class="ef-br-sp"><?php esc_html_e( '選ばれる3つの理由', 'eight-fields' ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/cards-feature', null, array( 'items' => ef_reasons() ) ); ?>
	</div>
</section>

<!-- SERVICE -->
<section class="ef-section">
	<div class="ef-container">
		<div class="ef-head" data-reveal>
			<span class="ef-eyebrow">Service</span>
			<h2 class="ef-h2"><?php esc_html_e( 'サービス', 'eight-fields' ); ?></h2>
			<p class="ef-lead">
				<?php esc_html_e( '光熱費の削減・家の修繕・電気自動車のご相談等、エイトフィールズは出来ないことは無いくらい幅広く皆様に喜んでもらえる商材を扱っております。', 'eight-fields' ); ?>
			</p>
		</div>

		<?php
		$ef_services = new WP_Query(
			array(
				'post_type'      => 'service',
				'posts_per_page' => 6,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);
		if ( $ef_services->have_posts() ) :
			?>
			<div class="ef-grid ef-grid--3">
				<?php
				while ( $ef_services->have_posts() ) :
					$ef_services->the_post();
					get_template_part( 'template-parts/card-service', null, array( 'no' => ef_service_position() ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<div class="ef-actions ef-actions--center ef-mt-64" data-reveal>
				<a class="ef-btn ef-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">
					<?php esc_html_e( 'サービス一覧をまとめて見る', 'eight-fields' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- FLOW -->
<section class="ef-section ef-section--sand">
	<div class="ef-container">
		<div class="ef-split ef-split--top">
			<div data-reveal>
				<span class="ef-eyebrow">Flow</span>
				<h2 class="ef-h2"><?php esc_html_e( 'ご相談から施工までの流れ', 'eight-fields' ); ?></h2>
				<p class="ef-lead">
					<?php esc_html_e( 'はじめてのお客様でも迷わないよう、各ステップでやること・かかる期間を明示しています。相談・現地調査・お見積りまでは無料です。', 'eight-fields' ); ?>
				</p>
				<div class="ef-callout ef-mt-32">
					<h3 class="ef-callout__title"><span class="ef-callout__icon"><?php ef_icon( 'chat' ); ?></span><?php esc_html_e( 'まずはご相談だけでも', 'eight-fields' ); ?></h3>
					<p class="ef-callout__text">
						<?php esc_html_e( '「電気代が上がって気になっている」「訪問販売で見積りをもらったが妥当か知りたい」——そんな段階でのご相談も歓迎です。セカンドオピニオンとしてお使いください。', 'eight-fields' ); ?>
					</p>
				</div>
			</div>
			<div data-reveal data-reveal-delay="1">
				<?php get_template_part( 'template-parts/list-flow', null, array( 'context' => 'home' ) ); ?>
			</div>
		</div>
	</div>
</section>

<!-- NUMBERS -->
<section class="ef-section ef-section--tight ef-section--deep ef-numbers">
	<div class="ef-container">
		<div class="ef-head ef-head--center" data-reveal>
			<span class="ef-eyebrow">Numbers</span>
			<h2 class="ef-h2"><?php esc_html_e( '数字で見るエイトフィールズ', 'eight-fields' ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/list-stats', null, array( 'items' => ef_numbers() ) ); ?>
	</div>
</section>

<!-- NEWS -->
<section class="ef-section">
	<div class="ef-container">
		<div class="ef-head" style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:20px;max-width:none;" data-reveal>
			<div>
				<span class="ef-eyebrow">News</span>
				<h2 class="ef-h2"><?php esc_html_e( 'お知らせ', 'eight-fields' ); ?></h2>
			</div>
			<a class="ef-link" href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'お知らせ一覧', 'eight-fields' ); ?></a>
		</div>

		<?php
		$ef_news = new WP_Query( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => true ) );
		if ( $ef_news->have_posts() ) :
			?>
			<ul class="ef-news" data-reveal data-reveal-delay="1">
				<?php
				while ( $ef_news->have_posts() ) :
					$ef_news->the_post();
					get_template_part( 'template-parts/item-news' );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
