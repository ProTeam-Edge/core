<?php
/**
 * The template for displaying the header.
 *
 * Displays all of the <head> section and everything up to the "content" div.
 *
 * @package Memberlite
 */

$nameWithType = "Wiscle Collaboration Network";
$description = "Team Up with Anyone, Anywhere to Collaborate on Anything";
$imageUrl = "https://storage.googleapis.com/pte_media_store_1/08701530-audrey2.png";
$linkUrl = "https://wiscle.com";

$wscMeta = "";
//fb
$wscMeta .= '<meta name="og:title" content=' . $nameWithType . '><meta name="og:description" content=' . $description . '><meta name="og:image" content="' . $imageUrl . '"><meta name="og:url" content="' . $linkUrl . '">';
//twitter
$wscMeta .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:site" content="@wiscleco"><meta name="twitter:title" content=' . $nameWithType . '><meta name="twitter:description" content=' . $description . '><meta name="twitter:image" content="' . $imageUrl . '">';

if (get_the_ID() == 9063) {
	$wscMeta = "";
	$memberId = isset($_GET['member_id']) && $_GET['member_id'] ? $_GET['member_id'] : false;
	$slideId = isset($_GET['slide_id']) && $_GET['slide_id'] ? $_GET['slide_id'] : false;
	if ($memberId && $slideId) {
		$nftResults = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM alpn_nft_meta WHERE id = %d", $slideId)
		 );
		 if (isset($nftResults[0])) {
			 $meta = json_decode($nftResults[0]->meta, true);

			 $description = stripslashes(filter_var($meta['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			 $description = str_replace("\n", "&nbsp;", $description);
			 $description = str_replace("\r", "&nbsp;", $description);
			 $description = json_encode($description);

			 $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

			 $name = filter_var($meta['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			 $mediaType = getFileMetaFromMimeType($nftResults[0]->media_mime_type)['type'];
			 $nameWithType = json_encode("{$name} | {$mediaType}");
			 if ($nftResults[0]->thumb_share_file_key) {
				 $imageUrl = PTE_IMAGES_ROOT_URL . $nftResults[0]->thumb_share_file_key;
			 } //else uses default above
			 $wscMeta .= '<meta name="og:title" content=' . $nameWithType . '><meta name="og:description" content=' . $description . '><meta name="og:image" content="' . $imageUrl . '"><meta name="og:url" content="' . $actual_link . '">';
			 $wscMeta .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:site" content="@wiscleco"><meta name="twitter:title" content=' . $nameWithType . '><meta name="twitter:description" content=' . $description . '><meta name="twitter:image" content="' . $imageUrl . '">';
		}
	}
}

?><!DOCTYPE html>

<html <?php language_attributes(); ?>>
<head>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-THPM5GZ');</script>
	<!-- End Google Tag Manager -->
<!-- Start of proteamedge Zendesk Widget script -->
<!-- <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=333d7fed-1308-4471-bb4e-1e7e6a8700d3"> </script>-->
<!-- End of proteamedge Zendesk Widget script -->

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<!-- default(white), black, black-translucent -->
	<meta name="apple-mobile-web-app-status-bar-style" content="default"/>
	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<meta name="apple-touch-fullscreen" content="yes"/>
	<meta name="App-Config" content="fullscreen=yes,useHistoryState=no,transition=no">
	<meta name="format-detaction" content="telephone=no,email=no">
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<meta name="HandheldFriendly" content="true">
	<meta name="MobileOptimized" content="750">
	<meta name="screen-orientation" content="portrait">
	<meta name="x5-orientation" content="portrait">
	<meta name="full-screen" content="yes">
	<meta name="x5-fullscreen" content="true">
	<meta name="browsermode" content="application">
	<meta name="x5-page-mode" content="app">
	<meta name="msapplication-tap-highlight" content="no">
	<meta name="renderer" content="webkit">	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php echo $wscMeta ?>
	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>


<?php do_action( 'memberlite_before_page' ); ?>
<div id="page" class="hfeed site">

<?php
	// Hide header output for the Blank page template.
	if ( ! is_page_template( 'templates/blank.php' ) ) { ?>

	<?php get_template_part( 'components/header/mobile', 'menu' ); ?>

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'memberlite' ); ?></a>

	<?php do_action( 'memberlite_before_site_header' ); ?>

	<header id="masthead" class="site-header" role="banner">
		<div class="row">
			<?php
				$meta_login = get_theme_mod( 'meta_login', false );
			if ( ! is_page_template( 'templates/interstitial.php' ) && ( ! empty( $meta_login ) || has_nav_menu( 'meta' ) || is_active_sidebar( 'sidebar-3' ) ) ) {
				$show_header_right = true;
			}
			?>

			<div class="
			<?php
			if ( is_page_template( 'templates/interstitial.php' ) || empty( $show_header_right ) ) {
				echo 'large-12';
			} else {
				echo 'medium-' . esc_attr( memberlite_getColumnsRatio( 'header-left' ) ); }
?>
 columns site-branding">


 <?php //memberlite_the_custom_logo(); ?>

				<?php
					$image = wp_get_attachment_image_src( 6956 );
		    ?>
		 		 <a href="https://wiscle.com"><img src="<?php echo $image[0]; ?>" alt="Wiscile Network"></a>

			</div><!-- .column4 -->

			<?php
				//<span onclick="speechSynthesis.speak(new SpeechSynthesisUtterance('wiscle'));">Say it</span>
				if ( ! empty( $show_header_right ) ) { ?>
				<div class="medium-<?php echo esc_attr( memberlite_getColumnsRatio( 'header-right' ) ); ?> columns header-right
												<?php
												if ( $meta_login == false ) {
								?>
								 no-meta-menu<?php } ?>">
					<?php
					if ( ! empty( $meta_login ) ) {
						get_template_part( 'components/header/meta', 'member' );
					}

					$meta_defaults = array(
						'theme_location'  => 'meta',
						'container'       => 'nav',
						'container_id'    => 'meta-navigation',
						'container_class' => 'meta-navigation',
						'fallback_cb'     => false,
					);
					wp_nav_menu( $meta_defaults );

					if ( is_dynamic_sidebar( 'sidebar-3' ) ) {
						dynamic_sidebar( 'sidebar-3' );
					}
					?>
				</div><!-- .columns -->
			<?php } ?>

			<?php
				// show the mobile menu toggle button
				if ( is_active_sidebar( 'sidebar-5' ) || has_nav_menu( 'primary' ) ) { ?>
					<div class="mobile-navigation-bar">
					<button class="menu-toggle"><i class="fa fa-bars"></i></button>
					</div>
				<?php }
			?>
		</div><!-- .row -->
	</header><!-- #masthead -->

	<?php do_action( 'memberlite_before_site_navigation' ); ?>

	<?php echo '<div id="vit_call_to_action_outer"><img id="vit_connection_loading" class="vit_connection_loading" src="' . PTE_ROOT_URL . 'pdf/web/images/loading-icon.gif" alt="Loading"><div id="vit_call_to_action_inner"></div></div>'; ?>

	<?php if ( ! is_page_template( 'templates/interstitial.php' ) && has_nav_menu( 'primary' ) ) { ?>
		<?php
			$sticky_nav = get_theme_mod( 'sticky_nav' );
			if ( false ) { ?>
				<div class="site-navigation-sticky-wrapper">
			<?php }
		?>
		<nav id="site-navigation">
		<?php

			$primary_defaults = array(
				'theme_location'  => 'primary',
				'container'       => 'div',
				'container_class' => 'main-navigation row',
				'menu_class'      => 'menu large-12 columns',
				'fallback_cb'     => false,
			);

			$currentPage = $wp_query->post->post_title;

			if(!is_user_logged_in()) {
					$primary_defaults['menu'] = "PTE-Home";

			}
			wp_nav_menu( $primary_defaults );
		?>
		</nav><!-- #site-navigation -->
		<?php
			if ( false ) { ?>
			</div> <!-- .site-navigation-sticky-wrapper -->
			<script>
				jQuery(document).ready(function ($) {
					var s = $("#site-navigation");
					var pos = s.position();
					$(window).scroll(function() {
						var windowpos = $(window).scrollTop();
						if ( windowpos >= pos.top ) {
							s.addClass("site-navigation-sticky");
						} else {
							s.removeClass("site-navigation-sticky");
						}
					});
				});
			</script>
		<?php }
		}
	} // End if(). ?>

	<?php do_action( 'memberlite_before_content' ); ?>

	<div id="content" class="site-content">

	<?php get_template_part( 'components/header/masthead' );
	 ?>

	<?php if ( ! is_page_template( 'templates/fluid-width.php' )  && ! is_page_template( 'templates/blank.php' ) && ! is_404() ) { ?>
		<div class="row">
	<?php }




	?>
