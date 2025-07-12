<?php

namespace Plugin_SEO_Check\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Plugin_SEO_Check\Analyzer\Seo_Analyzer;
use Plugin_SEO_Check\Scanner\HtmlScanner;

class SeoAnalyzerTest extends TestCase {

    public function test_title_length_valid() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_title')->willReturn('This is a perfectly sized title for SEO, exactly 55 chars.'); // 55 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertTrue($results['title_length']);
    }

    public function test_title_length_too_short() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_title')->willReturn('Short title'); // 12 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['title_length']);
    }

    public function test_title_length_too_long() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_title')->willReturn('This is an excessively long title that goes beyond the recommended character limit for optimal SEO visibility in search engine results pages.'); // 130 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['title_length']);
    }

    public function test_meta_description_length_valid() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_meta_description')->willReturn('This is a meta description that falls within the recommended character limits for search engines, providing a concise summary of the page content.'); // 120 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertTrue($results['meta_description_length']);
    }

    public function test_meta_description_length_too_short() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_meta_description')->willReturn('Short description.'); // 18 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['meta_description_length']);
    }

    public function test_meta_description_length_too_long() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_meta_description')->willReturn('This is an extremely long meta description that far exceeds the recommended character count, which could lead to it being truncated in search results and negatively impact click-through rates.'); // 200 chars

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['meta_description_length']);
    }

    public function test_h1_count_one() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_h1_count')->willReturn(1);

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertTrue($results['h1_count']);
    }

    public function test_h1_count_zero() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_h1_count')->willReturn(0);

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['h1_count']);
    }

    public function test_h1_count_multiple() {
        $mockScanner = $this->createMock(HtmlScanner::class);
        $mockScanner->method('get_h1_count')->willReturn(2);

        $analyzer = new Seo_Analyzer($mockScanner);
        $results = $analyzer->analyze();

        $this->assertFalse($results['h1_count']);
    }
}