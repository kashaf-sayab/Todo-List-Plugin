<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class Todo_CLI_Command {

         //     wp todo add user-id "Task" "pending"
        public function add( $args, $assoc_args ) {
            global $wpdb;

            $user_id = $args[0];
            $task = $args[1];
            $status = isset( $args[2] ) ? $args[2] : 'pending'; 

            if ( empty( $user_id ) || empty( $task ) ) {
                WP_CLI::error( 'You need to provide both a user ID and a task description.' );
                return;
            }

            if ( !in_array( $status, array( 'pending', 'completed' ), true ) ) {
                WP_CLI::error( "Invalid status provided. Allowed values are 'pending' or 'completed'." );
                return;
            }

            $table_name = $wpdb->prefix . 'to_do_list';
            $wpdb->insert( $table_name, array(
                'user_id' => $user_id,
                'task'    => $task,
                'status'  => $status,
            ) );

            if ( $wpdb->insert_id ) {
                WP_CLI::success( "Task added for user {$user_id}: {$task} with status: {$status}" );
            } else {
                WP_CLI::error( 'Failed to add task.' );
            }
        }

    
           //wp todo fetch user-id
        public function fetch( $args, $assoc_args ) {
            global $wpdb;
            $user_id = intval( $args[0] );
            $status = isset( $assoc_args['status'] ) ? $assoc_args['status'] : 'all';

            $user = get_user_by( 'ID', $user_id );
            if ( ! $user ) {
                WP_CLI::error( "User with ID {$user_id} does not exist." );
            }

            $table_name = $wpdb->prefix . 'to_do_list';
            if ( $status == 'all' ) {
                $todos = $wpdb->get_results( $wpdb->prepare(
                    "SELECT task, status FROM $table_name WHERE user_id = %d",
                    $user_id
                ) );
            } else {
                $todos = $wpdb->get_results( $wpdb->prepare(
                    "SELECT task, status FROM $table_name WHERE user_id = %d AND status = %s",
                    $user_id, $status
                ) );
            }

            if ( empty( $todos ) ) {
                WP_CLI::warning( "No to-do items found for user with ID {$user_id}." );
            } else {
                WP_CLI::log( "To-do items for user with ID {$user_id}:" );
                foreach ( $todos as $index => $todo ) {
                    WP_CLI::log( ($index + 1) . ". " . $todo->task . " [" . strtoupper( $todo->status ) . "]" );
                }
            }
        }
    

    
            // wp todo update task-id status
        public function update( $args, $assoc_args ) {
            global $wpdb;

            $task_id = intval( $args[0] );
            $status  = strtolower( sanitize_text_field( $args[1] ) );

            if ( ! in_array( $status, array( 'pending', 'completed' ), true ) ) {
                WP_CLI::error( "Invalid status provided. Status must be 'pending' or 'completed'." );
            }

            $table_name = $wpdb->prefix . 'to_do_list';
            $task = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $task_id
            ) );

            if ( ! $task ) {
                WP_CLI::error( "Task with ID {$task_id} does not exist." );
            }

            $updated = $wpdb->update(
                $table_name,
                array( 'status' => $status ),
                array( 'id' => $task_id )
            );

            if ( false === $updated ) {
                WP_CLI::error( "Failed to update the status for task ID {$task_id}." );
            } else {
                WP_CLI::success( "Task ID {$task_id} status updated to '{$status}'." );
            }
        }
    }
}   

    WP_CLI::add_command( 'todo', 'Todo_CLI_Command' );

