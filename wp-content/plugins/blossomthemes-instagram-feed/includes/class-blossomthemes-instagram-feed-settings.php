<?php
/**
 * Settings section of the plugin.
 *
 * Maintain a list of functions that are used for settings purposes of the plugin
 *
 * @package    BlossomThemes_Instagram_Feed
 * @subpackage BlossomThemes_Instagram_Feed/includes
 * @author    blossomthemes
 */
class BlossomThemes_Instagram_Feed_Settings {
    /**
     * Instagram 0auth URL.
     * @var string $auth_url Instagram Oauth URL with callback.
     */
    private $oauth_url = 'https://instagram.com/oauth/authorize/?client_id=8dc488eba3d54eb9806eb27eabb8cd03&response_type=token&redirect_uri=https://blossomthemes.com/instagram/';
    /**
     * Map of old instagram image size to new image size.
     * 
     * @var array $photo_sizes Map of old instagram image to new image size.
     */
    private  $photo_sizes = array(
        'img_thumb'    => 'thumbnail',
        'img_low'      => 'low_resolution', 
        'img_standard' => 'standard_resolution' 
    );

    public function __construct() {
        add_action( 'btif_settings_general_tab_content', array( $this, 'btif_settings_general_tab_content' ) );
        add_action( 'btif_settings_usage_tab_content', array( $this, 'btif_settings_usage_tab_content' ) );
        add_action( 'btif_settings_sidebar', array( $this, 'btif_settings_sidebar' ) );
    }

    /**
     * Include settings sidebar.
     */
    public function btif_settings_sidebar() {
        require_once BTIF_BASE_PATH . '/includes/template/backend/sidebar.php';      
    }

    /**
     * Include the settings usage tab content template.
     */
    public function btif_settings_usage_tab_content() {
        require_once BTIF_BASE_PATH . '/includes/template/backend/usage.php';      
    }

    /**
     * Include the settings general tab content template.
     */
    public function btif_settings_general_tab_content() {
        // $oauth_url = 'https://instagram.com/oauth/authorize/?client_id=291228964f8746a9933b6a51c6dcb750&response_type=token&redirect_uri=http://localhost/blossom/wordpress/blossom-insta-check/';

        // Authorize URL with client site redirect link
        $oauth_url = $this->oauth_url; 
        $oauth_url .= '?auth_site=' . esc_url( admin_url( 'admin.php?page=class-blossomthemes-instagram-feed-admin.php' ) );
        $oauth_url.='&hl=en';

        $options = get_option( 'blossomthemes_instagram_feed_settings' );
        $photo_size = isset( $options['photo_size'] ) ? esc_attr( $options['photo_size'] ) : 'low_resolution';

        // Changed to the new instagram image size.
        if ( array_key_exists( $photo_size, $this->photo_sizes ) ) {
            $photo_size = $this->photo_sizes[ $photo_size ];
        }
    
        // Override options Access Token if received new access token by POST method
        if ( isset( $_POST['access_token'] ) ) {
            $options['access-token'] = $_POST['access_token'];
        }

        // Prepare array to be passed to the template.
        $args = array(
            'oauth_url'     => $oauth_url,
            'access_token'  => isset( $options['access-token'] ) ? $options['access-token'] : '',
            'username'      => isset( $options['username'] ) ? $options['username'] : '',
            'photos'        => isset( $options['photos'] ) ? $options['photos'] : 5,
            'size'          => $photo_size,
            'photos_row'    => isset( $options['photos_row'] ) ? $options['photos_row'] : 5,
            'follow_me'     => isset( $options['follow_me'] ) ? $options['follow_me'] : '',
            'pull_duration' => isset( $options['pull_duration'] ) ? $options['pull_duration'] : 1,
            'pull_unit'     => isset( $options['pull_unit'] ) ? $options['pull_unit'] : 'days',
            'meta'          => isset( $options['meta'] ) ? $options['meta'] : true,
        );

        // Extrat the variables from the array.
        extract( $args );

        require_once BTIF_BASE_PATH . '/includes/template/backend/general.php';      
    }

    function blossomthemes_instagram_feed_backend_settings() {
        require_once BTIF_BASE_PATH . '/includes/template/backend/settings.php';      
    }
}
new BlossomThemes_Instagram_Feed_Settings;
