<?php

namespace easyNotes\Classes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register and initialize custom post type for easy-notes
 * @since 1.0.0
 */
class PostType
{
    public function __construct()
    {
        add_action('init', array($this, 'register'));
    }

    public function register()
    {
        $args = array(
            'capability_type' => 'post',
            'public' => false,
            'show_ui' => false,
        );
        register_post_type('easy-notes', $args);
    }
}
