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
            'update_note' => 'getData',
            'create_note' => 'createNote',
            'get_note' => 'get_easy_note_by_id'
        );

        if (isset($validRoutes[$route])) {
            do_action('easy-notes/doing_ajax_forms_' . $route);
            return $this->{$validRoutes[$route]}();
        }
        do_action('easy-notes/admin_ajax_handler_catch', $route);
    }
    protected function createNote() {
        try{
            global $wpdb;

            $title = $_REQUEST['title'];
            $created_by = get_current_user_id();

            $data = array(
                'title' => $title,
                'created_by' => $created_by
            );


            $table_name = $wpdb->prefix . 'easy_notes'; 
            $data = $wpdb->insert($table_name, $data);


            if ($wpdb->last_error) {
                wp_send_json_error($wpdb->last_error);
            } else {

                $inserted_id = $wpdb->insert_id; 
                $inserted_data = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $inserted_id", ARRAY_A);

                if ($inserted_data) {
                    wp_send_json_success($inserted_data);
                } else {
                    wp_send_json_error('Error retrieving inserted data.');
                }
            }
        } catch ( \Exception $error ) {
            wp_send_json_error($error);
        }
    }
    protected function get_easy_note_by_id()
    {
        try {
            global $wpdb;
            $note_id = $_REQUEST['note_id'];

            $table_name = $wpdb->prefix . 'easy_notes';
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $note_id);

            $result = $wpdb->get_row($sql, ARRAY_A);
            if ($result) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error('No data found');
            }
        } catch (\Exception $error) {
            wp_send_json_error($error);
        }
    }
}
