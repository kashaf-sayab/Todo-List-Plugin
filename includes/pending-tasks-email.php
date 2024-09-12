<?php

function schedule_pending_tasks_email_activation() {
    if (!wp_next_scheduled('send_pending_tasks_email_daily')) {
        wp_schedule_event(time(), 'daily', 'send_pending_tasks_email_daily');
    }
}
register_activation_hook(__FILE__, 'schedule_pending_tasks_email_activation');

add_action('send_pending_tasks_email_daily', 'send_pending_tasks_email_to_users');


function send_pending_tasks_email_to_users() {
    global $wpdb;

    $users = get_users(array('role__not_in' => array('administrator')));

    foreach ($users as $user) {
    
        $user_id = $user->ID;
        $user_email = $user->user_email;

        
        $table_name = $wpdb->prefix . 'to_do_list'; 
        $pending_tasks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND status = 'pending'",
            $user_id
        ));

        
        if (!empty($pending_tasks)) {
            
            $task_list = '';
            foreach ($pending_tasks as $task) {
                $task_list .= "<li>{$task->task}</li>"; 
            }

        
            $email_content = "
                <p>Dear {$user->display_name},</p>
                <p>You have the following pending tasks:</p>
                <ul>$task_list</ul>
                <p>Please complete them as soon as possible.</p>
            ";

            $subject = 'Pending Tasks Reminder';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($user_email, $subject, $email_content, $headers);
        }
    }
}

function unschedule_pending_tasks_email() {
    $timestamp = wp_next_scheduled('send_pending_tasks_email_daily');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'send_pending_tasks_email_daily');
    }
}
register_deactivation_hook(__FILE__, 'unschedule_pending_tasks_email');
