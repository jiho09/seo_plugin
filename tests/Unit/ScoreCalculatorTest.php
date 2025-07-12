<?php

namespace Plugin_SEO_Check\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Plugin_SEO_Check\Analyzer\ScoreCalculator;

class ScoreCalculatorTest extends TestCase {

    public function test_calculate_with_all_passed() {
        $calculator = new ScoreCalculator();
        $results = ['rule1' => true, 'rule2' => true, 'rule3' => true];
        $performance_score = 100;
        $this->assertEquals(100, $calculator->calculate($results, $performance_score));
    }

    public function test_calculate_with_some_passed() {
        $calculator = new ScoreCalculator();
        $results = ['rule1' => true, 'rule2' => false, 'rule3' => true];
        $performance_score = 66;
        $this->assertEquals(66, $calculator->calculate($results, $performance_score));
    }

    public function test_calculate_with_all_failed() {
        $calculator = new ScoreCalculator();
        $results = ['rule1' => false, 'rule2' => false, 'rule3' => false];
        $performance_score = 0;
        $this->assertEquals(0, $calculator->calculate($results, $performance_score));
    }

    public function test_calculate_with_empty_results() {
        $calculator = new ScoreCalculator();
        $results = [];
        $performance_score = 100;
        $this->assertEquals(100, $calculator->calculate($results, $performance_score));
    }
}
