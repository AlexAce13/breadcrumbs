<?php
// Hook for adding admin menus
add_action('admin_menu', 'mt_add_pages');
function mt_add_pages() {
// Add a new submenu under Options:
    add_menu_page('Breadcrumbs Settings', 'Breadcrumbs Settings', 'administrator', 'bc_options', 'my_breadcrumbs_settings_page');
//call register settings function
    add_action( 'admin_init', 'register_my_breadcrumbs_settings' );
}

function register_my_breadcrumbs_settings() {
    $post_types = get_post_types( ['public'   => true, '_builtin' => false,], 'names', 'and' );
    //register our settings
    register_setting( 'my-breadcrumbs-settings-group', 'show_current' );
    register_setting( 'my-breadcrumbs-settings-group', 'show_last_sep' );
    register_setting( 'my-breadcrumbs-settings-group', 'container_class' );
    register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_list_class' );
    register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_item_class' );
    if(!empty($post_types)){
        foreach ($post_types as $post_type){
            register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_'.$post_type.'_custom_step_text' );
            register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_'.$post_type.'_custom_step_link' );
        }
    }

}

function my_breadcrumbs_settings_page() {
    $post_types = get_post_types( ['public'   => true, '_builtin' => false,], 'names', 'and' );
    ?>
    <div class="wrap">
        <h1>WP breadcrumbs</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'my-breadcrumbs-settings-group' ); ?>
            <?php do_settings_sections( 'my-breadcrumbs-settings-group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Show current</th>
                    <td><input type="checkbox" name="show_current" value="1" <?php checked( 1, get_option('show_current') ) ?> /></td>
                </tr>
                <tr>
                    <th scope="row">Show last separator</th>
                    <td><input type="checkbox" name="show_last_sep" value="1" <?php checked( 1, get_option('show_last_sep') ) ?>/></td>
                </tr>
                <tr>
                    <th scope="row">Container class</th>
                    <td><input type="text" name="container_class" value="<?php echo esc_attr( get_option('container_class') ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Breadcrumbs list class</th>
                    <td><input type="text" name="breadcrumbs_list_class" value="<?php echo esc_attr( get_option('breadcrumbs_list_class') ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Breadcrumbs item class</th>
                    <td><input type="text" name="breadcrumbs_item_class" value="<?php echo esc_attr( get_option('breadcrumbs_item_class') ); ?>" /></td>
                </tr>
                <?php
                if(!empty($post_types)){
                    echo '<tr><th scope="row"><br /><br />Custom post types settings</th></tr>';
                    foreach ($post_types as $post_type){
                        register_setting( 'my-breadcrumbs-settings-group', 'breadcrumbs_'.$post_type.'_item_class' ); ?>
                        <tr>
                            <th scope="row"><?php echo $post_type ?> custom archive step</th>
                            <td><input type="text" name="<?php echo 'breadcrumbs_'.$post_type.'_custom_step_text'; ?>" value="<?php echo esc_attr( get_option('breadcrumbs_'.$post_type.'_custom_step_text') ); ?>" /></td>
                            <td><input type="text" name="<?php echo 'breadcrumbs_'.$post_type.'_custom_step_link'; ?>" value="<?php echo esc_attr( get_option('breadcrumbs_'.$post_type.'_custom_step_link') ); ?>" /></td>
                        </tr>
                    <?php }
                }
                ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
<?php } ?>

