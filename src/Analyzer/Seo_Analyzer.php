<?php

namespace Plugin_SEO_Check\Analyzer;

use Plugin_SEO_Check\Scanner\HtmlScanner;

class Seo_Analyzer {

    private $scanner;

    public function __construct( HtmlScanner $scanner ) {
        $this->scanner = $scanner;
    }

    public function analyze() {
        $results = [];

        $results['title_length'] = $this->check_title_length();
        $results['meta_description_length'] = $this->check_meta_description_length();
        $results['h1_count'] = $this->check_h1_count();

        return $results;
    }

    private function check_title_length() {
        $title = (string) $this->scanner->get_title(); // Cast to string to handle null
        $length = mb_strlen( $title );
        return ( $length >= 50 && $length <= 60 );
    }

    private function check_meta_description_length() {
        $description = (string) $this->scanner->get_meta_description(); // Cast to string to handle null
        $length = mb_strlen( $description );
        return ( $length >= 120 && $length <= 160 );
    }

    private function check_h1_count() {
        return $this->scanner->get_h1_count() === 1;
    }
}