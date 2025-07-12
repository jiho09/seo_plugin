<?php

namespace Plugin_SEO_Check\Controller;

use Plugin_SEO_Check\Repository\ResultRepo;
use Plugin_SEO_Check\Repository\Performance_Metrics_Repo;

class Api_Controller {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'wp-seo-check/v1', '/audits', [
            'methods'  => 'GET',
            'callback' => [ $this, 'get_all_audits' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ] );

        register_rest_route( 'wp-seo-check/v1', '/audits/(?P<post_id>\d+)', [
            'methods'  => 'GET',
            'callback' => [ $this, 'get_audit_for_post' ],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
            'args' => [
                'post_id' => [
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ],
            ],
        ] );

        register_rest_route( 'wp-seo-check/v1', '/scan-all', [
            'methods'  => 'POST',
            'callback' => [ $this, 'scan_all_posts' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ] );

        register_rest_route( 'wp-seo-check/v1', '/performance-metrics', [
            'methods'  => 'POST',
            'callback' => [ $this, 'save_performance_metric' ],
            'permission_callback' => '__return_true' // No specific capability needed for public metrics
        ] );

        register_rest_route( 'wp-seo-check/v1', '/generate-meta-tags', [
            'methods'  => 'POST',
            'callback' => [ $this, 'generate_meta_tags_endpoint' ],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ],
            ],
        ] );
    }

    public function get_all_audits() {
        $repo = new ResultRepo();
        $results = $repo->get_all();
        return new \WP_REST_Response( $results, 200 );
    }

    public function get_audit_for_post( \WP_REST_Request $request ) {
        $post_id = (int) $request['post_id'];
        $repo = new ResultRepo();
        $result = $repo->get_by_post_id( $post_id );
        return new \WP_REST_Response( $result, 200 );
    }

    public function scan_all_posts() {
        $scanner_service = new \Plugin_SEO_Check\Service\Scanner_Service();
        $posts = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ] );
        
        foreach ( $posts as $post ) {
            $scanner_service->scan_post( $post->ID );
        }

        return new \WP_REST_Response( [ 'success' => true, 'scanned_count' => count( $posts ) ], 200 );
    }

    public function save_performance_metric( \WP_REST_Request $request ) {
        $post_id = $request->get_param( 'post_id' );
        $metric_name = $request->get_param( 'name' );
        $metric_value = $request->get_param( 'value' );

        if ( empty( $post_id ) || empty( $metric_name ) || empty( $metric_value ) ) {
            return new \WP_REST_Response( [ 'error' => 'Invalid metric data' ], 400 );
        }

        $metrics_repo = new Performance_Metrics_Repo();
        $metrics_repo->create( (int) $post_id, $metric_name, (float) $metric_value );

        return new \WP_REST_Response( [ 'success' => true, 'message' => 'Metric received and saved' ], 200 );
    }

    public function generate_meta_tags_endpoint( \WP_REST_Request $request ) {
        $post_id = (int) $request->get_param( 'post_id' );
        $post = get_post( $post_id );

        if ( ! $post ) {
            return new \WP_REST_Response( [ 'error' => 'Post not found' ], 404 );
        }

        // Placeholder for Gemini API Key. In a real scenario, this would come from settings.
        $gemini_api_key = defined( 'WP_SEO_CHECK_GEMINI_API_KEY' ) ? WP_SEO_CHECK_GEMINI_API_KEY : '';

        if ( empty( $gemini_api_key ) ) {
            return new \WP_REST_Response( [ 'error' => 'Gemini API Key not configured.' ], 400 );
        }

        $gemini_service = new \Plugin_SEO_Check\Service\Gemini_Service( $gemini_api_key );
        $suggestions = $gemini_service->generate_meta_tags( $post->post_title, $post->post_content );

        if ( is_wp_error( $suggestions ) ) {
            return new \WP_REST_Response( [ 'error' => $suggestions->get_error_message() ], 500 );
        }

        return new \WP_REST_Response( $suggestions, 200 );
    }
}
