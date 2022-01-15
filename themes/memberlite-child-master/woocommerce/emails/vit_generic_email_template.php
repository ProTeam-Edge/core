<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

echo "<style>
  h1{
    font-size: 18px !important;
  }
  img.wsc_preview_image{
    max-width: 200px;
    max-height: 150px;
  }
</style>";

do_action( 'woocommerce_email_header', $email_heading, $email );

echo $email_body;

do_action( 'woocommerce_email_footer', $email );
