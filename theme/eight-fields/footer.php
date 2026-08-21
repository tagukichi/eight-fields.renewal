<?php
/**
 * Site footer, including the shared contact band.
 *
 * @package eight-fields
 */

?>
<section class="ef-section ef-cta">
	<div class="ef-container">
		<div class="ef-cta__inner">
			<div data-reveal>
				<span class="ef-eyebrow">Contact</span>
				<h2 class="ef-cta__title"><?php esc_html_e( '「うちの場合はどうなる？」', 'eight-fields' ); ?><br><?php esc_html_e( 'その一言から、お聞かせください。', 'eight-fields' ); ?></h2>
				<p class="ef-cta__text">
					<?php esc_html_e( 'お見積り・シミュレーションは無料です。営業から施工まで自社で一貫して行うため、お住まいの条件に合わせた現実的なプランと費用を、その場でご提示できます。太陽光・蓄電池以外の、ご自宅で気になる箇所のご相談も歓迎です。', 'eight-fields' ); ?>
				</p>
			</div>
			<div class="ef-cta__panel" data-reveal data-reveal-delay="1">
				<a class="ef-cta__tel" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">
					<small><?php esc_html_e( 'お電話でのご相談', 'eight-fields' ); ?></small>
					<b><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></b>
					<span class="ef-cta__hours"><?php echo esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ); ?></span>
				</a>
				<a class="ef-btn ef-btn--primary ef-btn--block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php ef_icon( 'mail' ); ?><span><?php esc_html_e( 'フォームで問い合わせる', 'eight-fields' ); ?></span>
				</a>
				<a class="ef-btn ef-btn--ghost ef-btn--block" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">
					<?php esc_html_e( 'サービス一覧を見る', 'eight-fields' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

</main>

<footer class="ef-footer" data-footer>
	<div class="ef-footer__main">
		<div class="ef-container">
			<div class="ef-footer__grid">
				<div>
					<a class="ef-logo ef-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="ef-logo__mark" src="<?php echo esc_url( ef_logo_url() ); ?>"
							alt="" width="376" height="214" loading="lazy" decoding="async">
						<span class="ef-logo__text">
							<span class="ef-logo__type">EIGHT FIELDS</span>
							<span class="ef-logo__sub"><?php bloginfo( 'name' ); ?></span>
						</span>
					</a>
					<address class="ef-footer__addr">
						<b><?php bloginfo( 'name' ); ?></b>
						〒<?php echo esc_html( ef_info( 'zip', '131-0042' ) ); ?><br>
						<?php echo esc_html( ef_info( 'address', '東京都墨田区東墨田2-12-20' ) ); ?><br>
						FAX：<?php echo esc_html( ef_info( 'fax', '03-6323-8861' ) ); ?>
					</address>
					<a class="ef-footer__tel" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">
						<b><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></b>
						<small><?php echo esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ); ?></small>
					</a>
				</div>

				<nav class="ef-footer__nav" aria-label="<?php esc_attr_e( 'フッターナビゲーション', 'eight-fields' ); ?>">
					<div class="ef-footer__col">
						<h3>Service</h3>
						<ul class="ef-footer__list">
							<?php
							$ef_services = get_posts(
								array(
									'post_type'      => 'service',
									'posts_per_page' => -1,
									'orderby'        => 'menu_order',
									'order'          => 'ASC',
								)
							);
							foreach ( $ef_services as $ef_service ) :
								?>
								<li>
									<a href="<?php echo esc_url( get_permalink( $ef_service ) ); ?>">
										<?php echo esc_html( get_the_title( $ef_service ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="ef-footer__col">
						<h3>Company</h3>
						<?php
						if ( has_nav_menu( 'footer' ) ) {
							wp_nav_menu(
								array(
									'theme_location' => 'footer',
									'container'      => false,
									'menu_class'     => 'ef-footer__list',
									'depth'          => 1,
								)
							);
						} else {
							?>
							<ul class="ef-footer__list">
								<li><a href="<?php echo esc_url( home_url( '/company/' ) ); ?>">会社概要</a></li>
								<li><a href="<?php echo esc_url( home_url( '/greeting/' ) ); ?>">ごあいさつ</a></li>
								<li><a href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">サービス一覧</a></li>
								<li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">お知らせ</a></li>
							</ul>
							<?php
						}
						?>
					</div>
					<div class="ef-footer__col">
						<h3>Contact</h3>
						<ul class="ef-footer__list">
							<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
							<li><a href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">お電話でのご相談</a></li>
						</ul>
					</div>
				</nav>
			</div>
		</div>
	</div>

	<div class="ef-footer__bottom">
		<div class="ef-container" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;">
			<div class="ef-footer__legal">
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a>
				<a href="<?php echo esc_url( home_url( '/company/' ) ); ?>">会社概要</a>
			</div>
			<small class="ef-footer__copy">&copy; <?php bloginfo( 'name' ); ?> All rights reserved.</small>
		</div>
	</div>
</footer>

<div class="ef-fixedbar" data-fixedbar>
	<a href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>"><?php ef_icon( 'phone' ); ?><span>電話する</span></a>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php ef_icon( 'mail' ); ?><span>無料見積り</span></a>
</div>
<a class="ef-totop" href="#top" data-totop aria-label="<?php esc_attr_e( 'ページの先頭へ戻る', 'eight-fields' ); ?>"><?php ef_icon( 'up' ); ?></a>

<?php wp_footer(); ?>
</body>
</html>
