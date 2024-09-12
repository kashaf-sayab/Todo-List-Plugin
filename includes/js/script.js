document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');


    function showMessage(message, isSuccess) {
        const messageElement = document.getElementById('message');
        
        messageElement.textContent = message;
        messageElement.style.color = 'black'; 
        messageElement.style.display = 'block'
        messageElement.style.padding = '10px';
        messageElement.style.borderRadius = '5px';
        messageElement.style.backgroundColor = isSuccess ? 'rgba(135, 206, 250, 0.3)' : 'rgba(255, 99, 71, 0.3)';
    }
    

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            if (email === '' || password === '') {
                showMessage('Please fill in all fields.', false);
                return;
            } else {
                showMessage('You have successfully logged in.', true);
                loginForm.submit();
            }
        });
    }

    jQuery(document).ready(function($) {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
    
            var email = $('#login-email').val();
            var password = $('#login-password').val();
    
            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'login_user',
                    email: email,
                    password: password,
                    nonce: myPluginData.todoListNonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = myPluginData.todo_list_url;
                    } else {
                        showMessage(response.data.message, false);
                    }
                },
                error: function() {
                    showMessage('An error occurred. Please try again.', false);
                }
            });
        });
    });

    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;

            function checkEmailExists(email, callback) {
                if (email.indexOf('@') <= 0 || email.lastIndexOf('.') <= email.indexOf('@') || email.lastIndexOf('.') >= email.length - 1) {
                    showMessage('Please enter a valid email.', false);
                    return;
                }
            
                setTimeout(function() {
                    const response = { exists: false }; 
                    callback(response);
                }, 500);
            }

            checkEmailExists(email, function(response) {
                if (response.exists) {
                    showMessage('Email is already in use.', false);
                    return;
                }

                if (password.length < 8) {
                    showMessage('Password must be at least 8 characters long.', false);
                    return;
                }

                let hasUppercase = false;
                let hasSpecialChar = false;
                let hasNumber = false;

                for (let i = 0; i < password.length; i++) {
                    if (password[i] >= 'A' && password[i] <= 'Z') {
                        hasUppercase = true;
                    }
                    if (password[i] >= '0' && password[i] <= '9') {
                        hasNumber = true;
                    }
                    if (['@', '$', '!', '#', '%', '*', '?', '&'].includes(password[i])) {
                        hasSpecialChar = true;
                    }
                }

                if (!hasUppercase || !hasSpecialChar || !hasNumber) {
                    showMessage('Password must include at least one uppercase letter, one number, and one special character.', false);
                    return;
                }

                if (password !== confirmPassword) {
                    showMessage('Passwords do not match.', false);
                    return;
                }

                submitFormViaAjax(email, password);
            });
        });
    }

    function submitFormViaAjax(email, password) {
        var uname = document.getElementById('uname').value;

        jQuery.ajax({
            url: myPluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'register_user',
                uname: uname,
                email: email,
                password: password,
                nonce: myPluginData.todoListNonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Registration successful!', true);
                    setTimeout(function() {
                        window.location.href = myPluginData.login_page_url;
                    }, 1000);
                } else {
                    showMessage(response.data.message, false);
                }
            },
            error: function() {
                showMessage('An error occurred. Please try again.', false);
            }
        });
    }
    
    jQuery(document).ready(function($) {
        function fetchTasks() {
            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_todo_tasks',
                    nonce: myPluginData.todoListNonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#todo-list').empty();
                        response.data.tasks.forEach(function(task) {
                            $('#todo-list').append(
                                `<li class="todo-item" data-id="${task.id}">
                                    <input type="checkbox" class="todo-item__checkbox" ${task.status === 'completed' ? 'checked' : ''}>
                                    <span class="todo-item__text">${task.task}</span>
                                    <button class="todo-item__delete">Delete</button>
                                    <select class="todo-item__status">
                                        <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    </select>
                                </li>`
                            );
                        });
                    }
                }
            });
            
        }

    
        fetchTasks();
    
        $('#todo-form').on('submit', function(e) {
            e.preventDefault();
            var task = $('#todo-item').val();

            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'add_todo_task',
                    nonce: myPluginData.todoListNonce,
                    task: task
                },
                success: function(response) {
                    if (response.success) {
                        $('#todo-item').val('');
                        showMessage(response.data.message, true);
                        fetchTasks();
                    } else {
                        showMessage(response.data.message, false);
                    }
                }
            });
        });

        // Delete task
        $('#todo-list').on('click', '.todo-item__delete', function() {
            var taskId = $(this).closest('.todo-item').data('id');

            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_todo_task',
                    nonce: myPluginData.todoListNonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, true);
                        fetchTasks();
                    } else {
                        showMessage(response.data.message, false);
                    }
                }
            });
        });

        // Update task status
        $('#todo-list').on('change', '.todo-item__status', function() {
            var taskId = $(this).closest('.todo-item').data('id');
            var status = $(this).val();

            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_todo_task',
                    nonce: myPluginData.todoListNonce,
                    task_id: taskId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, true);
                        fetchTasks();
                    } else {
                        showMessage(response.data.message, false);
                    }
                }
            });
        });

        // Toggle task completion
        $('#todo-list').on('change', '.todo-item__checkbox', function() {
            var taskId = $(this).closest('.todo-item').data('id');
            var status = $(this).is(':checked') ? 'completed' : 'pending';

            $.ajax({
                url: myPluginData.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_todo_task',
                    nonce: myPluginData.todoListNonce,
                    task_id: taskId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        fetchTasks();
                    } else {
                        showMessage(response.data.message, false);
                    }
                }
            });
        });
    });
});
