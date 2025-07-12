<?php

namespace Plugin_SEO_Check\Core;

class Activator {
    public static function activate() {
        global $wpdb;
        $audit_table_name = $wpdb->prefix . 'seo_audit';
        $metrics_table_name = $wpdb->prefix . 'seo_performance_metrics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql_audit = "CREATE TABLE $audit_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            score tinyint(3) UNSIGNED NOT NULL,
            issues longtext NOT NULL,
            scanned_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        $sql_metrics = "CREATE TABLE $metrics_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            metric_name varchar(50) NOT NULL,
            metric_value float NOT NULL,
            recorded_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY metric_name (metric_name)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_audit );
        dbDelta( $sql_metrics );
    }
}