<?php
/*
 * Plugin Name: Breadcrumbs
 * Description: breadcrumbs for wp
 * Version: 1.2.0
 * Author: Dev KO
 * Text Domain: breadcrumbs
 * Network: true
 */
include plugin_dir_path( __FILE__ ).'options.php';
function my_wp_breadcrumbs()
{

    /* === Options === */
    $text['home'] = __('Home'); // "Home" link text
    $text['category'] = __('%s'); // text for the category page
    $text['search'] = __('Search results by query "%s"'); // text for the search results page
    $text['tag'] = __('Posts with tags "%s"'); // text for the tag page
    $text['author'] = __('Author\'s  %s articles'); // text for the author page
    $text['404'] = __('Error 404'); // text for a 404 page
    $text['page'] = __('Page %s'); // text 'Page N'
    $text['cpage'] = __('Comments page %s'); // text 'Comments page N'

    if(get_option('container_class')){
        $breadcrumbs_container_class = get_option('container_class');
    } else {
        $breadcrumbs_container_class = '';
    }

    if(get_option('breadcrumbs_list_class')){
        $breadcrumbs_list_class = get_option('breadcrumbs_list_class');
    } else {
        $breadcrumbs_list_class = '';
    }

    if(get_option('breadcrumbs_item_class')){
        $breadcrumbs_item_class = get_option('breadcrumbs_item_class');
    } else {
        $breadcrumbs_item_class = '';
    }

//	var_dump($breadcrumbs_item_class);

    $wrap_before = '<div class="breadcrumbs__container '.$breadcrumbs_container_class.'"><ul class="breadcrumbs__list '.$breadcrumbs_list_class.'" itemscope="" itemtype="http://schema.org/BreadcrumbList">'; // opening wrap tag
    $wrap_after = '</ul></div>'; // closing wrapper tag
    $sep = '<li class="breadcrumbs__separator">-</li>';

    $before = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item '.$breadcrumbs_item_class.'" >';
    if (is_post_type_archive()) {
        $post_type = get_post_type(get_the_ID());
        $before .= '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_post_type_archive_link($post_type) . '" rel="nofollow" itemprop="item"><span itemprop="name">';
    } else if (is_tax()) {
        $taxonomy = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
        $before .= '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_term_link($taxonomy->term_id, $taxonomy->taxonomy) . '" rel="nofollow" itemprop="item"><span itemprop="name">';
    } else {
        $before .= '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_the_permalink() . '" rel="nofollow" itemprop="item"><span itemprop="name">';
    }
    $after = '</span></a><meta itemprop="position" content="%1$s" /></li>'; // tag after the current "crumb"

    $show_on_home = 1; // 1 - show "breadcrumbs" on the main page, 0 - do not show
    $show_home_link = 1; // 1 - show "Home" link, 0 - do not show
    $show_current = get_option('show_current'); // 1 - show the name of the current page, 0 - do not show
    $show_last_sep = get_option('show_last_sep'); // 1 - show last separator when title of the current page is not shown, 0 - do not show
    /* === END OF OPTIONS === */

    global $post;
    $home_url = home_url('/');
    $link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item '.$breadcrumbs_item_class.'">';
    $link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
    $link .= '<meta itemprop="position" content="%3$s" />';
    $link .= '</li>';
    $parent_id = ($post) ? $post->post_parent : '';
    $home_link = sprintf($link, $home_url, $text['home'], 1);

    if (is_home() || is_front_page()) {

        if ($show_on_home) echo $wrap_before . $home_link . $wrap_after;

    } else {

        $position = 0;

        echo $wrap_before;

        if ($show_home_link) {
            $position += 1;
            echo $home_link;
        }

        if (is_category()) {
            $parents = get_ancestors(get_query_var('cat'), 'category');
            foreach (array_reverse($parents) as $cat) {
                $position += 1;
                if ($position > 1) echo $sep;
                echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
            }
            if (get_query_var('paged')) {
                $position += 1;
                $cat = get_query_var('cat');
                echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . sprintf($after, $position + 1);
            } else {
                if ($show_current) {
                    if ($position >= 1) echo $sep;
                    echo $before . sprintf($text['category'], single_cat_title('', false)) . sprintf($after, $position + 1);
                } elseif ($show_last_sep) echo $sep;
            }

        } elseif (is_search()) {
            if (get_query_var('paged')) {
                $position += 1;
                if ($show_home_link) echo $sep;
                echo sprintf($link, $home_url . '?s=' . get_search_query(), sprintf($text['search'], get_search_query()), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . sprintf($after, $position + 1);
            } else {
                if ($show_current) {
                    if ($position >= 1) echo $sep;
                    echo $before . sprintf($text['search'], get_search_query()) . sprintf($after, $position + 1);
                } elseif ($show_last_sep) echo $sep;
            }

        } elseif (is_year()) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . get_the_time('Y') . sprintf($after, $position + 1);
            elseif ($show_home_link && $show_last_sep) echo $sep;

        } elseif (is_month()) {
            if ($show_home_link) echo $sep;
            $position += 1;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position);
            if ($show_current) echo $sep . $before . get_the_time('F') . sprintf($after, $position + 1);
            elseif ($show_last_sep) echo $sep;

        } elseif (is_day()) {
            if ($show_home_link) echo $sep;
            $position += 1;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position) . $sep;
            $position += 1;
            echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'), $position);
            if ($show_current) echo $sep . $before . get_the_time('d') . sprintf($after, $position + 1);
            elseif ($show_last_sep) echo $sep;

        } elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post') {
                $position += 1;
                $post_type = get_post_type_object(get_post_type());
                if ($position > 1) echo $sep;
                echo sprintf($link, get_post_type_archive_link($post_type->name), $post_type->labels->name, $position);
                if ($show_current) echo $sep . $before . get_the_title() . sprintf($after, $position + 1);
                elseif ($show_last_sep) echo $sep;
            } else {
                $cat = get_the_category();
                $catID = $cat[0]->cat_ID;
                $parents = get_ancestors($catID, 'category');
                $parents = array_reverse($parents);
                $parents[] = $catID;
                foreach ($parents as $cat) {
                    $position += 1;
                    if ($position > 1) echo $sep;
                    echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
                }
                if (get_query_var('cpage')) {
                    $position += 1;
                    echo $sep . sprintf($link, get_permalink(), get_the_title(), $position);
                    echo $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . sprintf($after, $position + 1);
                } else {
                    if ($show_current) echo $sep . $before . get_the_title() . sprintf($after, $position + 1);
                    elseif ($show_last_sep) echo $sep;
                }
            }

        } elseif (is_post_type_archive()) {
            $post_type = get_post_type_object(get_post_type());
            if (get_query_var('paged')) {
                $position += 1;
                if ($position > 1) echo $sep;
                echo sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label, $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . sprintf($after, $position);
            } else {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . $post_type->label . sprintf($after, $position + 1);
                elseif ($show_home_link && $show_last_sep) echo $sep;
            }

        } elseif (is_attachment()) {
            $parent = get_post($parent_id);
            $cat = get_the_category($parent->ID);
            $catID = $cat[0]->cat_ID;
            $parents = get_ancestors($catID, 'category');
            $parents = array_reverse($parents);
            $parents[] = $catID;
            foreach ($parents as $cat) {
                $position += 1;
                if ($position > 1) echo $sep;
                echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
            }
            $position += 1;
            echo $sep . sprintf($link, get_permalink($parent), $parent->post_title, $position);
            if ($show_current) echo $sep . $before . get_the_title() . sprintf($after, $position + 1);
            elseif ($show_last_sep) echo $sep;

        } elseif (is_page() && !$parent_id) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . get_the_title() . sprintf($after, $position + 1);
            elseif ($show_home_link && $show_last_sep) echo $sep;

        } elseif (is_page() && $parent_id) {
            $parents = get_post_ancestors(get_the_ID());
            foreach (array_reverse($parents) as $pageID) {
                $position += 1;
                if ($position > 1) echo $sep;
                echo sprintf($link, get_page_link($pageID), get_the_title($pageID), $position);
            }
            if ($show_current) echo $sep . $before . get_the_title() . sprintf($after, $position + 1);
            elseif ($show_last_sep) echo $sep;

        } elseif (is_tag()) {
            if (get_query_var('paged')) {
                $position += 1;
                $tagID = get_query_var('tag_id');
                echo $sep . sprintf($link, get_tag_link($tagID), single_tag_title('', false), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . sprintf($after, $position + 1);
            } else {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['tag'], single_tag_title('', false)) . sprintf($after, $position + 1);
                elseif ($show_home_link && $show_last_sep) echo $sep;
            }

        } elseif (is_tax()) {
            $pt = get_post_type_object(get_post_type());
            $term = get_queried_object();
            $parent = $term->parent;
            $position += 1;
            if ($position > 1) echo $sep;
            echo sprintf($link, get_post_type_archive_link($pt->name), $pt->labels->name, $position);

            $position += 1;
            if ($position > 1) echo $sep;
            echo sprintf($link, get_category_link($term->term_id), $term->name, $position);

            if (get_query_var('cpage')) {
                $position += 1;
                echo $sep . sprintf($link, get_permalink(), get_the_title(), $position);
                echo $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . sprintf($after, $position + 1);
            } else {
                if ($show_current) echo $sep . $before . get_the_title() . sprintf($after, $position + 1);
                elseif ($show_last_sep) echo $sep;
            }
        } elseif (is_author()) {
            $author = get_userdata(get_query_var('author'));
            if (get_query_var('paged')) {
                $position += 1;
                echo $sep . sprintf($link, get_author_posts_url($author->ID), sprintf($text['author'], $author->display_name), $position);
                echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . sprintf($after, $position + 1);
            } else {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['author'], $author->display_name) . sprintf($after, $position + 1);
                elseif ($show_home_link && $show_last_sep) echo $sep;
            }

        } elseif (is_404()) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . $text['404'] . sprintf($after, $position + 1);
            elseif ($show_last_sep) echo $sep;

        } elseif (has_post_format() && !is_singular()) {
            if ($show_home_link && $show_current) echo $sep;
            echo get_post_format_string(get_post_format());
        }

        echo $wrap_after;

    }
}

function my_sc_breadcrumbs() {
    my_wp_breadcrumbs();
}
add_shortcode('breadcrumbs', 'my_sc_breadcrumbs');
