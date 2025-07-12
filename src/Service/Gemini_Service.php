<?php

namespace Plugin_SEO_Check\Service;

class Gemini_Service {

    private $api_key;
    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct( $api_key ) {
        $this->api_key = $api_key;
    }

    public function generate_meta_tags( $post_title, $post_content ) {
        $prompt = "Generate an optimized SEO title (50-60 chars) and meta description (120-160 chars) for the following WordPress post.\n\nTitle: {$post_title}\nContent: {$post_content}\n\nFormat the output as a JSON object with 'title' and 'description' keys.";

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,
            ],
            'body'    => wp_json_encode( $body ),
            'method'  => 'POST',
            'timeout' => 30,
        ];

        $response = wp_remote_post( $this->api_url, $args );

        if ( is_wp_error( $response ) ) {
            return new \WP_Error( 'gemini_api_error', $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            $text_response = $data['candidates'][0]['content']['parts'][0]['text'];
            // Attempt to parse JSON from the text response
            $json_start = strpos($text_response, '{');
            $json_end = strrpos($text_response, '}');
            if ($json_start !== false && $json_end !== false) {
                $json_string = substr($text_response, $json_start, $json_end - $json_start + 1);
                $parsed_json = json_decode($json_string, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $parsed_json;
                }
            }
            // Fallback if JSON parsing fails, return raw text
            return ['title' => '', 'description' => $text_response];
        }

        return new \WP_Error( 'gemini_api_error', 'Unexpected API response', $data );
    }
}
