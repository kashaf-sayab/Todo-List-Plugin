<?php
function login_todo_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'to_do_list';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
         `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
         `user_id` BIGINT(20) UNSIGNED NOT NULL,
         `task` TEXT NOT NULL,
         `status` ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action( 'plugins_loaded', 'login_todo_create_custom_table' );