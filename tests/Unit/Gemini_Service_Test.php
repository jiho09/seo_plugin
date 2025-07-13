<?php

namespace Plugin_SEO_Check\Tests\Unit;

use Plugin_SEO_Check\Service\Gemini_Service;
use PHPUnit\Framework\TestCase;

class Gemini_Service_Test extends TestCase {

    public function test_is_api_key_configured_with_option() {
        update_option( 'wp_seo_check_gemini_api_key', 'test_api_key' );
        $this->assertTrue( Gemini_Service::is_api_key_configured() );
        delete_option( 'wp_seo_check_gemini_api_key' );
    }

    public function test_is_api_key_configured_with_constant() {
        if ( ! defined( 'WP_SEO_CHECK_GEMINI_API_KEY' ) ) {
            define( 'WP_SEO_CHECK_GEMINI_API_KEY', 'test_constant_key' );
        }
        $this->assertTrue( Gemini_Service::is_api_key_configured() );
    }

    public function test_is_api_key_configured_without_key() {
        delete_option( 'wp_seo_check_gemini_api_key' );
        $this->assertFalse( Gemini_Service::is_api_key_configured() );
    }

    public function test_save_api_key_success() {
        $result = Gemini_Service::save_api_key( 'valid_api_key_123' );
        $this->assertNotInstanceOf( \WP_Error::class, $result );
        $this->assertEquals( 'valid_api_key_123', get_option( 'wp_seo_check_gemini_api_key' ) );
    }

    public function test_save_api_key_invalid() {
        $result = Gemini_Service::save_api_key( 'short' );
        $this->assertInstanceOf( \WP_Error::class, $result );
        $this->assertEquals( 'invalid_api_key', $result->get_error_code() );
    }

    public function test_save_api_key_empty() {
        update_option( 'wp_seo_check_gemini_api_key', 'existing_key' );
        $result = Gemini_Service::save_api_key( '' );
        $this->assertNotInstanceOf( \WP_Error::class, $result );
        $this->assertFalse( get_option( 'wp_seo_check_gemini_api_key' ) );
    }

    public function test_constructor_with_api_key() {
        $service = new Gemini_Service( 'test_key' );
        $this->assertInstanceOf( Gemini_Service::class, $service );
    }

    public function test_constructor_without_api_key_throws_exception() {
        delete_option( 'wp_seo_check_gemini_api_key' );
        $this->expectException( \Exception::class );
        new Gemini_Service();
    }
} 