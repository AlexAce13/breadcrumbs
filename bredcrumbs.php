<?php
/*
 * Plugin Name: Easy Breadcrumbs
 * Description: breadcrumbs for wp
 * Version: 1.1
 * Author: Dev KO
 * Author URI: https://www.linkedin.com/in/oleksandr-klishch-1b302a190/
 */
include plugin_dir_path( __FILE__ ).'options.php';

require_once( 'Breadcrumb.php' );
function my_wp_breadcrumbs() {
    $position = 0;

    $homeBreadcrumbs = new HomeBreadcrumbs();

    echo $homeBreadcrumbs->get_wrap_before();

    ++ $position;

    echo $homeBreadcrumbs->display( $position );

    if ( is_category() ) {

        $catBreadcrumbs = new CategoryBreadcrumbs();
        ++ $position;
        echo $catBreadcrumbs->display( $position );

    } elseif ( is_single() && ! is_attachment() ) {

        if ( get_post_type() != 'post' ) {
            ++$position;
            $cptBreadcrumbs = new CustomPostTypeBreadcrumbs();
            echo $cptBreadcrumbs->display($position);
        } else {
            ++$position;
            $postBreadcrumbs = new PostBreadcrumbs();
            echo $postBreadcrumbs->display($position);
        }

    } elseif ( is_post_type_archive() ) {

        ++$position;
        $archiveBreadcrumbs = new ArchiveBreadcrumbs();
        echo $archiveBreadcrumbs->display($position);

    } elseif ( is_page() ) {
        ++$position;
        $pageBreadcrumbs = new PageBreadcrumbs();
        echo $pageBreadcrumbs->display($position);
    } elseif ( is_tax() ) {
        ++$position;
        $taxBreadcrumbs = new TaxBreadcrumbs();
        echo $taxBreadcrumbs->display($position);

    }

    echo $homeBreadcrumbs->get_wrap_after();
}

function my_sc_breadcrumbs() {
    my_wp_breadcrumbs();
}
add_shortcode('breadcrumbs', 'my_sc_breadcrumbs');
