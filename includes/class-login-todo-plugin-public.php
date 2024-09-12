<?php

if (!defined('ABSPATH')) {
    exit;
}
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
class login_todo_Plugin_Public {
    private $version = '1.0.0';

    public function __construct() {
              // Register REST API routes
            add_action('rest_api_init', array($this, 'register_rest_api_routes'));
            
    }

    public function enqueue_assets() {
        wp_enqueue_style('custom-authentication-css', plugin_dir_url(__FILE__) . 'css/styles.css');
        wp_enqueue_script('custom-authentication-js', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), null, true);
    
        function get_page_url_by_shortcode($shortcode) {
            $pages = get_pages();
            foreach ($pages as $page) {
                if (has_shortcode($page->post_content, $shortcode)) {
                    return get_permalink($page->ID);
                }
            }
            return '';
        }
    
        $login_url = get_page_url_by_shortcode('ltp_login');
        $todo_list_url = get_page_url_by_shortcode('ltp_todo');
    
        wp_localize_script('custom-authentication-js', 'myPluginData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'todoListNonce' => wp_create_nonce('todo-list-nonce'),
            'todo_list_url' => $todo_list_url,
            'login_page_url' => $login_url
        ));
    }

    public function display_registration_form() {
        ob_start();
        ?>
        <div class="container">
            <form action="register.php" method="POST" id="register-form">
                <h1>Sign up!</h1>
                <div id="message" class="message"></div>
                <label for="user-name"> User Name</label><br>
                <input type="text" id="uname" name="uname" size="50" placeholder="Enter your name" title="User-Name"><br>
                <label for="register-email"> Email</label><br>
                <input type="email" id="register-email" name="register-email" size="50" placeholder="abc@gmail.com" title="Enter valid email" required><br>
                <label for="password"> Password</label><br>
                <input type="password" id="register-password" name="register-password" size="50" placeholder="password must be 8 characters" required><br>
                <label for="confirm-password"> Confirm password</label><br>
                <input type="password" id="register-confirm-password" name="password" size="50" placeholder="Re-enter password" required><br>
                <button type="submit" title="click to Register">Register</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function register_user() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        $uname = sanitize_text_field($_POST['uname']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if (empty($uname) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }

        if (username_exists($uname) || email_exists($email)) {
            wp_send_json_error(array('message' => 'Username or email already exists.'));
        }

        $user_id = wp_create_user($uname, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }

        wp_send_json_success();
    }

    public function display_login_form() {
        ob_start();
        ?>
        <div class="container">
            <form action="login.php" method="POST" id="login-form">
                <h1>Sign in!</h1>
                <div id="message" class="message"></div>
                <label for="email">Email:</label><br>
                <input type="email" name="email" id="login-email" placeholder="Enter your email here!" size="50" required ><br>
                <label for="password">Password:</label><br>
                <input type="password" name="password" id="login-password" placeholder="Enter password" size="50" required><br>
                <button type="submit" title="click to submit">Submit</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function login_user() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $user = wp_authenticate($email, $password);

        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid email or password.'));
        } else {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_send_json_success();
        }
    }

    public function render_todo_list() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view the to-do list.</p>';
        }

        ob_start();
        ?>
        <div class="todo-list-container">
            <h2>My To-Do List</h2>
            <div id="message" class="message"></div>
            <form id="todo-form">
                <input type="text" id="todo-item" placeholder="Add a new item" required>
                <button type="submit">Add</button>
            </form>
            <ul id="todo-list">
                
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_add_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to add a task.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $task = sanitize_text_field($_POST['task']);
        
        if (empty($task)) {
            wp_send_json_error(array('message' => 'Task cannot be empty.'));
        }

        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'task' => $task,
                'status' => 'pending'
            ),
            array(
                '%d',
                '%s',
                '%s'
            )
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to add task.'));
        }

        wp_send_json_success(array('message' => 'Task added successfully.'));
    }

    public function handle_fetch_todo_tasks() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to view tasks.'));
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'to_do_list';

        $tasks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, task, status FROM $table_name WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );

        if ($tasks === false) {
            wp_send_json_error(array('message' => 'Failed to fetch tasks.'));
        }

        wp_send_json_success(array('tasks' => $tasks));
    }

    public function handle_update_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to update a task.'));
        }

        global $wpdb;
        $task_id = intval($_POST['task_id']);
        $status = sanitize_text_field($_POST['status']);
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $task_id, 'user_id' => $user_id),
            array('%s'),
            array('%d', '%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to update task.'));
        }

        wp_send_json_success(array('message' => 'Task updated successfully.'));
    }

    public function handle_delete_todo_task() {
        check_ajax_referer('todo-list-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to delete a task.'));
        }

        global $wpdb;
        $task_id = intval($_POST['task_id']);
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'to_do_list';

        $result = $wpdb->delete(
            $table_name,
            array('id' => $task_id, 'user_id' => $user_id),
            array('%d', '%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to delete task.'));
        }

        wp_send_json_success(array('message' => 'Task deleted successfully.'));
    }

    public function restrict_access() {
        if (is_user_logged_in()) {
            if (is_page()) {
                global $post;
                $content = $post->post_content;
                $contains_login_shortcode = has_shortcode($content, 'ltp_login');
                $contains_register_shortcode = has_shortcode($content, 'ltp_register');
                if (($contains_login_shortcode || $contains_register_shortcode) && !current_user_can('administrator')) {
                    $todo_page_id = $this->get_page_id_by_shortcode('ltp_todo');
                    $todo_page_url = get_permalink($todo_page_id);
                    wp_redirect($todo_page_url);
                    exit;
                }
            }
        }
    }
    
    private function get_page_id_by_shortcode($shortcode) {
        $pages = get_pages();
        foreach ($pages as $page) {
            if (has_shortcode($page->post_content, $shortcode)) {
                return $page->ID;
            }
        }
        return 0;
    }
    
     // Register all REST API routes
    
    public function register_rest_api_routes() {
        $this->fetch_user_tasks_api_endpoints_routes();
        $this->add_user_task_api_endpoints_routes();
        $this->update_user_task_api_endpoints_routes();
    }


     // Register route to fetch user tasks
     
    public function fetch_user_tasks_api_endpoints_routes() {
        register_rest_route('ltp/v1', '/task/(?P<user_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'fetch_user_tasks'), 
        ));
    }

    
     // Callback to fetch user tasks
     
    public function fetch_user_tasks($request) {
        global $wpdb;
        $user_id = intval($request['user_id']);

        if ($user_id <= 0) {
            return new WP_Error('invalid_user_id', 'Invalid user ID.', array('status' => 400));
        }

        $tasks = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}to_do_list WHERE user_id = %d", $user_id),
            ARRAY_A
        );

        if (empty($tasks)) {
            $tasks = array(); 
        }

        return new WP_REST_Response($tasks, 200);
    }

    
     // Register route to add new user task
     
    public function add_user_task_api_endpoints_routes() {
        register_rest_route('ltp/v1', '/task/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_new_user_tasks'),
            'args' => array(
                'task' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return !empty($param);
                    }
                ),
                'status' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return in_array($param, array('pending', 'completed'), true);
                    }
                ),
            ),
        ));
    }

     // Callback to add new user task
    
    public function add_new_user_tasks($request) {
        global $wpdb;
        $user_id = get_current_user_id('user-id');
        $task = sanitize_text_field($request['task']);
        $status = sanitize_text_field($request['status']);

        if ($user_id <= 0) {
            return new WP_Error('invalid_user_id', 'Invalid user ID provided.', array('status' => 400));
        }

        
        $wpdb->insert(
            "{$wpdb->prefix}to_do_list",
            array(
                'user_id' => $user_id,
                'task' => $task,
                'status' => $status,
            ),
            array('%d', '%s', '%s')
        );

        if ($wpdb->insert_id) {
            return new WP_REST_Response(array('task_id' => $wpdb->insert_id), 201);
        } else {
            return new WP_Error('db_insert_error', 'Failed to add task.', array('status' => 500));
        }
    }

    
     // Register route to update user task
     
    public function update_user_task_api_endpoints_routes() {
        register_rest_route('ltp/v1', '/task/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_user_task_status'),
            'args' => array(
                'task_id' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return !empty($param);
                    }
                ),
                'status' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return in_array($param, array('pending', 'completed'), true);
                    }
                ),
            ),
        ));
    }

    
     //Callback to update user task
     
    public function update_user_task_status($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $task_id = intval($request['task_id']);
        $status = sanitize_text_field($request['status']);

        if ($user_id <= 0) {
            return new WP_Error('invalid_user_id', 'Invalid user ID.', array('status' => 400));
        }

        $updated = $wpdb->update(
            "{$wpdb->prefix}to_do_list",
            array('status' => $status),
            array(
                'id' => $task_id,
                'user_id' => $user_id
            ),
            array('%s'),
            array('%d', '%d')
        );

        if (false === $updated) {
            return new WP_Error('db_update_error', 'Failed to update task.', array('status' => 500));
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    public function check_jwt_auth($request) {
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    
        if (empty($auth_header)) {
            return new WP_Error('rest_forbidden', 'JWT Token is missing.', array('status' => 403));
        }
    

        $auth_header = str_replace('Bearer ', '', $auth_header);
    
        try {
            $secret = JWT_AUTH_SECRET_KEY;
    
            
            $decoded = JWT::decode($auth_header, new Key($secret, 'HS256'));
    
        
            $now = time();
            if (isset($decoded->exp) && $decoded->exp < $now) {
                return new WP_Error('rest_forbidden', 'JWT Token has expired.', array('status' => 403));
            }
    
            
            if (isset($decoded->data->user_id)) {
                $user = get_user_by('id', $decoded->data->user_id);
                if ($user) {
                    wp_set_current_user($user->ID);
                    return true;
                }
            }
    
        } catch (Exception $e) {
            return new WP_Error('rest_forbidden', 'Invalid JWT Token: ' . $e->getMessage(), array('status' => 403));
        }
    
        return new WP_Error('rest_forbidden', 'JWT Token is invalid.', array('status' => 403));
    }
}
