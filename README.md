# To-Do-list Plugin
   A custom WordPress plugin that provides user authentication with custom login and registration forms, along with a to-do list feature.
#  Motivation
   The project was created to make it easier to manage user login, registration, and task tracking in WordPress. While WordPress handles user management, it lacks simple, customizable login and registration forms. Additionally, having a built-in to-do list feature helps users stay organized. This plugin was developed to address these needs, providing an easy-to-use solution for both custom authentication and task management.
# Build Status
* Known Issues: There are no known issues at the moment.
* Pending Fixes: No pending fixes.
#  Code Style
For consistency and readability, this project follows the WordPress Coding Standards.
*  __PHP:__ Follow the WordPress PHP Coding Standards.
*  __JavaScript:__ Adhere to the WordPress JavaScript Coding Standards.
*  __CSS:__ Use the WordPress CSS Coding Standards.
*  __AJAX:__  Allows for asynchronous communication between the client and server without reloading the page. Used  for sending and receiving data for to-do items.
*  __REST-API:__ API Endpoints: Provides endpoints for CRUD operations on to-do items.
*  __WP-CLI:__ Command Line Interface: Provides commands for managing to-do items from the command line.
 #  Features
 * __Custom Login and Registration Forms:__ Users can first register an account, then log in, and access the to-do list page. This process ensures a smooth and secure user experience
 *  __To-Do List Page:__ After logging in, users can manage and track their tasks on a dedicated To-Do List page, separate from the dashboard.
 *  __AJAX Integration:__  Asynchronous updates for login, registration, and to-do list management without page reloads.
 * __REST-API:__ Provides endpoints for CRUD operations on to-do items.
 * __JWT Authentication:__ Secures API endpoints by requiring a JWT for authentication. Tokens are generated and validated to ensure that only authenticated users can interact with the API.
 * __WP CLI:__ Provides command-line tools for managing tasks within the plugin, enabling administrators to perform operations directly from the terminal.
 # Installation
   __To install and set up this plugin,follow these steps:__
1.__Download the Plugin:__
   Download the ZIP file from this repository: Download ZIP.
 * **install the Plugin:**

2. __Log in to your WordPress admin dashboard__
         Go to Plugins > Add New > Upload Plugin.
         Click Choose File and select the downloaded ZIP file.
         Click Install Now and then Activate Plugin.
3. __Set Up Pages:__
    Create three new pages in WordPress:
      * Login Page
      * Registration Page
      * To-Do List Page
4. __Add Shortcodes:__
      Edit each page and add the appropriate shortcode to display the forms or to-do list:
      * For the login form, use __[ltp_login]__
      * For the registration form, use __[ltp_register]__
      * For the to-do list, use __[ltp_todo]__
5. __Publish the Pages:__
    Publish each page after adding the shortcodes.
# Working Video
[plugin working](https://drive.google.com/file/d/132G7jzGlKPeDWrhqfFt7fXb11ShwGlgk/view?usp=drive_link)
[Rest_API](https://drive.google.com/file/d/1c3KNWI-Mu9o794YbQlV-NcU_6Ms1Ihap/view?usp=drive_link)
[WP-CLI](https://drive.google.com/file/d/1YWmeFSi49tRnZoO5-M6UX7TfSDqWbRHs/view?usp=drive_link)
# screenshot

# Todo-List-Plugin
