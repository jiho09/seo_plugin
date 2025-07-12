<?php

namespace Plugin_SEO_Check\Repository;

class ResultRepo {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'seo_audit';
    }

    public function create( $post_id, $score, $issues ) {
        global $wpdb;

        return $wpdb->insert(
            $this->table_name,
            [
                'post_id'    => $post_id,
                'score'      => $score,
                'issues'     => wp_json_encode( $issues ),
                'scanned_at' => current_time( 'mysql' ),
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
            ]
        );
    }

    public function get_by_post_id( $post_id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE post_id = %d ORDER BY scanned_at DESC",
                $post_id
            )
        );
    }

    public function get_all() {
        global $wpdb;
        // Get the latest audit for each post
        $sql = "
            SELECT r1.*, p.post_title
            FROM {$this->table_name} r1
            INNER JOIN (
                SELECT post_id, MAX(scanned_at) as max_scanned_at
                FROM {$this->table_name}
                GROUP BY post_id
            ) r2 ON r1.post_id = r2.post_id AND r1.scanned_at = r2.max_scanned_at
            INNER JOIN {$wpdb->posts} p ON r1.post_id = p.ID
            ORDER BY r1.scanned_at DESC
        ";
        return $wpdb->get_results( $sql );
    }
}
