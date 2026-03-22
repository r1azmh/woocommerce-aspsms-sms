<?php
/**
 * Handles communication with the ASPSMS JSON REST API.
 *
 * Base URL:  https://json.aspsms.com/
 * Send SMS:  POST https://json.aspsms.com/SendTextSMS  (application/json)
 * Docs:      https://json.aspsms.com/
 */
defined( 'ABSPATH' ) || exit;

class WC_ASPSMS_API {

    const SEND_URL   = 'https://json.aspsms.com/SendTextSMS';
    const CREDIT_URL = 'https://json.aspsms.com/CheckCredits';

    /**
     * Send an SMS message via the ASPSMS JSON API.
     *
     * @param string $userkey     ASPSMS UserKey  (called "UserName" in the API).
     * @param string $password    ASPSMS API Password.
     * @param string $originator  Sender name (up to 11 alpha chars) or unlocked phone number.
     * @param string $recipient   Recipient phone number in E.164 format (e.g. +358401234567).
     * @param string $message     The SMS body text (UTF-8).
     *
     * @return array {
     *     @type bool   $success  Whether the send was accepted.
     *     @type string $code     StatusCode returned by ASPSMS.
     *     @type string $message  Human-readable outcome.
     * }
     */
    public static function send_sms( $userkey, $password, $originator, $recipient, $message ) {
        $payload = [
            'UserName'    => $userkey,
            'Password'    => $password,
            'Originator'  => $originator,
            'Recipients'  => [ self::format_number( $recipient ) ],
            'MessageText' => $message,
            'ForceGSM7bit'=> false,
        ];

        $response = wp_remote_post(
            self::SEND_URL,
            [
                'timeout'     => 15,
                'headers'     => [ 'Content-Type' => 'application/json' ],
                'body'        => wp_json_encode( $payload ),
                'data_format' => 'body',
            ]
        );

        return self::parse_response( $response );
    }

    /**
     * Check remaining credits — useful for connection testing.
     *
     * @param string $userkey
     * @param string $password
     *
     * @return array {
     *     @type bool   $success
     *     @type string $code
     *     @type string $message
     *     @type string $credits  Remaining credits (on success).
     * }
     */
    public static function check_credits( $userkey, $password ) {
        $payload = [
            'UserName' => $userkey,
            'Password' => $password,
        ];

        $response = wp_remote_post(
            self::CREDIT_URL,
            [
                'timeout'     => 15,
                'headers'     => [ 'Content-Type' => 'application/json' ],
                'body'        => wp_json_encode( $payload ),
                'data_format' => 'body',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'code'    => 'wp_error',
                'message' => $response->get_error_message(),
                'credits' => '',
            ];
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body_raw  = wp_remote_retrieve_body( $response );
        $json      = json_decode( $body_raw, true );

        $status_code = $json['StatusCode'] ?? 'unknown';
        $credits     = $json['Credits']    ?? '';
        $success     = ( $http_code === 200 && $status_code === '1' );

        return [
            'success' => $success,
            'code'    => $status_code,
            'message' => $success
                ? sprintf( 'Connected! Remaining credits: %s', $credits )
                : sprintf( 'ASPSMS error — StatusCode: %s, Info: %s', $status_code, $json['StatusInfo'] ?? '' ),
            'credits' => $credits,
        ];
    }

    /**
     * Parse a wp_remote_post() response into a standard result array.
     *
     * @param WP_Error|array $response
     * @return array
     */
    private static function parse_response( $response ) {
        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'code'    => 'wp_error',
                'message' => $response->get_error_message(),
            ];
        }

        $http_code   = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $json        = json_decode( $body_raw, true );

        // ASPSMS JSON API: StatusCode "1" = success.
        $status_code = $json['StatusCode'] ?? 'unknown';
        $status_info = $json['StatusInfo'] ?? '';
        $success     = ( $http_code === 200 && $status_code === '1' );

        return [
            'success' => $success,
            'code'    => $status_code,
            'message' => $success
                ? 'SMS Notification sent successfully via ASPSMS.'
                : sprintf(
                    'ASPSMS error — StatusCode: %s, Info: %s (HTTP %d)',
                    $status_code,
                    $status_info,
                    $http_code
                ),
        ];
    }

    /**
     * Normalise a phone number to E.164 format (keep leading +, digits only).
     *
     * @param string $number
     * @return string
     */
    private static function format_number( $number ) {
        return preg_replace( '/[^\d+]/', '', $number );
    }
}
