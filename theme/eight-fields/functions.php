<?php
/**
 * EIGHT FIELDS theme bootstrap.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EF_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/nav.php';
require_once get_template_directory() . '/inc/service-icons.php';
require_once get_template_directory() . '/inc/template-tags.php';

/**
 * Theme supports and menu locations.
 */
function ef_setup() {
	load_theme_textdomain( 'eight-fields', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 214,
			'width'       => 376,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'グローバルナビゲーション', 'eight-fields' ),
			'footer'  => __( 'フッターナビゲーション', 'eight-fields' ),
		)
	);

	// Service thumbnails are used at 16:10 in card grids.
	add_image_size( 'ef-card', 720, 450, true );
	add_image_size( 'ef-hero', 1920, 1080, true );
}
add_action( 'after_setup_theme', 'ef_setup' );

/**
 * Front-end assets.
 */
function ef_enqueue_assets() {
	wp_enqueue_style(
		'ef-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Outfit:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'ef-style',
		get_theme_file_uri( '/assets/css/style.css' ),
		array( 'ef-fonts' ),
		EF_THEME_VERSION
	);

	// GSAP is bundled with the theme rather than pulled from a CDN, so the site
	// keeps working if the CDN is unreachable and no third party sees visitors.
	wp_enqueue_script(
		'gsap',
		get_theme_file_uri( '/assets/js/vendor/gsap.min.js' ),
		array(),
		'3.15.0',
		true
	);

	wp_enqueue_script(
		'gsap-scrolltrigger',
		get_theme_file_uri( '/assets/js/vendor/ScrollTrigger.min.js' ),
		array( 'gsap' ),
		'3.15.0',
		true
	);

	wp_enqueue_script(
		'ef-script',
		get_theme_file_uri( '/assets/js/main.js' ),
		array( 'gsap', 'gsap-scrolltrigger' ),
		EF_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ef_enqueue_assets' );

/**
 * Favicons, unless a Site Icon has been set in the Customizer.
 *
 * The bundled files are square so browsers never squash the wide logo mark.
 */
function ef_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	?>
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( '/assets/img/favicon.ico' ) ); ?>" sizes="32x32">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( '/assets/img/favicon.png' ) ); ?>" type="image/png" sizes="512x512">
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_theme_file_uri( '/assets/img/apple-touch-icon.png' ) ); ?>">
	<meta name="theme-color" content="#0B2E42">
	<?php
}
add_action( 'wp_head', 'ef_favicons', 5 );

/**
 * Preconnect to the Google Fonts hosts.
 *
 * @param array  $urls           URLs to print.
 * @param string $relation_type  Relation type.
 * @return array
 */
function ef_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'ef_resource_hints', 10, 2 );

/**
 * Posts per page for the news archive.
 *
 * @param WP_Query $query Query.
 */
function ef_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'service' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
	if ( $query->is_home() || $query->is_category() ) {
		$query->set( 'posts_per_page', 10 );
	}
}
add_action( 'pre_get_posts', 'ef_pre_get_posts' );

/**
 * Give nav menu items the class names the stylesheet expects.
 *
 * @param array    $classes Existing classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Menu args.
 * @param int      $depth   Depth.
 * @return array
 */
function ef_nav_menu_css_class( $classes, $item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && 0 === $depth ) {
		$classes[] = 'ef-nav__item';
		if ( in_array( 'current-menu-item', $classes, true )
			|| in_array( 'current-menu-ancestor', $classes, true )
			|| in_array( 'current_page_parent', $classes, true ) ) {
			$classes[] = 'is-current';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'ef_nav_menu_css_class', 10, 4 );

/**
 * Link classes for the primary menu.
 *
 * @param array    $atts  Anchor attributes.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Depth.
 * @return array
 */
function ef_nav_menu_link_attributes( $atts, $item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
		$atts['class'] = ( 0 === $depth ) ? 'ef-nav__link' : 'ef-nav__sublink';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'ef_nav_menu_link_attributes', 10, 4 );

/**
 * Excerpt tail.
 *
 * @return string
 */
function ef_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'ef_excerpt_more' );

/**
 * Excerpt length in characters for Japanese text.
 *
 * @param string $text Excerpt.
 * @return string
 */
function ef_trim_excerpt( $text ) {
	$text = wp_strip_all_tags( $text );
	if ( mb_strlen( $text ) > 90 ) {
		$text = mb_substr( $text, 0, 90 ) . '…';
	}
	return $text;
}
add_filter( 'get_the_excerpt', 'ef_trim_excerpt', 20 );

/**
 * The default Google Maps embed for the head office, used when the Customizer
 * field is left empty. Keyless `output=embed` form — no API key to manage.
 */
define( 'EF_MAP_EMBED_DEFAULT', 'https://www.google.com/maps?q=%E3%80%92131-0042%20%E6%9D%B1%E4%BA%AC%E9%83%BD%E5%A2%A8%E7%94%B0%E5%8C%BA%E6%9D%B1%E5%A2%A8%E7%94%B02-12-20&hl=ja&z=17&output=embed' );

/**
 * Company details, editable from Appearance → Customize.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function ef_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'ef_company',
		array(
			'title'    => __( '会社情報', 'eight-fields' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'ef_tel'      => array( __( '電話番号', 'eight-fields' ), '03-6670-5540' ),
		'ef_fax'      => array( __( 'FAX番号', 'eight-fields' ), '03-6323-8861' ),
		'ef_zip'      => array( __( '郵便番号', 'eight-fields' ), '131-0042' ),
		'ef_address'  => array( __( '住所', 'eight-fields' ), '東京都墨田区東墨田2-12-20' ),
		'ef_hours'    => array( __( '受付時間', 'eight-fields' ), '平日 9:00 - 18:00' ),
		'ef_map'      => array( __( 'Googleマップ埋め込みURL', 'eight-fields' ), EF_MAP_EMBED_DEFAULT, 'esc_url_raw' ),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field[1],
				'sanitize_callback' => isset( $field[2] ) ? $field[2] : 'wp_kses_post',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'ef_company',
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'ef_customize_register' );
