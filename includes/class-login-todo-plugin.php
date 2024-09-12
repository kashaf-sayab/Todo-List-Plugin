<?php
if (!defined('ABSPATH')) {
    exit;
}

class login_todo_plugin {

    public function __construct() {

        $this->load_dependencies();

        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-login-todo-plugin-public.php';
    }

    private function define_public_hooks() {
        $plugin_public = new login_todo_Plugin_Public();

        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_assets'));

        // Shortcodes
        add_shortcode('ltp_register', array($plugin_public, 'display_registration_form'));
        add_shortcode('ltp_login', array($plugin_public, 'display_login_form'));
        add_shortcode('ltp_todo', array($plugin_public, 'render_todo_list'));

        // AJAX Actions
        add_action('wp_ajax_register_user', array($plugin_public, 'register_user'));
        add_action('wp_ajax_nopriv_register_user', array($plugin_public, 'register_user'));

        add_action('wp_ajax_login_user', array($plugin_public, 'login_user'));
        add_action('wp_ajax_nopriv_login_user', array($plugin_public, 'login_user'));

        add_action('wp_ajax_add_todo_task', array($plugin_public, 'handle_add_todo_task'));
        add_action('wp_ajax_fetch_todo_tasks', array($plugin_public, 'handle_fetch_todo_tasks'));
        add_action('wp_ajax_update_todo_task', array($plugin_public, 'handle_update_todo_task'));
        add_action('wp_ajax_delete_todo_task', array($plugin_public, 'handle_delete_todo_task'));

        add_action('template_redirect', array($plugin_public, 'restrict_access'));
        
    }

    public function run() {
    
    }
}