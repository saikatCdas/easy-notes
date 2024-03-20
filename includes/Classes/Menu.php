<?php

namespace easyNotes\Classes;

class Menu
{
    public function register()
    {
        add_action('admin_menu', array($this, 'addMenus'));
        add_action('admin_bar_menu', array($this, 'admin_bar_item'), 500);
    }

    public function addMenus()
    {
        $menuPermission = AccessControl::hasTopLevelMenuPermission();
        if (!$menuPermission) {
            return;
        }

        if (function_exists('wp_enqueue_media')) {
            add_filter('user_can_richedit', '__return_true');
            wp_tinymce_inline_scripts();
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        $title = __('Easy Notes', 'textdomain');
        global $submenu;
        add_menu_page(
            $title,
            $title,
            $menuPermission,
            'easy-notes',
            array($this, 'enqueueAssets'),
            'dashicons-media-document',
        );

        $submenu['easy-notes']['my_profile'] = array(
            __('Notes', 'textdomain'),
            $menuPermission,
            'admin.php?page=easy-notes#/',
        );
        $submenu['easy-notes']['settings'] = array(
            __('Creates', 'textdomain'),
            $menuPermission,
            'admin.php?page=easy-notes#/create',
        );
    }
    
    public function admin_bar_item(\WP_Admin_Bar $admin_bar)
    {
        $admin_bar->add_menu(array(
            'id'    => 'wp_admin_bar_easy_notes',
            'title' => $this->adminBarMenuTitleHtml(),
            'href'  => admin_url('admin.php?page=easy-notes'),
            'meta'  => array(
                'title' => __('Easy Notes', 'textdomain'),
            ),
        ));
        $admin_bar->add_menu(array(
            'id'     => 'wp_admin_bar_easy_notes_notes',
            'parent' => 'wp_admin_bar_easy_notes',
            'title'  => __('Notes', 'textdomain'),
            'href'   => 'admin.php?page=easy-notes#/',
            'meta'   => array(
                'title' => __('Creates', 'textdomain'),
            ),
        ));
        $admin_bar->add_menu(array(
            'id'     => 'wp_admin_bar_easy_notes_create',
            'parent' => 'wp_admin_bar_easy_notes',
            'title'  => __('Creates', 'textdomain'),
            'href'   => 'admin.php?page=easy-notes#/create',
            'meta'   => array(
                'title' =>__('Creates', 'textdomain'),
            ),
        ));

    }

    public function enqueueAssets()
    {
        do_action('easy-notes/render_admin_app');
        wp_enqueue_script(
            'easy-notes_boot',
            EASYNOTES_URL . 'assets/js/boot.js',
            array('jquery'),
            EASYNOTES_VERSION,
            true
        );

        // 3rd party developers can now add their scripts here
        do_action('easy-notes/booting_admin_app');
        wp_enqueue_script(
            'easy-notes_js',
            EASYNOTES_URL . 'assets/js/plugin-main-js-file.js',
            array('easy-notes_boot'),
            EASYNOTES_VERSION,
            true
        );

        //enque css file
        wp_enqueue_style('easy-notes_admin_css', EASYNOTES_URL . 'assets/css/element.css');

        $easyNotesAdminVars = apply_filters('easy-notes/admin_app_vars', array(
            //'image_upload_url' => admin_url('admin-ajax.php?action=wpf_global_settings_handler&route=wpf_upload_image'),
            'assets_url' => EASYNOTES_URL . 'assets/',
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('easy-notes'),
        ));

        wp_localize_script('easy-notes_boot', 'easyNotesAdmin', $easyNotesAdminVars);
        add_filter('admin_footer_text', function ($content) {
            $url = '#';
            return '';
        });
    }

    private function adminBarMenuTitleHtml() {
        return '<div 
        class="wp-menu-image dashicons-before dashicons-media-document" 
        style="display: flex; gap: 4px; align-items: center;height: 31px;">' .  __('Easy Notes', 'textdomain') . '</div>';
    }

}
