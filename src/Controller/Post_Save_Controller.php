<?php

namespace Plugin_SEO_Check\Controller;

use Plugin_SEO_Check\Service\Scanner_Service;

class Post_Save_Controller {

    private $scanner_service;

    public function __construct() {
        $this->scanner_service = new Scanner_Service();
    }

    public function run_analysis( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // The Scanner_Service now handles the full analysis including performance
        $this->scanner_service->scan_post( $post_id );
    }
}