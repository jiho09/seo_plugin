<?php

namespace Plugin_SEO_Check\Scanner;

class HtmlScanner {

    private $dom;
    private $xpath;

    public function __construct( $html ) {
        $this->dom = new \DOMDocument();
        // Suppress warnings from invalid HTML
        @$this->dom->loadHTML( $html );
        $this->xpath = new \DOMXPath( $this->dom );
    }

    public function get_title() {
        $title_node = $this->xpath->query( '//title' )->item(0);
        return $title_node ? $title_node->textContent : '';
    }

    public function get_meta_description() {
        $meta_node = $this->xpath->query( "//meta[@name='description']" )->item(0);
        return $meta_node ? $meta_node->getAttribute('content') : '';
    }

    public function get_h1_count() {
        return $this->xpath->query( '//h1' )->length;
    }
}
