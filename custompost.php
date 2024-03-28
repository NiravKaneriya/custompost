<?php
/*
Plugin Name: Create Post Type
Description: Create Custom Post type and also managed post type.
Version: 1.0
Author: Nirav Kaneriya
*/

// Activate plugin hook
register_activation_hook(__FILE__, 'database_table_crud_create_table');

// Create database table on plugin activation
function database_table_crud_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        data varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Insert data into the database table
function database_table_crud_insert_data($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $wpdb->insert(
        $table_name,
        array(
            'data' => $data,
        )
    );
}

// Update data in the database table
function database_table_crud_update_data($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $wpdb->update(
        $table_name,
        array(
            'data' => $data,
        ),
        array('id' => $id)
    );
}

// Delete data from the database table
function database_table_crud_delete_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );
}

// Retrieve and display all data from the database table
function database_table_crud_display_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    if ($results) {
        echo '<h2>All Post Type</h2>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Post Type Name</th></tr>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['data'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No data found.</p>';
    }
}

function create_custom_post_types_from_database() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';
    // Fetch data from the database
    $data_from_database = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if ($data_from_database) {
        foreach ($data_from_database as $data) {
            // Construct labels based on database data
            $labels = array(
                'name'                => _x( $data['data'], 'Post Type General Name', 'my-custom-post-type' ),
                'singular_name'       => _x( $data['data'], 'Post Type Singular Name', 'my-custom-post-type' ),
                'menu_name'           => _x( $data['data'], 'Admin Menu General Name', 'my-custom-post-type' ),
                // ... (other label definitions)
            );

            // Construct arguments for register_post_type
            $args = array(
                'label'               => __( $data['data'], 'my-custom-post-type' ),
                'description'         => __( 'Custom post type for ' . $data['data'], 'my-custom-post-type' ),
                'public'              => true,
                'has_archive'         => true,
                'rewrite'             => array( 'slug' => $data['data'] ),
                'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'categories','tags' ),
                'taxonomies'          => array( 'category', 'post_tag' ), // Include support for categories and tags
                'labels'              => $labels,
                // ... (other arguments)
            );

            // Register the custom post type
            register_post_type( $data['data'], $args );
        }
    }
}

// Hook to execute the function on plugin activation
add_action( 'init', 'create_custom_post_types_from_database' );


// Render the content of the custom plugin page
function create_custom_post_type_content() {
    if (isset($_POST['submit'])) {
        if ($_POST['action'] === 'insert') {
            $data_to_insert = sanitize_text_field($_POST['data_to_insert']);
            database_table_crud_insert_data($data_to_insert);
            echo '<div class="updated"><p>Data inserted successfully!</p></div>';
        } elseif ($_POST['action'] === 'update') {
            $id_to_update = intval($_POST['id_to_update']);
            $data_to_update = sanitize_text_field($_POST['data_to_update']);
            database_table_crud_update_data($id_to_update, $data_to_update);
            echo '<div class="updated"><p>Data updated successfully!</p></div>';
        } elseif ($_POST['action'] === 'delete') {
            $id_to_delete = intval($_POST['id_to_delete']);
            database_table_crud_delete_data($id_to_delete);
            echo '<div class="updated"><p>Data deleted successfully!</p></div>';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Create Custom Post Type</h1>';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=create_custom_post_type" class="nav-tab">Add Post Type</a>
        <a href="?page=edit_custom_post_type" class="nav-tab">View/Edit Post Type</a>
    </h2>
    <?php
    // Check which tab is active
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

    // Display content based on active tab
    if ( $active_tab === 'advanced' ) {
        // Content for Advanced Settings tab
        // Form for updating data
        echo '<h2>Update Custom Post Type</h2>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="text" name="id_to_update" placeholder="ID to Update">';
        echo '<input type="text" name="data_to_update" placeholder="Data to Update">';
        echo '<input type="submit" name="submit" value="Update" class="button button-primary">';
        echo '</form>';

        // Form for deleting data
        echo '<h2>Delete Custom Post Type</h2>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="action" value="delete">';
        echo '<input type="text" name="id_to_delete" placeholder="ID to Delete">';
        echo '<input type="submit" name="submit" value="Delete" class="button button-primary">';
        echo '</form>';
    } else {
        // Default content for General Settings tab
        // Form for inserting data
        echo '<form method="post" action="">';
        echo '<span>Post Type Name: </span><input type="hidden" name="action" value="insert">';
        echo '<input type="text" name="data_to_insert" placeholder="Data to Insert">';
        echo '<input type="submit" name="submit" value="Create" class="button button-primary">';
        echo '</form>';
    }

    echo '</div>'; // .wrap
}
// Render the content of the custom plugin page
function edit_custom_post_type_content() {
    if (isset($_POST['submit'])) {
        if ($_POST['action'] === 'insert') {
            $data_to_insert = sanitize_text_field($_POST['data_to_insert']);
            database_table_crud_insert_data($data_to_insert);
            echo '<div class="updated"><p>Data inserted successfully!</p></div>';
        } elseif ($_POST['action'] === 'update') {
            $id_to_update = intval($_POST['id_to_update']);
            $data_to_update = sanitize_text_field($_POST['data_to_update']);
            database_table_crud_update_data($id_to_update, $data_to_update);
            echo '<div class="updated"><p>Data updated successfully!</p></div>';
        } elseif ($_POST['action'] === 'delete') {
            $id_to_delete = intval($_POST['id_to_delete']);
            database_table_crud_delete_data($id_to_delete);
            echo '<div class="updated"><p>Data deleted successfully!</p></div>';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Create Custom Post Type</h1>';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=create_custom_post_type" class="nav-tab">Add Post Type</a>
        <a href="?page=edit_custom_post_type" class="nav-tab">View/Edit Post Type</a>
    </h2>
    <?php
    // Check which tab is active
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

    // Display content based on active tab

    // Content for Advanced Settings tab
    // Form for updating data
    echo '<h2>Update Custom Post Type</h2>';
    echo '<form method="post" action="">';
    echo '<input type="hidden" name="action" value="update">';
    echo '<input type="text" name="id_to_update" placeholder="ID to Update">';
    echo '<input type="text" name="data_to_update" placeholder="Data to Update">';
    echo '<input type="submit" name="submit" value="Update" class="button button-primary">';
    echo '</form>';

    // Form for deleting data
    echo '<h2>Delete Custom Post Type</h2>';
    echo '<form method="post" action="">';
    echo '<input type="hidden" name="action" value="delete">';
    echo '<input type="text" name="id_to_delete" placeholder="ID to Delete">';
    echo '<input type="submit" name="submit" value="Delete" class="button button-primary">';
    echo '</form>';

    // Display all data
    database_table_crud_display_data();

    echo '</div>'; // .wrap
}

// Add custom plugin page to the admin menu
function custom_post_type_page_menu() {
    add_menu_page(
        'Create Custom Post Type',
        'Create Post Type',
        'manage_options',
        'create_custom_post_type',
        'create_custom_post_type_content',
        'dashicons-database',
        30
    );
    add_submenu_page(
        'create_custom_post_type', // parent slug
        'View/Edit Post Type',      // page title
        'View/Edit Post Type',                 // submenu title
        'manage_options',          // capability
        'edit_custom_post_type',            // menu slug
        'edit_custom_post_type_content'         // callback function
    );

}
add_action('admin_menu', 'custom_post_type_page_menu');
