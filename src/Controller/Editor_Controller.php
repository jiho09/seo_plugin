<?php

namespace Plugin_SEO_Check\Controller;

class Editor_Controller {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_seo_check_meta_box' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_classic_editor_assets' ] );
    }

    public function add_seo_check_meta_box() {
        add_meta_box(
            'wp-seo-check-meta-box',
            __( 'SEO Check', 'wp-seo-check' ),
            [ $this, 'render_meta_box_content' ],
            [ 'post', 'page' ], // Post types to show the meta box on
            'side',
            'high'
        );
    }

    public function render_meta_box_content( $post ) {
        // This div will be the mount point for our React app in Classic Editor
        echo '<div id="wp-seo-check-editor-app" data-post-id="' . esc_attr( $post->ID ) . '"></div>';
    }

    public function enqueue_block_editor_assets() {
        // Enqueue scripts for Gutenberg (Block Editor)
        $this->enqueue_editor_scripts( 'block' );
    }

    public function enqueue_classic_editor_assets( $hook ) {
        // Enqueue scripts for Classic Editor
        if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
            $this->enqueue_editor_scripts( 'classic' );
        }
    }

    private function enqueue_editor_scripts( $editor_type ) {
        $asset_file = plugins_url( 'dist/editor.js', WPSEOCHK_PLUGIN_FILE );
        $asset_dep = []; // Dependencies will be added by Vite

        wp_enqueue_script(
            'wp-seo-check-editor-script',
            plugins_url( 'dist/editor.js', WPSEOCHK_PLUGIN_FILE ),
            $asset_dep,
            null, // Version
            true // In footer
        );

        wp_localize_script(
            'wp-seo-check-editor-script',
            'wpSeoCheckEditor',
            [
                'postId' => get_the_ID(),
                'editorType' => $editor_type,
                'restNonce' => wp_create_nonce( 'wp_rest' ),
            ]
        );
    }
}