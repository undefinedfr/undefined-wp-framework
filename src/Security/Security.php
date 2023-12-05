<?php

namespace Undefined\Core\Security;

/**
 * Security Actions
 *
 * @name Security
 * @since 1.0.0
 * @package Undefined\Core\Security
 */
class Security
{
    /**
     * @return void
     */
    public function __construct()
    {
        add_filter( 'rest_endpoints', [ $this, 'remove_users_rest_route' ] );
    }

    /**
     * Remove Users rest route
     *
     * @param $endpoints
     * @return mixed
     */
    public function remove_users_rest_route( $endpoints )
    {
        if ( isset( $endpoints[ '/wp/v2/users' ] ) ) {
            unset( $endpoints[ '/wp/v2/users' ] );
        }

        if ( isset( $endpoints[ '/wp/v2/users/(?P<id>[\d]+)' ] ) ) {
            unset( $endpoints[ '/wp/v2/users/(?P<id>[\d]+)' ] );
        }

        return $endpoints;
    }
}
