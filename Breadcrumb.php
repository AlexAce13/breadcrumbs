<?php
abstract class Breadcrumbs {
	protected $text;
	protected $breadcrumbs_container_class;
	protected $breadcrumbs_list_class;
	protected $breadcrumbs_item_class;
	protected $wrap_before;
	protected $wrap_after;
	protected $sep;

	protected $before;

	protected $after;
	protected $show_on_home;
	protected $show_home_link;
	protected $show_current;
	protected $show_last_sep;

	public function __construct() {
		$this->text = array(
			'home'     => __( 'Home' ),
			'category' => __( '%s' ),
			'search'   => __( 'Search results by query "%s"' ),
			'tag'      => __( 'Posts with tags "%s"' ),
			'author'   => __( 'Author\'s  %s articles' ),
			'404'      => __( 'Error 404' ),
			'page'     => __( 'Page %s' ),
			'cpage'    => __( 'Comments page %s' )
		);

		$this->breadcrumbs_container_class = get_option( 'container_class' ) ?: '';
		$this->breadcrumbs_list_class      = get_option( 'breadcrumbs_list_class' ) ?: '';
		$this->breadcrumbs_item_class      = get_option( 'breadcrumbs_item_class' ) ?: '';

		$this->wrap_before    = '<div class="breadcrumbs__container ' . $this->breadcrumbs_container_class . '"><ul class="breadcrumbs__list ' . $this->breadcrumbs_list_class . '" itemscope="" itemtype="http://schema.org/BreadcrumbList">';
		$this->wrap_after     = '</ul></div>';
		$this->sep            = '<li class="breadcrumbs__separator">-</li>';
		$this->before         = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '" >';
		$this->after          = '</span></a><meta itemprop="position" content="%1$s" /></li>';
		$this->show_on_home   = 1;
		$this->show_home_link = 1;
		$this->show_current   = get_option( 'show_current' );
		$this->show_last_sep  = get_option( 'show_last_sep' );
	}

	public function get_wrap_before() {
		return $this->wrap_before;
	}

	public function get_wrap_after() {
		return $this->wrap_after;
	}

	public function get_separator() {
		return $this->sep;
	}

	abstract protected function display( $position = 0 );
}

class HomeBreadcrumbs extends Breadcrumbs {

	public function display( $position = 0 ) {
		global $post;
		$home_url  = home_url( '/' );
		$link      = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
		$link      .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link      .= '<meta itemprop="position" content="%3$s" />';
		$link      .= '</li>';
		$parent_id = ( $post ) ? $post->post_parent : '';
		$home_link = sprintf( $link, $home_url, $this->text['home'], 1 );

		if ( $this->show_on_home ) {
			echo $this->wrap_before . $home_link . $this->wrap_after;
		}

		// Additional logic specific to category breadcrumbs
	}
}

class CategoryBreadcrumbs extends Breadcrumbs {

	public function display( $position = 0 ) {
		$link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
		$link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link .= '<meta itemprop="position" content="%3$s" />';
		$link .= '</li>';
//		parent::display();
		$parents = get_ancestors( get_query_var( 'cat' ), 'category' );

		foreach ( array_reverse( $parents ) as $cat ) {
			$position += 1;
			if ( $position > 1 ) {
				echo $this->sep;
			}
			echo sprintf( $link, get_category_link( $cat ), get_cat_name( $cat ), $position );
		}
		if ( get_query_var( 'paged' ) ) {
			$position += 1;
			$cat      = get_query_var( 'cat' );
			echo $this->sep . sprintf( $link, get_category_link( $cat ), get_cat_name( $cat ), $position );
			echo $this->sep . $this->before . sprintf( $this->text['page'], get_query_var( 'paged' ) ) . sprintf( $this->after, $position + 1 );
		} else {
			if ( $this->show_current ) {
				if ( $position >= 1 ) {
					echo $this->sep;
				}
				echo $this->before . sprintf( $this->text['category'], single_cat_title( '', false ) ) . sprintf( $this->after, $position + 1 );
			} elseif ( $this->show_last_sep ) {
				echo $this->sep;
			}
		}
		// Additional logic specific to category breadcrumbs
	}

}

class PageBreadcrumbs extends Breadcrumbs {
	public function display( $position = 0 ) {
		$before = $this->before . '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_the_permalink() . '" rel="nofollow" itemprop="item"><span itemprop="name">';
		if ( $this->show_home_link && $this->show_current ) {
			echo $this->sep;
		}
		if ( $this->show_current ) {
			echo $before . get_the_title() . sprintf( $this->after, $position + 1 );
		} elseif ( $this->show_home_link && $this->show_last_sep ) {
			echo $this->sep;
		}
	}
}

class PostBreadcrumbs extends Breadcrumbs {
	public function display( $position = 0 ) {

		$link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
		$link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link .= '<meta itemprop="position" content="%3$s" />';
		$link .= '</li>';

		$before = $this->before . '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_the_permalink() . '" rel="nofollow" itemprop="item"><span itemprop="name">';

		$cat       = get_the_category();
		$catID     = $cat[0]->cat_ID;
		$parents   = get_ancestors( $catID, 'category' );
		$parents   = array_reverse( $parents );
		$parents[] = $catID;
		foreach ( $parents as $cat ) {
			$position += 1;
			if ( $position > 1 ) {
				echo $this->sep;
			}
			echo sprintf( $link, get_category_link( $cat ), get_cat_name( $cat ), $position );
		}
		if ( get_query_var( 'cpage' ) ) {
			$position += 1;
			echo $this->sep . sprintf( $link, get_permalink(), get_the_title(), $position );
			echo $this->sep . $before . sprintf( $this->text['cpage'], get_query_var( 'cpage' ) ) . sprintf( $this->after, $position + 1 );
		} else {
			if ( $this->show_current ) {
				echo $this->sep . $before . get_the_title() . sprintf( $this->after, $position + 1 );
			} elseif ( $this->show_last_sep ) {
				echo $this->sep;
			}
		}
	}
}

class CustomPostTypeBreadcrumbs extends Breadcrumbs {
    public function display( $position = 0 ) {

        $link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
        $link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
        $link .= '<meta itemprop="position" content="%3$s" />';
        $link .= '</li>';

        $before = $this->before . '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_the_permalink() . '" rel="nofollow" itemprop="item"><span itemprop="name">';

        $position += 1;
        $post_type      = get_post_type_object( get_post_type() );
        if ( $position > 1 ) {
            echo $this->sep;
        }
        if( !empty(get_option( 'breadcrumbs_'.$post_type->name.'_custom_step_text' )) && !empty(get_option( 'breadcrumbs_'.$post_type->name.'_custom_step_link' )) ){
            echo sprintf( $link, get_option( 'breadcrumbs_'.$post_type->name.'_custom_step_link' ), get_option( 'breadcrumbs_'.$post_type->name.'_custom_step_text' ), $position );
        } else {
            echo sprintf( $link, get_post_type_archive_link( $post_type->name ), $post_type->labels->name, $position );
        }
        if ( $this->show_current ) {
            echo $this->sep . $before . get_the_title() . sprintf( $this->after, $position + 1 );
        } elseif ( $this->show_last_sep ) {
            echo $this->sep;
        }
    }
}

class ArchiveBreadcrumbs extends Breadcrumbs {
	public function display( $position = 0 ) {

		$link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
		$link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link .= '<meta itemprop="position" content="%3$s" />';
		$link .= '</li>';

		$post_type = get_post_type( get_the_ID() );
		$before    = $this->before . '<a class="breadcrumbs__link breadcrumbs__current" href="' . get_post_type_archive_link( $post_type ) . '" rel="nofollow" itemprop="item"><span itemprop="name">';

		if ( get_query_var( 'paged' ) ) {
			$this->position += 1;
			if ( $this->position > 1 ) {
				echo $this->sep;
			}
			echo sprintf( $link, get_post_type_archive_link( $post_type->name ), $post_type->label, $this->position );
			echo $this->sep . $before . sprintf( $this->text['page'], get_query_var( 'paged' ) ) . sprintf( $this->after, $this->position );
		} else {
			if ( $this->show_home_link && $this->show_current ) {
				echo $this->sep;
			}
			if ( $this->show_current ) {
				echo $before . $post_type->label . sprintf( $this->after, $this->position + 1 );
			} elseif ( $this->show_home_link && $this->show_last_sep ) {
				echo $this->sep;
			}
		}
	}

}

class TaxBreadcrumbs extends Breadcrumbs {
	public function display( $position = 0 ) {
		$link = '<li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem" class="breadcrumbs__item ' . $this->breadcrumbs_item_class . '">';
		$link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link .= '<meta itemprop="position" content="%3$s" />';
		$link .= '</li>';

		$pt   = get_post_type_object( get_post_type() );
		$term = get_queried_object();
//			var_dump($term);
		$parent         = $term->parent;
		$this->position += 1;
		if ( $position > 1 ) {
			echo $this->sep;
		}
		echo sprintf( $link, get_post_type_archive_link( $pt->name ), $pt->labels->name, $position );

		$this->position += 1;
		if ( $this->position > 1 ) {
			echo $this->sep;
		}
		echo sprintf( $link, get_category_link( $term->term_id ), $term->name, $this->position );

		if ( get_query_var( 'cpage' ) ) {
			$this->position += 1;
			echo $this->sep . sprintf( $link, get_permalink(), get_the_title(), $this->position );
			echo $this->sep . $this->before . sprintf( $this->text['cpage'], get_query_var( 'cpage' ) ) . sprintf( $this->after, $this->position + 1 );
		} else {
			if ( $this->show_current ) {
				echo $this->sep . $this->before . get_the_title() . sprintf( $this->after, $this->position + 1 );
			} elseif ( $this->show_last_sep ) {
				echo $this->sep;
			}
		}
	}
}