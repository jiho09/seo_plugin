<?php

namespace Plugin_SEO_Check\Core;

use Plugin_SEO_Check\Admin\Page_Controller;
use Plugin_SEO_Check\Controller\Api_Controller;
use Plugin_SEO_Check\Controller\Post_Save_Controller;
use Plugin_SEO_Check\Controller\Public_Controller;
use Plugin_SEO_Check\Controller\Editor_Controller;

class Loader {

    public function run() {
        $this->define_admin_hooks();
        $this->define_post_hooks();
        $this->define_api_hooks();
        $this->define_public_hooks();
        $this->define_editor_hooks();
    }

    private function define_admin_hooks() {
        new Page_Controller();
    }

    private function define_post_hooks() {
        $post_save_controller = new Post_Save_Controller();
        add_action( 'save_post', [ $post_save_controller, 'run_analysis' ], 10, 2 );
    }

    private function define_api_hooks() {
        new Api_Controller();
    }

    private function define_public_hooks() {
        new Public_Controller();
    }

    private function define_editor_hooks() {
        new Editor_Controller();
    }
}

