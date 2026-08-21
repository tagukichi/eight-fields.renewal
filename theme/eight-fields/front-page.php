<?php
/**
 * Front page.
 *
 * @package eight-fields
 */

get_header();
?>

<section class="ef-hero">
	<div class="ef-hero__glow"></div>
	<div class="ef-container ef-container--wide ef-hero__inner">
		<div class="ef-hero__grid">
			<div class="ef-hero__copy">
				<span class="ef-hero__tag"><?php esc_html_e( '東京・関東一円／施工実績 一万棟以上', 'eight-fields' ); ?></span>
				<h1 class="ef-hero__title">
					<?php esc_html_e( '暮らしのエネルギーを、', 'eight-fields' ); ?><br>
					<span class="ef-mark"><?php esc_html_e( 'まるごと', 'eight-fields' ); ?></span><?php esc_html_e( 'ひとつの窓口へ。', 'eight-fields' ); ?>
				</h1>
				<p class="ef-hero__text">
					<?php esc_html_e( '光熱費の削減、家の修繕、電気自動車のご相談まで。', 'eight-fields' ); ?><br>
					<?php esc_html_e( 'エイトフィールズは「出来ないことは無い」くらい幅広く、皆様に喜んでもらえる商材を扱っております。', 'eight-fields' ); ?>
				</p>
				<div class="ef-hero__cta">
					<a class="ef-btn ef-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
						<?php ef_icon( 'mail' ); ?><span><?php esc_html_e( '無料相談・お見積り', 'eight-fields' ); ?></span>
					</a>
					<a class="ef-btn ef-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">
						<?php esc_html_e( 'サービスを見る', 'eight-fields' ); ?>
					</a>
				</div>
			</div>

			<div class="ef-hero__visual" aria-hidden="true">
				<?php
				$ef_hero_shots = array( 'solar.jpg', 'battery.jpg', 'ev_charge.jpg' );
				$ef_letters    = array( 'a', 'b', 'c' );
				foreach ( $ef_hero_shots as $ef_k => $ef_shot ) :
					?>
					<figure class="ef-hero__shot ef-hero__shot--<?php echo esc_attr( $ef_letters[ $ef_k ] ); ?>">
						<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/' . $ef_shot ) ); ?>" alt="" decoding="async">
					</figure>
				<?php endforeach; ?>
			</div>
		</div>

		<dl class="ef-hero__meta">
			<div class="ef-hero__stat">
				<dt><?php esc_html_e( '施工実績', 'eight-fields' ); ?></dt>
				<dd><span class="ef-num" data-count="10000">10000</span><span><?php esc_html_e( '棟以上', 'eight-fields' ); ?></span></dd>
			</div>
			<div class="ef-hero__stat">
				<dt><?php esc_html_e( '対応エリア', 'eight-fields' ); ?></dt>
				<dd><span class="ef-num" data-count="7">7</span><span><?php esc_html_e( '都県', 'eight-fields' ); ?></span></dd>
			</div>
			<div class="ef-hero__stat">
				<dt><?php esc_html_e( '取扱サービス', 'eight-fields' ); ?></dt>
				<dd><span class="ef-num" data-count="6">6</span><span><?php esc_html_e( '分野', 'eight-fields' ); ?></span></dd>
			</div>
			<div class="ef-hero__stat">
				<dt><?php esc_html_e( '営業＝施工', 'eight-fields' ); ?></dt>
				<dd><?php esc_html_e( '自社一貫', 'eight-fields' ); ?><span><?php esc_html_e( '体制', 'eight-fields' ); ?></span></dd>
			</div>
		</dl>
	</div>
	<span class="ef-hero__scroll">SCROLL</span>
</section>

<!-- ABOUT -->
<section class="ef-section">
	<div class="ef-container">
		<div class="ef-split">
			<div class="ef-split__media ef-split__media--stack" data-reveal>
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/solar.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/ceo.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
				<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/battery.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
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

<!-- SERVICE -->
<section class="ef-section ef-section--sand">
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
				$ef_i = 0;
				while ( $ef_services->have_posts() ) :
					$ef_services->the_post();
					++$ef_i;
					get_template_part( 'template-parts/card-service', null, array( 'no' => $ef_i ) );
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

<!-- NUMBERS -->
<section class="ef-section ef-section--tight ef-section--deep">
	<div class="ef-container">
		<div class="ef-head ef-head--center" data-reveal>
			<span class="ef-eyebrow">Numbers</span>
			<h2 class="ef-h2"><?php esc_html_e( '数字で見るエイトフィールズ', 'eight-fields' ); ?></h2>
		</div>
		<div class="ef-stats" data-reveal data-reveal-delay="1">
			<div class="ef-stats__item">
				<p class="ef-stats__label"><?php esc_html_e( '施工実績', 'eight-fields' ); ?></p>
				<p class="ef-stats__value"><span data-count="10000">10000</span><span><?php esc_html_e( '棟以上', 'eight-fields' ); ?></span></p>
			</div>
			<div class="ef-stats__item">
				<p class="ef-stats__label"><?php esc_html_e( '対応エリア', 'eight-fields' ); ?></p>
				<p class="ef-stats__value"><span data-count="7">7</span><span><?php esc_html_e( '都県', 'eight-fields' ); ?></span></p>
			</div>
			<div class="ef-stats__item">
				<p class="ef-stats__label"><?php esc_html_e( 'スタッフ数', 'eight-fields' ); ?></p>
				<p class="ef-stats__value"><span data-count="28">28</span><span><?php esc_html_e( '名', 'eight-fields' ); ?></span></p>
			</div>
			<div class="ef-stats__item">
				<p class="ef-stats__label"><?php esc_html_e( '設立', 'eight-fields' ); ?></p>
				<p class="ef-stats__value"><span class="ef-num">2023</span><span><?php esc_html_e( '年', 'eight-fields' ); ?></span></p>
			</div>
		</div>
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
