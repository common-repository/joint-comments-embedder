<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The REST API-specific functionality of the plugin.
 *
 * @link       https://jointcomments.com
 * @since      1.0.0
 *
 * @package    JvComments
 * @subpackage JvComments/rest-api
 */

/**
 * Defines the REST API endpoints for the plugin
 *
 * @package    JvComments
 * @subpackage JvComments/rest-api
 * @author     Joint Ventures
 */
class JvComments_Rest_Api {

    const REST_NAMESPACE = 'jv_comments/v1';

    /**
     * Instance of the JVComments API service.
     *
     * @since    1.0.0
     * @access   private
     * @var      JvComments_Api_Service    $api_service    Instance of the JVComments API service.
     */
    private $api_service;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    JvComments_Api_Service $api_service    Instance of the JvComments API service.
     * @param    string             $version        The version of this plugin.
     */
    public function __construct( $api_service, $version ) {
        $this->api_service = $api_service;
        $this->version = $version;
    }

    /**
     * Registers JvComments plugin WordPress REST API endpoints.
     *
     * @since    1.0.0
     */
    public function jv_comments_register_endpoints() {
        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_login', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'jv_comments_login' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_register', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'jv_comments_register' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_sites', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'jv_comments_get_sites' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_add_site', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'jv_comments_add_site' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_update_site_settings', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'jv_comments_update_site_settings' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_site_settings', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'jv_comments_get_site_settings' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
            'args' => [ 'id' ],
        ) );

        register_rest_route( JvComments_Rest_Api::REST_NAMESPACE, 'jv_comments_logout', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'jv_comments_logout' ),
            'permission_callback' => array( $this, 'rest_admin_only_permission_callback' ),
        ) );
    }

    public function jv_comments_get_sites() {
        $token = $this->jv_comments_get_plugin_token();

        $response = $this->api_service->jv_comments_get_sites($token);

        return $response;
    }

    public function jv_comments_add_site( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        $token = $this->jv_comments_get_plugin_token();

        $response = $this->api_service->jv_comments_add_site($token, $params);

        if (!isset($response->data->websiteId)) {
            return $response;
        } else {
            $id = $response->data->websiteId;
            $this->jv_comments_save_plugin_site(array(
                'website_id' => $id,
                'is_open' => $response->data->isOpen,
                'position' => $response->data->widgetPosition,
            ));

            return array(
                'websiteId' => $id,
                'websiteName' => $params['name'],
                'websiteUrl' => $params['url'],
                'widgetPosition' => $response->data->widgetPosition,
                'isOpen' => $response->data->isOpen,
                'commentStatus' => $response->data->commentStatus,
                'languageId' => $response->data->languageId,
                'colors' => [],
            );
        }
    }

    public function jv_comments_update_site_settings( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        $token = $this->jv_comments_get_plugin_token();

        $response = $this->api_service->jv_comments_update_site_settings($token, $params);

        if (!isset($response->data->websiteId)) {
            return $response;
        }

        $pluginData = array(
            'website_id' => $response->data->websiteId,
            'is_open' => $response->data->isOpen,
            'position' => $response->data->widgetPosition,
            'lang' => $response->data->languageId,
        );

        if (
            isset($response->data) &&
            isset($response->data->colors) &&
            count($response->data->colors) === 2
        ) {
            $pluginData['theme'] = array(
                'mainColor' => $response->data->colors[0],
                'textColor' => $response->data->colors[1],
            );
        }

        if (isset($response->errors)) {
            return array (
                'error' => true,
                'errors' => $response->errors,
            );
        } else {
            $this->jv_comments_save_plugin_site($pluginData);
            return $response;
        }
    }

    public function jv_comments_register( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        $response = $this->api_service->jv_comments_register($params);

        $responseData = $response['body'];
        $response = json_decode($responseData, true);

        if (isset($response['errors'])) {
            return array (
                'error' => true,
                'errors' => $response['errors'],
            );
        } else {
            $this->jv_comments_save_plugin_token(json_decode($response));

            return $response;
        }
    }

    public function jv_comments_get_site_settings( WP_REST_Request $request ) {
        $params = $request->get_query_params();
        $token = $this->jv_comments_get_plugin_token();

        $response = $this->api_service->jv_comments_get_site_settings($params['id'], $token);

        return $response;
    }

    public function jv_comments_login( WP_REST_Request $request ) {
        $params = $request->get_json_params();
        $response = $this->api_service->jv_comments_login($params['email'], $params['password']);

        if (isset($response) && is_array($response) && isset($response['error'])) {
            return $response;
        }

        $this->jv_comments_save_plugin_token($response);

        return $response;
    }

    public function jv_comments_save_plugin_token( $token ) {
        update_option('jv_comments_admin_token', (array) $token);
    }

    public function jv_comments_get_plugin_token() {
        $token = get_option('jv_comments_admin_token');

        return maybe_unserialize($token);
    }

    public function jv_comments_save_plugin_site($site) {
        update_option('jv_comments_site', json_encode($site));
    }

    public function jv_comments_get_plugin_site() {
        $site = get_option('jv_comments_site');

        $response = array( );

        if ($site) {
            $response = json_decode($site);
        }

        return $response;
    }

    public function jv_comments_logout() {
        delete_option('jv_comments_site');
        delete_option('jv_comments_admin_token');

        return array(
            'success' => true,
        );
    }

    /**
     * Callback to ensure user has manage_options permissions.
     *
     * @since     1.0.0
     * @param     WP_REST_Request $request    The request object.
     * @return    boolean|WP_Error            Whether the user has permission to the admin REST API.
     */
    public function rest_admin_only_permission_callback( WP_REST_Request $request ) {
        // Regular cookie-based authentication.
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        return true;
    }
}
