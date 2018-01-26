<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @version 1.0
 */
/*
Plugin Name: Auto Create User
Description: Auto Create User if not existing in wordpress user
Version: 1.0
Author: Strategies360 Team
Author URI: https://strategies360.com
*/

function acu_setting_submenu_page() {
    add_submenu_page( 'options-general.php', __( 'Auto Create User', 'acuS360' ), __( 'Auto Create User', 'acuS360' ), 'manage_options', 'acu-setting', 'acuSetting' );
}
add_action( 'admin_menu', 'acu_setting_submenu_page', 2 );

function AcuPluginsActivate() {
    installData();
}
register_activation_hook( __FILE__, 'AcuPluginsActivate' );


function AcuPluginsDeactivate(){
    uninstallData();
}
register_deactivation_hook( __FILE__, 'AcuPluginsDeactivate' );


function AcuPluginsUninstall() {
    uninstallData();
}
register_uninstall_hook( __FILE__, 'AcuPluginsUninstall' );


function doLogin($username, $password) {
    if(strlen($username) > 0 && strlen($password) > 0) {
        check_login_wp($username, $password);
    }
}
add_action('wp_authenticate', 'doLogin', 30, 2);


function check_login_wp($username, $password) {
    global $wpdb;

    $user = get_user_by('login', $username);
    if($user === false) {
        $table = $wpdb->prefix . "acu_setting";
        $sql = "SELECT * FROM $table;";
        $getDataTable = $wpdb->get_results($sql);
        if($getDataTable) {
            $databaseTable = '';
            $dataTable = '';
            foreach($getDataTable as $list) {
                if($list->name === 'database_name') {
                    $databaseTable = $list->value;
                }
                if($list->name === 'table_name') {
                    $dataTable = $list->value;
                }
            }
            if(strlen($databaseTable) > 0 && strlen($dataTable) > 0) {
                $sql = "SELECT * FROM $databaseTable.$dataTable WHERE `username`='$username'";
                $getData = $wpdb->get_row($sql);
                if($getData) {
                    $getPassword = $getData->password;
                    $getSalt = $getData->salt;
                    if(checkPassword($password, $getPassword, $getSalt)) {
                        $email = $getData->email;
                        $name = $getData->usertitle;
                        createUserWp($username, $password, $email, $name);
                    }
                }
            }
        }
    }
}

function createUserWp($username, $password, $email, $name) {
    $user_id = username_exists( $username );
    if ( !$user_id and email_exists($email) == false ) {
        $user_id = wp_create_user( $username, $password, $email );
        wp_update_user( array( 'ID' => $user_id, 'first_name' => $name ) );
    }
}

function checkPassword($sendPassword, $getPassword, $getSalt) {
    if(md5(md5($sendPassword).$getSalt) == $getPassword) {
        return true;
    }
    return false;
}

function acuSetting() {

    global $wpdb;

    $dataTable = '';
    $databaseTable = '';
    $table = $wpdb->prefix . "acu_setting";
    $sql = "SELECT * FROM $table;";
    $getDataTable = $wpdb->get_results($sql);
    if($getDataTable) {
        $databaseTable = '';
        $dataTable = '';
        foreach ($getDataTable as $list) {
            if ($list->name === 'database_name') {
                $databaseTable = $list->value;
            }
            if ($list->name === 'table_name') {
                $dataTable = $list->value;
            }
        }
    }

    if($_POST) {

        $table = $wpdb->prefix . "acu_setting";
        $databaseName = isset($_POST['databaseName']) ? esc_attr($_POST['databaseName']) : '';
        if(strlen($databaseName) > 0) {
            $databaseTable = $databaseName;
        }
        $tableName = isset($_POST['tableName']) ? esc_attr($_POST['tableName']) : '';
        if(strlen($tableName) > 0) {
            $dataTable = $tableName;
        }
        $sql = "UPDATE $table SET `value`='$databaseTable' WHERE `name`='database_name';";
        $wpdb->query($sql);
        $sql = "UPDATE $table SET `value`='$dataTable' WHERE `name`='table_name';";
        $wpdb->query($sql);

    }

    include "setting-page.php";
}

function installData() {

    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $table = $wpdb->prefix . "acu_setting";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "DROP TABLE IF EXISTS $table";
    dbDelta( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS $table (
            `id` mediumint(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `value` text,
            UNIQUE (`id`)
            ) $charset_collate;";
    dbDelta( $sql );

    $list_field = [
        'database_name',
        'table_name'
    ];

    $sql_string = '';
    foreach($list_field as $list) {
        $sql_string .= "('$list', '')";
    }

    $sql = "INSERT INTO $table (`name`, `value`) 
            VALUES $sql_string;";
    dbDelta( $sql );

    $table = $wpdb->prefix . "acu_history";
    $sql = "CREATE TABLE IF NOT EXISTS $table (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `user_wp_id` mediumint(10) NOT NULL,
            `user_id` mediumint(10) NOT NULL,
            `created_at` datetime NOT NULL,
            UNIQUE (`id`)
            ) $charset_collate;";
    dbDelta( $sql );

}

function uninstallData() {
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $table = $wpdb->prefix . "acu_setting";
    $sql = "DROP TABLE IF EXISTS $table";
    dbDelta( $sql );

    $table = $wpdb->prefix . "acu_history";
    $sql = "DROP TABLE IF EXISTS $table";
    dbDelta( $sql );
}