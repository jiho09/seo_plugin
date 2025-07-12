<?php

namespace Plugin_SEO_Check\Service;

use Plugin_SEO_Check\Scanner\HtmlScanner;
use Plugin_SEO_Check\Analyzer\Seo_Analyzer;
use Plugin_SEO_Check\Analyzer\Performance_Analyzer;
use Plugin_SEO_Check\Analyzer\ScoreCalculator;
use Plugin_SEO_Check\Repository\ResultRepo;

class Scanner_Service {

    public function scan_post( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || $post->post_status !== 'publish' ) {
            return false;
        }

        // Get HTML content internally instead of wp_remote_get
        $html = apply_filters( 'the_content', $post->post_content );
        $html = '<html lang="' . get_bloginfo( 'language' ) . '"><head><title>' . get_the_title( $post_id ) . '</title></head><body>' . $html . '</body></html>';

        if ( empty( $html ) ) {
            return false;
        }

        // SEO Analysis
        $scanner = new HtmlScanner( $html );
        $seo_analyzer = new Seo_Analyzer( $scanner );
        $seo_results = $seo_analyzer->analyze();

        // Performance Analysis
        $performance_analyzer = new Performance_Analyzer();
        $performance_score = $performance_analyzer->analyze( $post_id );

        // Combine results and calculate final score using ScoreCalculator
        $calculator = new ScoreCalculator();
        $final_score = $calculator->calculate( $seo_results, $performance_score );

        $combined_results = array_merge( $seo_results, [ 'performance_score' => $performance_score ] );

        $repo = new ResultRepo();
        $repo->create( $post_id, (int) $final_score, $combined_results );

        return true;
    }
}