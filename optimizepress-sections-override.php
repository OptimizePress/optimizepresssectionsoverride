<?php
/*
Plugin Name: OptimizePress Sections Override
Plugin URI: http://www.optimizepress.com
Description: Override header & navigation, typography, footer or colour scheme options on multiple OptimizePress LiveEditor pages with this simple plugin.
Version: 1.1.0
Author: OptimizePress
Author URI: http://www.optimizepress.com
*/

class OptimizePress_SectionsOverride
{
    /**
     * @var OptimizePress_SectionsOverride
     */
    protected static $instance;

    /**
     * Registering actions and filters
     */
    protected function __construct()
    {
        add_action('admin_menu', array($this, 'registerAdminPage'), 101);
    }

    /**
     * Singleton
     * @return OptimizePress_SectionsOverride
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Adds menu page, in network admin only
     * @return void
     */
    public function registerAdminPage()
    {
        add_submenu_page('optimizepress', 'OptimizePress Sections Override', 'Sections Override', 'edit_theme_options', 'optimizepress-sections-override', array($this, 'displayAdminPage'));
    }

    /**
     * Site cloner page logic
     * @return void
     */
    public function displayAdminPage()
    {
        /*
         * Lets go ahead and create a new blog
         */
        if (isset($_POST['overwrite-sections'])) {
            check_admin_referer('op-sections-override-overwrite', '_wpnonce_op-sections-override-overwrite');

            $errors = $sections = array();

            /*
             * Checking if all required fields have been filled
             */
            if (!isset($_POST['master_page'])) {
                $errors[] = 'Select master page';
            }

            if (!isset($_POST['minion_pages'])) {
                $errors[] = 'Select one or more pages that will be overwritten';
            }

            if (!isset($_POST['sections'])) {
                $errors[] = 'Check one or more sections that will be overwritten';
            }

            if (empty($errors)) {
                $masterId = sanitize_text_field($_POST['master_page']);
                foreach ($_POST['sections'] as $section) {
                    $sections[$section] = get_post_meta($masterId, '_optimizepress_' . $section, true);
                }
                foreach ($_POST['minion_pages'] as $minionId) {
                    foreach ($_POST['sections'] as $section) {
                        update_post_meta($minionId, '_optimizepress_' . $section, $sections[$section]);
                    }
                }

                // working on footer
                if (in_array('footer_area', $_POST['sections'])) {
                    // checking if there is a Extra large footer elements present on master page
                    global $wpdb;
                    $footer = $wpdb->get_results($wpdb->prepare(
                        "SELECT type, layout FROM {$wpdb->prefix}optimizepress_post_layouts WHERE post_id = %d AND status = 'publish' AND type = 'footer'",
                        $masterId
                    ), ARRAY_A);
                    
                    if (count($footer) > 0) {
                        foreach ($_POST['minion_pages'] as $minion_id) {

                            $duplicate = $wpdb->get_results($wpdb->prepare(
                                "SELECT post_id from {$wpdb->prefix}optimizepress_post_layouts WHERE post_id = %d AND status = 'publish' AND type = 'footer'",
                                $minion_id
                            ), ARRAY_A);

                            $sql = '';
                            if (count($duplicate) > 0) {
                                $sql = "UPDATE {$wpdb->prefix}optimizepress_post_layouts SET layout = %s, modified = %s WHERE post_id = %d AND status = 'publish' AND type = 'footer'";
                                $sql = $wpdb->prepare($sql, $footer[0]['layout'], current_time('mysql'), $minion_id);
                            } else {
                                $sql = "INSERT INTO {$wpdb->prefix}optimizepress_post_layouts (post_id, type, layout, status, modified) VALUES (%s,'footer', %s, 'publish', %s)";
                                $sql = $wpdb->prepare($sql, $minion_id, $footer[0]['layout'], current_time('mysql'));
                            }

                            $wpdb->query($sql);
                        }
                    }
                }
                /*
                 * Setting success message
                 */
                $messages = array(
                    'Sections overwritten',
                );
            }
        }

        require_once plugin_dir_path(__FILE__) . 'views/admin.php';
    }
}
add_action('plugins_loaded', array('OptimizePress_SectionsOverride', 'getInstance'));