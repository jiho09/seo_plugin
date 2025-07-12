<?php

namespace Plugin_SEO_Check\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Plugin_SEO_Check\Scanner\HtmlScanner;

class HtmlScannerTest extends TestCase {

    public function test_get_title() {
        $html = '<html><head><title>Test Title</title></head><body></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals('Test Title', $scanner->get_title());
    }

    public function test_get_title_no_title() {
        $html = '<html><head></head><body></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals('', $scanner->get_title());
    }

    public function test_get_meta_description() {
        $html = '<html><head><meta name="description" content="Test Description"></head><body></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals('Test Description', $scanner->get_meta_description());
    }

    public function test_get_meta_description_no_description() {
        $html = '<html><head></head><body></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals('', $scanner->get_meta_description());
    }

    public function test_get_h1_count_one() {
        $html = '<html><body><h1>Heading 1</h1></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals(1, $scanner->get_h1_count());
    }

    public function test_get_h1_count_multiple() {
        $html = '<html><body><h1>Heading 1</h1><h1>Heading 2</h1></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals(2, $scanner->get_h1_count());
    }

    public function test_get_h1_count_zero() {
        $html = '<html><body><h2>Heading 2</h2></body></html>';
        $scanner = new HtmlScanner($html);
        $this->assertEquals(0, $scanner->get_h1_count());
    }
}
