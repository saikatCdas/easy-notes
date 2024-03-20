<?php

namespace easyNotes\Classes;

class AdminAjaxHandler
{
    public function registerEndpoints()
    {
        add_action('wp_ajax_easy-notes_admin_ajax', array($this, 'handleEndPoint'));
    }

    public function handleEndPoint()
    {

        // Verify the nonce to ensure the request is legitimate
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'easy-notes')) {
            wp_send_json_error('error');
        }
        
        $route = sanitize_text_field($_REQUEST['route']);

        $validRoutes = array(
            'get_data' => 'getData',
            'update_note' => 'getData'
        );

        if (isset($validRoutes[$route])) {
            do_action('easy-notes/doing_ajax_forms_' . $route);
            return $this->{$validRoutes[$route]}();
        }
        do_action('easy-notes/admin_ajax_handler_catch', $route);
    }

    protected function getData()
    {
        wp_send_json('hello world');
        // write your sql queries here or another class, then send by a success response
        // wp_send_json_success($data);
    }
}
