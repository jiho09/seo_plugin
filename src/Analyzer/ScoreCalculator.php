<?php

namespace Plugin_SEO_Check\Analyzer;

class ScoreCalculator {

    public function calculate( array $seo_analysis_results, int $performance_score ) {
        $total_checks = count( $seo_analysis_results );
        if ( $total_checks === 0 ) {
            $seo_percentage = 100;
        } else {
            $passed_checks = count( array_filter( $seo_analysis_results ) );
            $seo_percentage = ( $passed_checks / $total_checks ) * 100;
        }

        $final_score = ( $seo_percentage + $performance_score ) / 2;

        return (int) $final_score;
    }
}