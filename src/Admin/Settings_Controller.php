<?php

namespace Plugin_SEO_Check\Admin;

use Plugin_SEO_Check\Service\Gemini_Service;

class Settings_Controller {

    private $option_group = 'wp_seo_check_options';
    private $option_name = 'wp_seo_check_settings';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_wp_seo_check_save_api_key', [ $this, 'handle_api_key_save' ] );
    }

    public function register_settings() {
        register_setting(
            $this->option_group,
            $this->option_name,
            [ $this, 'sanitize_settings' ]
        );

        add_settings_section(
            'wp_seo_check_gemini_section',
            __( 'Gemini AI Settings', 'wp-seo-check' ),
            [ $this, 'render_gemini_section' ],
            'wp_seo_check_settings'
        );

        add_settings_field(
            'gemini_api_key',
            __( 'Gemini API Key', 'wp-seo-check' ),
            [ $this, 'render_api_key_field' ],
            'wp_seo_check_settings',
            'wp_seo_check_gemini_section'
        );
    }

    public function render_gemini_section() {
        echo '<p>' . __( 'Configure your Gemini AI API key to enable AI-powered meta tag suggestions.', 'wp-seo-check' ) . '</p>';
        
        if ( ! Gemini_Service::is_api_key_configured() ) {
            echo '<div class="notice notice-warning"><p>' . __( 'Warning: Gemini API key is not configured. AI features will not work.', 'wp-seo-check' ) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>' . __( 'Gemini API key is configured and ready to use.', 'wp-seo-check' ) . '</p></div>';
        }
    }

    public function render_api_key_field() {
        $current_key = get_option( 'wp_seo_check_gemini_api_key' );
        $masked_key = $current_key ? str_repeat( '*', strlen( $current_key ) - 4 ) . substr( $current_key, -4 ) : '';
        
        echo '<input type="password" id="gemini_api_key" name="' . $this->option_name . '[gemini_api_key]" value="' . esc_attr( $masked_key ) . '" class="regular-text" />';
        echo '<p class="description">' . __( 'Enter your Gemini API key. You can also set it in wp-config.php as WP_SEO_CHECK_GEMINI_API_KEY.', 'wp-seo-check' ) . '</p>';
        
        if ( $current_key ) {
            echo '<p><a href="' . admin_url( 'admin-post.php?action=wp_seo_check_clear_api_key&_wpnonce=' . wp_create_nonce( 'wp_seo_check_clear_api_key' ) ) . '" class="button button-secondary">' . __( 'Clear API Key', 'wp-seo-check' ) . '</a></p>';
        }
    }

    public function sanitize_settings( $input ) {
        $sanitized = [];
        
        if ( isset( $input['gemini_api_key'] ) ) {
            $api_key = sanitize_text_field( $input['gemini_api_key'] );
            
            // 마스킹된 키인지 확인 (모든 문자가 *인 경우)
            if ( ! preg_match( '/^\*+$/', $api_key ) ) {
                $result = Gemini_Service::save_api_key( $api_key );
                if ( is_wp_error( $result ) ) {
                    add_settings_error(
                        'wp_seo_check_messages',
                        'wp_seo_check_message',
                        $result->get_error_message(),
                        'error'
                    );
                } else {
                    add_settings_error(
                        'wp_seo_check_messages',
                        'wp_seo_check_message',
                        __( 'API key saved successfully.', 'wp-seo-check' ),
                        'success'
                    );
                }
            }
        }
        
        return $sanitized;
    }

    public function handle_api_key_save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-seo-check' ) );
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'wp_seo_check_save_api_key' ) ) {
            wp_die( __( 'Security check failed.', 'wp-seo-check' ) );
        }

        $api_key = sanitize_text_field( $_POST['gemini_api_key'] ?? '' );
        $result = Gemini_Service::save_api_key( $api_key );

        if ( is_wp_error( $result ) ) {
            wp_redirect( add_query_arg( 'error', urlencode( $result->get_error_message() ), admin_url( 'admin.php?page=seo-audit-settings' ) ) );
        } else {
            wp_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=seo-audit-settings' ) ) );
        }
        exit;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php settings_errors( 'wp_seo_check_messages' ); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( 'wp_seo_check_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
} 