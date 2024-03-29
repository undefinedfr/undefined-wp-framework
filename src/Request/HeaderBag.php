<?php

namespace Undefined\Core\Request;

/**
 * @name HeaderBag
 * @package Undefined\Core\Request
 */
class HeaderBag
{
    protected $headers = [];

    protected $cacheControl = [];

    /**
     * @param array $headers An array of HTTP headers
     */
    public function __construct( array $headers = [] )
    {
        foreach ( $headers as $key => $values ) {
            $this->set( $key, $values );
        }
    }

    /**
     * Returns the headers as a string.
     *
     * @return string The headers
     */
    public function __toString()
    {
        if ( !$headers = $this->all() ) {
            return '';
        }

        ksort( $headers );
        $max        = max( array_map( 'strlen', array_keys( $headers ) ) ) + 1;
        $content    = '';

        foreach ( $headers as $name => $values ) {
            $name = ucwords( $name, '-' );
            foreach ( $values as $value ) {
                $content .= sprintf( "%-{$max}s %s\r\n", $name.':', $value );
            }
        }

        return $content;
    }

    /**
     * Returns the headers.
     *
     * @return array An array of headers
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys()
    {
        return array_keys( $this->all() );
    }

    /**
     * Replaces the current HTTP headers by a new set.
     *
     * @param array $headers An array of HTTP headers
     */
    public function replace( array $headers = [] )
    {
        $this->headers = [];
        $this->add( $headers );
    }

    /**
     * Adds new headers the current HTTP headers set.
     *
     * @param array $headers An array of HTTP headers
     */
    public function add( array $headers )
    {
        foreach ( $headers as $key => $values ) {
            $this->set( $key, $values );
        }
    }

    /**
     * Returns a header value by name.
     *
     * @param string      $key     The header name
     * @param string|null $default The default value
     * @param bool        $first   Whether to return the first value or all header values
     *
     * @return string|string[]|null The first header value or default value if $first is true, an array of values otherwise
     */
    public function get( $key, $default = null, $first = true )
    {
        $key = str_replace( '_', '-', strtolower( $key ) );
        $headers = $this->all();
        if ( !array_key_exists( $key, $headers ) ) {
            if ( null === $default ) {
                return $first ? null : [];
            }
            return $first ? $default : [ $default ];
        }
        if ( $first ) {
            return \count( $headers[$key] ) ? $headers[$key][0] : $default;
        }

        return $headers[$key];
    }

    /**
     * Sets a header by name.
     *
     * @param string          $key     The key
     * @param string|string[] $values  The value or an array of values
     * @param bool            $replace Whether to replace the actual value or not (true by default)
     */
    public function set( $key, $values, $replace = true )
    {
        $key = str_replace( '_', '-', strtolower( $key ) );
        if ( \is_array( $values ) ) {
            $values = array_values( $values );
            if ( true === $replace || !isset( $this->headers[$key] ) ) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge( $this->headers[$key], $values );
            }
        } else {
            if ( true === $replace || !isset( $this->headers[$key] ) ) {
                $this->headers[$key] = [ $values ];
            } else {
                $this->headers[$key][] = $values;
            }
        }
    }

    /**
     * Returns true if the HTTP header is defined.
     *
     * @param string $key The HTTP header
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has( $key )
    {
        return array_key_exists( str_replace( '_', '-', strtolower( $key ) ), $this->all() );
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     *
     * @param string $key   The HTTP header name
     * @param string $value The HTTP value
     *
     * @return bool true if the value is contained in the header, false otherwise
     */
    public function contains( $key, $value )
    {
        return \in_array( $value, $this->get( $key, null, false ) );
    }

    /**
     * Removes a header.
     *
     * @param string $key The HTTP header name
     */
    public function remove( $key )
    {
        $key = str_replace( '_', '-', strtolower( $key ) );
        unset( $this->headers[$key] );
    }

    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->headers );
    }

    /**
     * Returns the number of headers.
     *
     * @return int The number of headers
     */
    public function count()
    {
        return \count( $this->headers );
    }
}
