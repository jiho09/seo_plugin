<?php

namespace Plugin_SEO_Check\Analyzer;

use Plugin_SEO_Check\Repository\Performance_Metrics_Repo;

class Performance_Analyzer {

    private $metrics_repo;

    public function __construct() {
        $this->metrics_repo = new Performance_Metrics_Repo();
    }

    public function analyze( $post_id ) {
        $metrics = $this->metrics_repo->get_latest_for_post( $post_id );
        $performance_results = [];

        foreach ( $metrics as $metric ) {
            $performance_results[ $metric->metric_name ] = $metric->metric_value;
        }

        // Example scoring logic (simplified)
        $score = 100;

        if ( isset( $performance_results['LCP'] ) && $performance_results['LCP'] > 2500 ) {
            $score -= 20; // Penalize for high LCP
        }
        if ( isset( $performance_results['CLS'] ) && $performance_results['CLS'] > 0.1 ) {
            $score -= 20; // Penalize for high CLS
        }
        if ( isset( $performance_results['TTFB'] ) && $performance_results['TTFB'] > 600 ) {
            $score -= 10; // Penalize for high TTFB
        }

        return max( 0, $score ); // Ensure score is not negative
    }
}
