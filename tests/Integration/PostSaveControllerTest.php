<?php

namespace Plugin_SEO_Check\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Plugin_SEO_Check\Controller\Post_Save_Controller;
use Plugin_SEO_Check\Service\Scanner_Service;

class PostSaveControllerTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        
        // Reset Brain/Monkey for each test
        \Brain\Monkey\tearDown();

        // Stub WordPress functions before Brain/Monkey setup
        Functions\stubs([
            'defined',
            'DOING_AUTOSAVE',
            'current_user_can',
            'get_permalink',
            'get_post_status',
            'wp_remote_get',
            'is_wp_error',
            'wp_remote_retrieve_body',
            'update_post_meta',
            'get_posts',
            'get_post',
            'wp_json_encode',
            'wp_create_nonce',
            'plugins_url',
            'wp_enqueue_script',
            'wp_localize_script',
            'add_action',
            'add_meta_box',
            'get_the_ID',
            'esc_attr',
            '__',
            'url_to_postid',
            'current_time',
            'dbDelta',
        ]);

        \Brain\Monkey\setUp();

        // Mock WordPress functions after Brain/Monkey setup
        Functions\when( 'defined' )->justReturn( true );
        Functions\when( 'DOING_AUTOSAVE' )->justReturn( false );
        Functions\when( 'current_user_can' )->justReturn( true );
        Functions\when( 'get_permalink' )->justReturn( 'http://example.com/test-post' );
        Functions\when( 'get_post_status' )->justReturn( 'publish' );
        Functions\when( 'wp_remote_get' )->justReturn( [
            'body' => '<html><head><title>Test Title</title></head><body><h1>Test Content</h1></body></html>',
            'response' => ['code' => 200, 'message' => 'OK']
        ] );
        Functions\when( 'is_wp_error' )->justReturn( false );
        Functions\when( 'wp_remote_retrieve_body' )->justReturn( '<html><head><title>Test Title</title></head><body><h1>Test Content</h1></body></html>' );
        Functions\when( 'update_post_meta' )->justReturn( true );
        Functions\when( 'get_posts' )->justReturn( [] ); // For Api_Controller scan_all_posts
        Functions\when( 'get_post' )->justReturn( (object)['ID' => 1, 'post_title' => 'Test Post', 'post_content' => 'This is test content.'] );
        Functions\when( 'wp_json_encode' )->justReturn( '{}' ); // Simplified for testing
        Functions\when( 'wp_create_nonce' )->justReturn( 'test_nonce' );
        Functions\when( 'plugins_url' )->justReturn( 'http://example.com/wp-content/plugins/wp-seo-check/dist/editor.js' );
        Functions\when( 'wp_enqueue_script' )->justReturn( true );
        Functions\when( 'wp_localize_script' )->justReturn( true );
        Functions\when( 'add_action' )->justReturn( true );
        Functions\when( 'add_meta_box' )->justReturn( true );
        Functions\when( 'get_the_ID' )->justReturn( 1 );
        Functions\when( 'esc_attr' )->justReturn( 'test_attr' ); // Simplified for testing
        Functions\when( '__' )->justReturn( 'test_text' ); // Simplified for testing
        Functions\when( 'url_to_postid' )->justReturn( 1 );
        Functions\when( 'current_time' )->justReturn( '2025-07-11 10:00:00' );
        Functions\when( 'dbDelta' )->justReturn( true );

        // Mock $wpdb
        global $wpdb;
        $wpdb = $this->getMockBuilder('wpdb')
                     ->disableOriginalConstructor()
                     ->getMock();
        $wpdb->prefix = 'wp_';
        $wpdb->method('insert')->willReturn(1);
        $wpdb->method('get_row')->willReturn((object)['id' => 1, 'post_id' => 1, 'score' => 80, 'issues' => '{}', 'scanned_at' => '2025-07-11 10:00:00']);
        $wpdb->method('get_results')->willReturn([]);
        $wpdb->method('prepare')->willReturnArgument(0);
    }

    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_run_analysis_calls_scanner_service() {
        $post_id = 123;
        $post = (object) ['ID' => $post_id, 'post_status' => 'publish'];

        $scannerServiceMock = $this->getMockBuilder(Scanner_Service::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        
        $scannerServiceMock->expects($this->once())
                             ->method('scan_post')
                             ->with($post_id);

        // Use reflection to inject the mock into the controller
        $controller = new Post_Save_Controller();
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('scanner_service');
        $property->setAccessible(true);
        $property->setValue($controller, $scannerServiceMock);

        $controller->run_analysis($post_id, $post);
    }

    public function test_run_analysis_does_not_run_on_autosave() {
        Functions\when( 'DOING_AUTOSAVE' )->justReturn( true );

        $scannerServiceMock = $this->getMockBuilder(Scanner_Service::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        
        $scannerServiceMock->expects($this->never())
                             ->method('scan_post');

        $controller = new Post_Save_Controller();
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('scanner_service');
        $property->setAccessible(true);
        $property->setValue($controller, $scannerServiceMock);

        $post_id = 123;
        $post = (object) ['ID' => $post_id, 'post_status' => 'publish'];
        $controller->run_analysis($post_id, $post);
    }

    public function test_run_analysis_does_not_run_if_user_cannot_edit() {
        Functions\when( 'current_user_can' )->justReturn( false );

        $scannerServiceMock = $this->getMockBuilder(Scanner_Service::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        
        $scannerServiceMock->expects($this->never())
                             ->method('scan_post');

        $controller = new Post_Save_Controller();
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('scanner_service');
        $property->setAccessible(true);
        $property->setValue($controller, $scannerServiceMock);

        $post_id = 123;
        $post = (object) ['ID' => $post_id, 'post_status' => 'publish'];
        $controller->run_analysis($post_id, $post);
    }
}
