<?php

namespace Plugin_SEO_Check\Controller;

class Public_Controller {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts() {
        // Enqueue the performance collector script only on single posts/pages
        if ( is_singular() ) {
            global $post;
            $post_id = $post->ID;

            wp_enqueue_script(
                'wp-seo-check-performance-collector',
                plugins_url( 'dist/performance-collector.js', WPSEOCHK_PLUGIN_FILE ),
                [],
                null,
                true
            );

            wp_localize_script(
                'wp-seo-check-performance-collector',
                'wpSeoCheckPerformance',
                [
                    'postId' => $post_id,
                ]
            );
        }
    }
}