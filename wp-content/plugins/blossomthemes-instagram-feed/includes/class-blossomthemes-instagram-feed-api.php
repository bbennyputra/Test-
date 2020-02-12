<?php

class Blossomthemes_Instagram_Feed_API {
    /**
     * @var Blossomthemes_Instagram_Feed_API The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Instagram Access Token
     *
     * @var string
     */
    protected $access_token;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Blossomthemes_Instagram_Feed_API The *Singleton* instance.
     */
    public static function getInstance()
    {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        $options             = get_option( 'blossomthemes_instagram_feed_settings' );
        $this->access_token  = !empty( $options['access-token'] ) ? $options['access-token'] : '';
        $this->username      = !empty( $options['username'] ) ? $options['username'] : '';
        $this->pull_duration = !empty( $options['pull_duration'] ) ? $options['pull_duration'] : 1;
        $this->pull_unit     = !empty( $options['pull_unit'] ) ? $options['pull_unit'] : 'days';

    }

    /**
     * Calculate posts fetch interval.
     */
    function get_transient_lifetime() {

        $values = array( 'minutes' => MINUTE_IN_SECONDS, 'hours' => HOUR_IN_SECONDS, 'days' => DAY_IN_SECONDS );
        $keys   = array_keys( $values );
        $type   = in_array( $this->pull_unit, $keys ) ? $values[ $this->pull_unit ] : $values['minutes'];

        return $type * $this->pull_duration;
    }

    /**
     * @param int    $image_limit Number of images to retrieve
     * @param string $photo_size  Desired image size to retrieve
     *
     * @return array|bool Array of tweets or false if method fails
     */
    public function get_items( $image_limit, $photo_size ) {
        // Check if the user feed is already fetched.
        $fetch_already = get_transient( 'blossomthemes_instagram_data_fetch' );
        $feed          = get_option( 'blossomthemes_instagram_user_feed' );
        
        // Return data from cache if the images are already fetched and data is in the cache.
        if ( $fetch_already && ! empty( $feed )) {
            return $this->processing_response_data( $feed, $photo_size, $image_limit );
        }

        // Get the new images if the images are not fetched.
        $response = wp_remote_get( sprintf( BTIF_INSTAGRAM_API_MEDIA_ACCESS_URL, $this->access_token, BTIF_INSTAGRAM_API_IMAGE_LIMIT ) );

        // Return the images from cache if new images cannot be fetched.
        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            update_option( 'blossomthemes_instagram_invalid_token', true, false );
            return $this->processing_response_data( $feed, $photo_size, $image_limit );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ) );

        // Bail if the data is empty.
        if ( empty( $data->data ) ) {
            update_option( 'blossomthemes_instagram_invalid_token', true, false );
            return false;
        }

        // Set the data fetch to true and update the cache with new posts.
        set_transient( 'blossomthemes_instagram_data_fetch', true, $this->get_transient_lifetime() );
        update_option( 'blossomthemes_instagram_user_feed', $data, false );
        return $this->processing_response_data( $data, $photo_size, $image_limit );
    }

    public function processing_response_data( $data, $photo_size, $image_limit ) {

        // Bail early if the data is false.
        if ( false === $data ) {
            return false;
        }

        $result   = array();
        $username = '';

        $i = 0;
        foreach ( $data->data as $key => $item ) {

            if ( empty( $username ) ) {
                $username = $item->user->username;
            }

            if ( $i < $image_limit ) {

                $result[] = array(
                    'link'           => $item->link,
                    'image-standard' => $item->images->standard_resolution->url,
                    'image-url'      => $item->images->$photo_size->url,
                    'likes_count'    => ! empty( $item->likes->count ) ? esc_attr( $item->likes->count ) : 0,
                    'comments_count' => ! empty( $item->comments->count ) ? esc_attr( $item->comments->count ) : 0,
                    'caption'        => ! empty( $item->caption->text ) ? html_entity_decode( $item->caption->text ) : 'Instagram-Caption',
                );
            }
            $i++;
        }

        $result = array( 'items' => $result, 'username' => $username );

        return $result;
    }

    /**
     * Check if given access token is valid for Instagram Api.
     */
    public static function is_access_token_valid( $access_token ) {
        $response = wp_remote_get( sprintf( 'https://api.instagram.com/v1/users/self/?access_token=%s', $access_token ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        // Set invalid token to false.
        update_option( 'blossomthemes_instagram_invalid_token', false, false );
        return true;
    }

    public function is_configured() {
        $transient = 'blossomthemes_instagram_is_configured';

        if ( false !== ( $result = get_transient( $transient ) ) ) {

            if ( 'yes' === $result ) {
                return true;
            }

            if ( 'no' === $result ) {
                return false;
            }
        }

        $condition = $this->is_access_token_valid( $this->access_token );

        if ( true === $condition ) {
            set_transient( $transient, 'yes', DAY_IN_SECONDS );

            return true;
        }

        set_transient( $transient, 'no', DAY_IN_SECONDS );

        return false;
    }

    /**
     * Reset the cache.
     */
    public static function reset_cache() {
        delete_transient( 'blossomthemes_instagram_is_configured' );
    }

    /**
     * Get access token.
     */
    public function get_access_token() {
        return $this->access_token;
    }

    /**
     * Set access token.
     * 
     * @param string $access_token Instagram access token.
     */
    public function set_access_token( $access_token ) {
        $this->access_token = $access_token;
    }

}
