<?php

// Hook for adding admin menus
add_action('admin_menu', 'mt_add_pages');
function mt_add_pages() {
// Add a new submenu under Options:
add_menu_page('Breadcrumbs Settings', 'Breadcrumbs Settings', 8, 'bc_options', 'my_breadcrumbs_settings_page');
//call register settings function
	add_action( 'admin_init', 'register_my_breadcrumbs_settings' );
}

function register_my_breadcrumbs_settings() {
	//register our settings
	register_setting( 'my-breadcrumbs-settings-group', 'show_current' );
	register_setting( 'my-breadcrumbs-settings-group', 'show_last_sep' );
	register_setting( 'my-breadcrumbs-settings-group', 'container_class' );
	register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_list_class' );
	register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_item_class' );
}

function my_breadcrumbs_settings_page() { ?>
    <div class="wrap">
        <h1><?php esc_attr('WP breadcrumbs') ?></h1>
        <form method="post" action="options.php">
			<?php settings_fields( 'my-breadcrumbs-settings-group' ); ?>
			<?php do_settings_sections( 'my-breadcrumbs-settings-group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_attr('Show current') ?></th>
                    <td><input type="checkbox" name="show_current" value="1" <?php checked( 1, get_option('show_current') ) ?> /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_attr('Show last separator') ?></th>
                    <td><input type="checkbox" name="show_last_sep" value="1" <?php checked( 1, get_option('show_last_sep') ) ?>/></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_attr('Container class') ?></th>
                    <td><input type="text" name="container_class" value="<?php echo esc_attr( get_option('container_class') ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_attr('Breadcrumbs list class') ?></th>
                    <td><input type="text" name="breadcrumbs_list_class" value="<?php echo esc_attr( get_option('breadcrumbs_list_class') ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_attr('Breadcrumbs item class') ?></th>
                    <td><input type="text" name="breadcrumbs_item_class" value="<?php echo esc_attr( get_option('breadcrumbs_item_class') ); ?>" /></td>
                </tr>
            </table>

			<?php submit_button(); ?>
        </form>
    </div>
<?php } ?>

