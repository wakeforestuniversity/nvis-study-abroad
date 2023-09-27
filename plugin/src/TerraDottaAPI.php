<?php

namespace InvisibleUs\StudyAbroad;

class TerraDottaAPI {
    private const BASE_PATH = '/piapi/index.cfm?';
    private static $base_url = null;
    private static $default_params = [
        'ResponseEncoding' => 'JSON',
        'CallBack'         => 'false'
    ];

    static function get_base_url() {
        if (!self::$base_url) {
            self::$base_url = 
                'https://' .
                self::get_host() .
                self::BASE_PATH;
        }

        return self::$base_url;
    }

    static function get_host() {
        $option = 'sap_tdhost';
        $host = Plugin::get_option($option);

        if (!$host) {
            if (WP_DEBUG_LOG) {
                $message = __(
                    /* translators: the placeholder is the name of the PHP constant. */
                    'Terra Dotta home URL not set. Either enter in the Settings screen or define %s in wp-config.',
                    'nvis-study-abroad'
                );
    
                error_log(sprintf($message, strtoupper('NVIS_' . $option)));
            }
            
            return false;
        }

        if (strpos($host, 'http') !== false) {
            $host = wp_parse_url($host, PHP_URL_HOST);
        }

        return $host;
    }
    
    static function request(string $callname, array $params = []) {
        $params = array_merge (
            self::$default_params,
            $params
        );

        $params['callName'] = $callname;

        $response = wp_remote_get(
            self::get_base_url() . http_build_query($params),
            ['timeout' => 8]
        );

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new \WP_Error(
                'nvis_unexpected_response_code',
                __('Unexpected response code from server. See error data for full response.', 'nvis-study-abroad'),
                $response
            );
        }

        return $response;
    }

    static function retrieve_columns_to_rows($response) {
        $records = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($records['recordcount'])) {
            $rows = [];
            $columns = explode(',', $records['columnlist']);

            foreach ($columns as $key) {
                for ($i = 0; $i < $records['recordcount']; $i++) {
                    if (!isset($rows[$i])) {
                        $rows[$i] = [];
                    }

                    $rows[$i][$key] = $records['data'][$key][$i];
                }
            }

            return $rows;
        }

        $error = 'Unexpected Terra Dotta JSON format. "recordcount" key not found.';

        if (WP_DEBUG && WP_DEBUG_LOG) {
            error_log($error);
        }

        return new \WP_Error(
            'nvis_malformed_json',
            $error,
            $response
        );
    }

    static function retrieve_json_records($response, $key) {
        $body = json_decode( wp_remote_retrieve_body($response), true);

        if (isset($body[$key])) {
            return $body[$key];
        }
        
        $error = sprintf(
            /* translators: the placeholder is the expected key, or property name. */
            __("Unexpected Terra Dotta JSON format. '%s' key not found."),
            $key
        );

        if (WP_DEBUG && WP_DEBUG_LOG) {
            error_log($error);
        }

        return new \WP_Error(
            'malformed_json',
            $error,
            $response
        );
    }

    static function get_staff() {
        $response = self::request('getStaff');

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return self::retrieve_columns_to_rows($response);
        }

        return $response;
    }

    static function get_programs(array $program_types = []) {
        $program_types = !empty($program_types) ? $program_types : [1,2];
        
        $response = self::request(
            'getPrograms',
            ['ProgramType' => implode(',', $program_types)] 
        );

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return self::retrieve_json_records($response,'PROGRAM');
        }

        return $response;
    }

    static function get_program_brochure(int $program_id) {
        $response = self::request(
            'getProgramBrochure',
            ['program_id' => $program_id] 
        );

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return self::retrieve_json_records($response, 'DETAILS');
        }

        return $response;
    }

    static function get_program_deadlines(int $program_id) {
        $response = self::request(
            'getProgramDeadlines',
            ['program_id' => $program_id] 
        );

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return self::retrieve_columns_to_rows($response);
        }

        return $response;
    }

    static function get_program_parameters() {
        $response = self::request(
            'getProgramSearchElements',
            ['parameters' => true] 
        );

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return self::retrieve_json_records($response, 'ELEMENT');
        }

        return $response;
    }
}
