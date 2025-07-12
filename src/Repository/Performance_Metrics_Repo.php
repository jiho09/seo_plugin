<?php

namespace Plugin_SEO_Check\Repository;

class Performance_Metrics_Repo {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'seo_performance_metrics';
    }

    public function create( $post_id, $metric_name, $metric_value ) {
        global $wpdb;

        return $wpdb->insert(
            $this->table_name,
            [
                'post_id'     => $post_id,
                'metric_name' => $metric_name,
                'metric_value' => $metric_value,
                'recorded_at' => current_time( 'mysql' ),
            ],
            [
                '%d',
                '%s',
                '%f',
                '%s',
            ]
        );
    }

    public function get_latest_for_post( $post_id ) {
        global $wpdb;
        // Get the latest metric for each metric_name for a given post_id
        $sql = "
            SELECT pm1.metric_name, pm1.metric_value
            FROM {$this->table_name} pm1
            INNER JOIN (
                SELECT metric_name, MAX(recorded_at) as max_recorded_at
                FROM {$this->table_name}
                WHERE post_id = %d
                GROUP BY metric_name
            ) pm2 ON pm1.metric_name = pm2.metric_name AND pm1.recorded_at = pm2.max_recorded_at
            WHERE pm1.post_id = %d
        ";
        return $wpdb->get_results( $wpdb->prepare( $sql, $post_id, $post_id ) );
    }
}