<?php

namespace Plugin_SEO_Check\Admin;

class Page_Controller {

    private $page_slug = 'seo-audit';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'SEO Audit', 'wp-seo-check' ),
            __( 'SEO Audit', 'wp-seo-check' ),
            'manage_options',
            $this->page_slug,
            [ $this, 'render_main_page' ],
            'dashicons-analytics',
            20
        );

        add_submenu_page(
            $this->page_slug,
            __( 'Settings', 'wp-seo-check' ),
            __( 'Settings', 'wp-seo-check' ),
            'manage_options',
            'seo-audit-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Render the plugin's main admin page.
     *
     * Outputs the container element for the React UI.
     */
    public function render_main_page() {
        echo '<div id="root"></div>';
    }

    public function render_settings_page() {
        $settings_controller = new Settings_Controller();
        $settings_controller->render_settings_page();
    }

    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_' . $this->page_slug !== $hook && 'seo-audit_page_seo-audit-settings' !== $hook ) {
            return;
        }

        // WordPress에서 React를 로드
        wp_enqueue_script( 'react' );
        wp_enqueue_script( 'react-dom' );

        wp_enqueue_script(
            'wp-seo-check-admin-main',
            plugins_url( 'dist/main.js', WPSEOCHK_PLUGIN_FILE ),
            [ 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n', 'react', 'react-dom' ], // Added dependencies
            null, // Version
            true // In footer
        );

        // Pass REST nonce and API base URL to JavaScript
        wp_localize_script( 'wp-seo-check-admin-main', 'WpSeoChk', [
            'nonce'   => wp_create_nonce( 'wp_rest' ),
            'apiBase' => esc_url_raw( rest_url( 'wp-seo-check/v1' ) ),
        ] );
    }
}
