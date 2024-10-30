<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The service for making requests to the Joint Ventures Comments API
 *
 * @link       https://jointcomments.com
 * @since      1.0.0
 *
 * @package    JvComments
 * @subpackage JvComments/includes
 */

class JvComments_API_Service {

    const AUTH_ENDPOINT = 'https://id.jointcomments.com';
    const API_ENDPOINT = 'https://wp.jointcomments.com';
    const CLIENT_ID = 'wpClient';
    const CLIENT_SECRET = 'm9SsyRbu?7Xf}eZ5';

    /**
     * JV Comments User Registration
     *
     * @since   1.0.0
     * @return  mixed
     */
    public function jv_comments_register($params) {
        $endpoint = JvComments_API_Service::AUTH_ENDPOINT . '/Membership/RegisterProductUser';

        // SET Client ID
        $params['clientid'] = JvComments_API_Service::CLIENT_ID;

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($params),
        ) );

        return $response;
    }

    public function jv_comments_login($username, $password) {
        $endpoint = JvComments_API_Service::AUTH_ENDPOINT . '/connect/token';

        $params = array(
            'username' => $username,
            'password' => $password,
            'client_id' => JvComments_API_Service::CLIENT_ID,
            'client_secret' => JvComments_API_Service::CLIENT_SECRET,
            'grant_type' => 'password',
        );

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => http_build_query($params),
        ) );

        return $this->get_response_body( $response );
    }

    public function jv_comments_get_sites($token) {
        $endpoint = JvComments_API_Service::API_ENDPOINT . '/GetWebsites';

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
        ) );

        $response = $this->get_response_body($response);

        $sites = [];
        if ($response) {
            $sites = array_map(function($site) {
                return array(
                    'websiteId' => $site->websiteID,
                    'name' => $site->name,
                    'url' => $site->url,
                );
            }, $response->data);
        }

        return $sites;
    }

    public function jv_comments_add_site($token, $params) {
        $endpoint = JvComments_API_Service::API_ENDPOINT . '/AddWebsite';

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($params),
        ) );

        return $this->get_response_body($response);
    }

    public function jv_comments_update_site_settings($token, $params) {
        $endpoint = JvComments_API_Service::API_ENDPOINT . '/UpdateWebsiteSettings';

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json-patch+json',
            ),
            'body' => json_encode($params),
        ) );

        return $this->get_response_body( $response );
    }

    public function jv_comments_get_site_settings($id, $token) {
        $endpoint = JvComments_API_Service::API_ENDPOINT . '/GetWebsiteSettings?id=' . $id;

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json-patch+json',
            ),
        ) );

        $response = $this->get_response_body( $response );

        return $response->data;
    }

	/**
	 * Checks the type of response and returns and object variable with the JVComments response.
	 *
	 * @since     1.0.0
	 * @param     WP_Error|array $response    The remote response.
	 * @return    mixed                       The response body in JVComments API format.
	 */
	private function get_response_body( $response ) {
        if ($response['response'] && $response['response']['code'] !== 200) {
            $response = json_decode($response['body'], true);

            if (isset($response['errors'])) {
                return array(  'errors' => $response['errors'] );
            } else if (isset($response['error'])) {
                return array (
                    'error' => $response['error'],
                    'error_description' => $response['error_description'],
                );
            } else {
                return array ( 'error' => 'unknown' );
            }
        }

        return json_decode( $response['body'] );
	}
}
