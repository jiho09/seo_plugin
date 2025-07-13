<?php

namespace Plugin_SEO_Check\Core;

class Logger {

    const LOG_OPTION = 'wp_seo_check_error_log';
    const MAX_LOG_ENTRIES = 100;

    /**
     * 에러 로그 기록
     */
    public static function log_error( $message, $context = [] ) {
        $log_entry = [
            'timestamp' => current_time( 'mysql' ),
            'message'   => $message,
            'context'   => $context,
            'user_id'   => get_current_user_id(),
            'url'       => $_SERVER['REQUEST_URI'] ?? '',
        ];

        $logs = get_option( self::LOG_OPTION, [] );
        $logs[] = $log_entry;

        // 최대 로그 개수 제한
        if ( count( $logs ) > self::MAX_LOG_ENTRIES ) {
            $logs = array_slice( $logs, -self::MAX_LOG_ENTRIES );
        }

        update_option( self::LOG_OPTION, $logs );

        // WordPress 에러 로그에도 기록
        error_log( 'WP SEO Check: ' . $message );
    }

    /**
     * 로그 조회
     */
    public static function get_logs( $limit = 50 ) {
        $logs = get_option( self::LOG_OPTION, [] );
        return array_slice( $logs, -$limit );
    }

    /**
     * 로그 삭제
     */
    public static function clear_logs() {
        delete_option( self::LOG_OPTION );
    }

    /**
     * API 에러 로그
     */
    public static function log_api_error( $endpoint, $error_message, $response_code = null ) {
        self::log_error( "API Error: {$endpoint} - {$error_message}", [
            'endpoint'     => $endpoint,
            'response_code' => $response_code,
        ] );
    }

    /**
     * Gemini API 에러 로그
     */
    public static function log_gemini_error( $error_message, $post_id = null ) {
        self::log_error( "Gemini API Error: {$error_message}", [
            'service' => 'gemini',
            'post_id' => $post_id,
        ] );
    }

    /**
     * 스캔 에러 로그
     */
    public static function log_scan_error( $post_id, $error_message ) {
        self::log_error( "Scan Error for Post {$post_id}: {$error_message}", [
            'service' => 'scanner',
            'post_id' => $post_id,
        ] );
    }
} 