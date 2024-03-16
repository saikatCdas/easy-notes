<?php

namespace easyNotes\Classes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler Class
 * @since 1.0.0
 */
class Activator
{
    public function migrateDatabases($network_wide = false)
    {
        global $wpdb;
        if ($network_wide) {
            // Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
            if (function_exists('get_sites') && function_exists('get_current_network_id')) {
                $site_ids = get_sites(array('fields' => 'ids', 'network_id' => get_current_network_id()));
            } else {
                $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;");
            }
            // Install the plugin for all these sites.
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                $this->migrate();
                restore_current_blog();
            }
        } else {
            $this->migrate();
        }
    }

    private function migrate()
    {
        /*
        * database creation commented out,
        * If you need any database just active this function bellow
        * and write your own query at createBookmarkTable function
        */

        $this->createNotesTable();
    }

    public function createNotesTable()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'easy_notes'; // Changed table name to 'easy_notes'
        $sql = "CREATE TABLE $table_name (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` TEXT NULL COMMENT 'Title or Name of the Task , It can be longer than 255 characters.',
                `description` LONGTEXT NULL,
                `created_by` INT UNSIGNED NOT NULL,
                `settings` TEXT NULL COMMENT 'Serialized',
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
                ) $charset_collate;";
            
        $this->runSQL($sql, $table_name);

    }

    private function runSQL($sql, $tableName)
    {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

}
