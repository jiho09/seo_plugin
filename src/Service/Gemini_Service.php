<?php

namespace Plugin_SEO_Check\Service;

use Plugin_SEO_Check\Core\Logger;

class Gemini_Service {

    private $api_key;
    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct( $api_key = null ) {
        // 우선순위: 1. 전달된 키, 2. 옵션 테이블, 3. 상수
        $this->api_key = $api_key ?: get_option( 'wp_seo_check_gemini_api_key' ) ?: ( defined( 'WP_SEO_CHECK_GEMINI_API_KEY' ) ? WP_SEO_CHECK_GEMINI_API_KEY : null );
        
        if ( ! $this->api_key ) {
            throw new \Exception( __( 'Gemini API key is not configured. Please set it in the plugin settings or wp-config.php.', 'wp-seo-check' ) );
        }
    }

    /**
     * API 키가 설정되어 있는지 확인
     */
    public static function is_api_key_configured() {
        return get_option( 'wp_seo_check_gemini_api_key' ) || defined( 'WP_SEO_CHECK_GEMINI_API_KEY' );
    }

    /**
     * API 키를 안전하게 저장
     */
    public static function save_api_key( $api_key ) {
        if ( empty( $api_key ) ) {
            return delete_option( 'wp_seo_check_gemini_api_key' );
        }
        
        // API 키 유효성 검사
        if ( ! self::validate_api_key( $api_key ) ) {
            return new \WP_Error( 'invalid_api_key', __( 'Invalid API key format.', 'wp-seo-check' ) );
        }
        
        return update_option( 'wp_seo_check_gemini_api_key', sanitize_text_field( $api_key ) );
    }

    /**
     * API 키 유효성 검사
     */
    private static function validate_api_key( $api_key ) {
        return ! empty( $api_key ) && is_string( $api_key ) && strlen( $api_key ) > 10;
    }

    public function generate_meta_tags( $post_title, $post_content ) {
        try {
            $prompt = "Generate an optimized SEO title (50-60 chars) and meta description (120-160 chars) for the following WordPress post.\n\nTitle: {$post_title}\nContent: {$post_content}\n\nFormat the output as a JSON object with 'title' and 'description' keys.";

            $body = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ];

            $args = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->api_key,
                ],
                'body'    => wp_json_encode( $body ),
                'method'  => 'POST',
                'timeout' => 30,
            ];

            $response = wp_remote_post( $this->api_url, $args );

            if ( is_wp_error( $response ) ) {
                Logger::log_gemini_error( $response->get_error_message() );
                return new \WP_Error( 'gemini_api_error', __( 'Failed to connect to Gemini API. Please check your internet connection.', 'wp-seo-check' ) );
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            if ( $response_code !== 200 ) {
                Logger::log_gemini_error( 'HTTP ' . $response_code . ' - ' . wp_remote_retrieve_body( $response ) );
                return new \WP_Error( 'gemini_api_error', __( 'Gemini API returned an error. Please check your API key.', 'wp-seo-check' ) );
            }

            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            if ( ! $data || ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
                return new \WP_Error( 'gemini_api_error', __( 'Invalid response from Gemini API.', 'wp-seo-check' ) );
            }

            $text_response = $data['candidates'][0]['content']['parts'][0]['text'];
            
            // Attempt to parse JSON from the text response
            $json_start = strpos($text_response, '{');
            $json_end = strrpos($text_response, '}');
            
            if ( $json_start !== false && $json_end !== false ) {
                $json_string = substr($text_response, $json_start, $json_end - $json_start + 1);
                $parsed_data = json_decode($json_string, true);
                
                if ( $parsed_data && isset( $parsed_data['title'] ) && isset( $parsed_data['description'] ) ) {
                    return [
                        'title' => sanitize_text_field( $parsed_data['title'] ),
                        'description' => sanitize_textarea_field( $parsed_data['description'] )
                    ];
                }
            }
            
            return new \WP_Error( 'gemini_api_error', __( 'Could not parse response from Gemini API.', 'wp-seo-check' ) );
            
        } catch ( \Exception $e ) {
            Logger::log_gemini_error( $e->getMessage() );
            return new \WP_Error( 'gemini_api_error', __( 'An unexpected error occurred while generating meta tags.', 'wp-seo-check' ) );
        }
    }
}
