<?php
namespace Undefined\Core;

/**
 * @name Request
 * @since 1.0.0
 * @update 2.0.0
 * @package Undefined\Core
 */
class Request
{
    /**
     * Request body parameters ($_POST).
     */
    public $request;

    /**
     * Query string parameters ($_GET).
     */
    public $query;

    /**
     * Server and execution environment parameters ($_SERVER).
     */
    public $server;

    /**
     * Cookies ($_COOKIE).
     */
    public $cookies;

    /**
     * Headers (taken from the $_SERVER).
     */
    public $session;

    /**
     * Sessions (taken from the $_SESSION).
     */
    public $headers;

    /**
     * @var string|resource|false|null
     */
    protected $content;

    /**
     * Custom parameters.
     *
     * @var Request\ParameterBag
     */
    public $attributes;

    /**
     * @var string
     */
    protected $format;

    /**
     * @return void
     */
    public function __construct( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null, $session = [] )
    {
        $this->initialize( $query, $request, $attributes, $cookies, $files, $server, $content, $session );
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     * @param array                $server     The SESSION parameters
     */
    public function initialize( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null, $session = [] )
    {
        $this->request                  = new Request\ParameterBag( $request );
        $this->query                    = new Request\ParameterBag( $query );
        $this->attributes               = new Request\ParameterBag( $attributes );
        $this->cookies                  = new Request\ParameterBag( $cookies );
        $this->server                   = new Request\ServerBag( $server );
        $this->session                  = new Request\ServerBag( $session );
        $this->headers                  = new Request\HeaderBag( $this->server->getHeaders() );
        $this->content                  = $content;
        $this->languages                = null;
        $this->charsets                 = null;
        $this->encodings                = null;
        $this->acceptableContentTypes   = null;
        $this->pathInfo                 = null;
        $this->requestUri               = null;
        $this->baseUrl                  = null;
        $this->basePath                 = null;
        $this->method                   = null;
        $this->format                   = null;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return static
     */
    public static function createFromGlobals()
    {
        $request = self::createRequestFromFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, null, $_SESSION);
        if ( 0 === strpos($request->headers->get( 'CONTENT_TYPE' ), 'application/x-www-form-urlencoded')
            && \in_array( strtoupper( $request->server->get( 'REQUEST_METHOD', 'GET' ) ), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str( $request->getContent(), $data );
            $request->request = new Request\ParameterBag( $data );
        }

        return $request;
    }

    /**
     * Gets a "parameter" value from any bag.
     *
     * This method is mainly useful for libraries that want to provide some flexibility. If you don't need the
     * flexibility in controllers, it is better to explicitly get request parameters from the appropriate
     * public property instead (attributes, query, request).
     *
     * Order of precedence: PATH (routing placeholders or custom attributes), GET, BODY
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get( $key, $default = null )
    {
        if ( $this !== $result = $this->attributes->get( $key, $this ) ) {
            return $result;
        }
        if ( $this !== $result = $this->query->get( $key, $this ) ) {
            return $result;
        }
        if ( $this !== $result = $this->request->get( $key, $this ) ) {
            return $result;
        }
        if ( $this !== $result = $this->session->get( $key, $this ) ) {
            return $result;
        }

        return $default;
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream
     *
     * @throws \LogicException
     */
    public function getContent( $asResource = false )
    {
        $currentContentIsResource = \is_resource( $this->content );
        if ( true === $asResource ) {
            if ( $currentContentIsResource ) {
                rewind( $this->content );
                return $this->content;
            }
            // Content passed in parameter (test)
            if ( \is_string( $this->content ) ) {
                $resource = fopen( 'php://temp', 'r+' );
                fwrite( $resource, $this->content );
                rewind( $resource );
                return $resource;
            }
            $this->content = false;
            return fopen( 'php://input', 'rb' );
        }
        if ( $currentContentIsResource ) {
            rewind( $this->content );
            return stream_get_contents( $this->content );
        }
        if ( null === $this->content || false === $this->content ) {
            $this->content = file_get_contents( 'php://input' );
        }

        return $this->content;
    }

    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     *
     * @return static
     */
    public function duplicate( array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null )
    {
        $dup = clone $this;

        if ( null !== $query ) {
            $dup->query = new Request\ParameterBag( $query );
        }

        if ( null !== $request ) {
            $dup->request = new Request\ParameterBag( $request );
        }

        if ( null !== $attributes ) {
            $dup->attributes = new Request\ParameterBag( $attributes );
        }

        if ( null !== $cookies ) {
            $dup->cookies = new Request\ParameterBag( $cookies );
        }

        if ( null !== $files) {
            //$dup->files = new FileBag($files);
        }

        if ( null !== $server ) {
            $dup->server = new Request\ServerBag( $server );
            $dup->headers = new Request\HeaderBag( $dup->server->getHeaders() );
        }

        $dup->languages                 = null;
        $dup->charsets                  = null;
        $dup->encodings                 = null;
        $dup->acceptableContentTypes    = null;
        $dup->pathInfo                  = null;
        $dup->requestUri                = null;
        $dup->baseUrl                   = null;
        $dup->basePath                  = null;
        $dup->method                    = null;
        $dup->format                    = null;

        if ( !$dup->get( '_format' ) && $this->get( '_format' ) ) {
            $dup->attributes->set( '_format', $this->get( '_format' ) );
        }

        if ( !$dup->getRequestFormat( null ) ) {
            $dup->setRequestFormat( $this->getRequestFormat( null ) );
        }

        return $dup;
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request attribute
     *  * $default
     *
     * @param string|null $default The default format
     *
     * @return string The request format
     */
    public function getRequestFormat( $default = 'html' )
    {
        if ( null === $this->format ) {
            $this->format = $this->attributes->get( '_format' );
        }

        return null === $this->format ? $default : $this->format;
    }

    /**
     * Sets the request format.
     *
     * @param string $format The request format
     */
    public function setRequestFormat( $format )
    {
        $this->format = $format;
    }

    /**
     * Clones the current request.
     *
     * Note that the session is not cloned as duplicated requests
     * are most of the time sub-requests of the main one.
     */
    public function __clone()
    {
        $this->query        = clone $this->query;
        $this->request      = clone $this->request;
        $this->attributes   = clone $this->attributes;
        $this->cookies      = clone $this->cookies;
        $this->files        = clone $this->files;
        $this->server       = clone $this->server;
        $this->headers      = clone $this->headers;
    }
    /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    public function __toString()
    {
        try {
            $content = $this->getContent();
        } catch ( \LogicException $e ) {
            return trigger_error( $e, E_USER_ERROR );
        }

        $cookieHeader   = '';
        $cookies        = [];
        foreach ( $this->cookies as $k => $v ) {
            $cookies[] = $k.'='.$v;
        }

        if ( !empty( $cookies ) ) {
            $cookieHeader = 'Cookie: '.implode( '; ', $cookies )."\r\n";
        }

        return
            $this->headers.
            $cookieHeader."\r\n".
            $content;
    }

    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
     * $_FILES is never overridden, see rfc1867
     */
    public function overrideGlobals()
    {
        $this->server->set( 'QUERY_STRING', static::normalizeQueryString( http_build_query( $this->query->all(), '', '&' ) ) );

        $_GET       = $this->query->all();
        $_POST      = $this->request->all();
        $_SERVER    = $this->server->all();
        $_COOKIE    = $this->cookies->all();

        foreach ( $this->headers->all() as $key => $value ) {
            $key = strtoupper( str_replace( '-', '_', $key ) );
            if ( \in_array( $key, [ 'CONTENT_TYPE', 'CONTENT_LENGTH' ] ) ) {
                $_SERVER[$key] = implode( ', ', $value );
            } else {
                $_SERVER['HTTP_'.$key] = implode( ', ', $value );
            }
        }

        $request        = [ 'g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE ];
        $requestOrder   = ini_get( 'request_order' ) ?: ini_get( 'variables_order' );
        $requestOrder   = preg_replace( '#[^cgp]#', '', strtolower( $requestOrder ) ) ?: 'gp';
        $_REQUEST       = [[]];
        foreach ( str_split( $requestOrder ) as $order ) {
            $_REQUEST[] = $request[ $order ];
        }
        $_REQUEST       = array_merge(...$_REQUEST);
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data
     *
     * @return static
     */
    public static function create( $uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null )
    {
        $server                     = array_replace( [
            'SERVER_NAME'           => 'localhost',
            'SERVER_PORT'           => 80,
            'HTTP_HOST'             => 'localhost',
            'HTTP_USER_AGENT'       => 'Undefined',
            'HTTP_ACCEPT'           => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE'  => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'   => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'           => '127.0.0.1',
            'SCRIPT_NAME'           => '',
            'SCRIPT_FILENAME'       => '',
            'SERVER_PROTOCOL'       => 'HTTP/1.1',
            'REQUEST_TIME'          => time(),
        ], $server );

        $server['PATH_INFO']        = '';
        $server['REQUEST_METHOD']   = strtoupper($method);
        $components                 = parse_url($uri);

        if ( isset( $components[ 'host' ] ) ) {
            $server['SERVER_NAME']  = $components[ 'host' ];
            $server['HTTP_HOST']    = $components[ 'host' ];
        }

        if ( isset( $components[ 'scheme' ] ) ) {
            if ( 'https' === $components[ 'scheme' ] ) {
                $server[ 'HTTPS' ]        = 'on';
                $server[ 'SERVER_PORT' ] = 443;
            } else {
                unset( $server[ 'HTTPS' ]);
                $server[ 'SERVER_PORT' ] = 80;
            }
        }

        if ( isset($components[ 'port' ] ) ) {
            $server[ 'SERVER_PORT' ] = $components[ 'port' ];
            $server[ 'HTTP_HOST' ] .= ':'.$components[ 'port' ];
        }

        if ( isset( $components[ 'user' ] ) ) {
            $server[ 'PHP_AUTH_USER' ] = $components[ 'user' ];
        }

        if ( isset( $components['pass'] ) ) {
            $server[ 'PHP_AUTH_PW' ] = $components[ 'pass' ];
        }

        if ( !isset($components[ 'path' ] ) ) {
            $components[ 'path' ] = '/';
        }

        switch ( strtoupper( $method ) ) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if ( !isset( $server[ 'CONTENT_TYPE' ] ) ) {
                    $server[ 'CONTENT_TYPE' ] = 'application/x-www-form-urlencoded';
                }
            // no break
            case 'PATCH':
                $request    = $parameters;
                $query      = [];
                break;
            default:
                $request    = [];
                $query      = $parameters;
                break;
        }

        $queryString = '';

        if ( isset( $components[ 'query' ] ) ) {
            parse_str( html_entity_decode( $components[ 'query' ] ), $qs );
            if ( $query ) {
                $query          = array_replace( $qs, $query );
                $queryString    = http_build_query( $query, '', '&' );
            } else {
                $query          = $qs;
                $queryString    = $components[ 'query' ];
            }
        } elseif ( $query ) {
            $queryString = http_build_query( $query, '', '&' );
        }

        $server[ 'REQUEST_URI' ]    = $components['path'].( '' !== $queryString ? '?' . $queryString : '' );
        $server[ 'QUERY_STRING' ]   = $queryString;

        return self::createRequestFromFactory( $query, $request, [], $cookies, $files, $server, $content, $_SESSION );
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null A normalized query string for the Request
     */
    public function getQueryString()
    {
        $qs = static::normalizeQueryString( $this->server->get( 'QUERY_STRING' ) );

        return '' === $qs ? null : $qs;
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     *
     * @return string A normalized query string for the Request
     */
    public static function normalizeQueryString( $qs )
    {
        if ( '' == $qs ) {
            return '';
        }

        parse_str( $qs, $qs );
        ksort( $qs );

        return http_build_query( $qs, '', '&', PHP_QUERY_RFC3986 );
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        return preg_split( '/\s*,\s*/', $this->headers->get( 'if_none_match' ), null, PREG_SPLIT_NO_EMPTY );
    }

    /**
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param $content
     * @param $session
     * @return static
     */
    private static function createRequestFromFactory( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null, $session = [] )
    {
        return new static( $query, $request, $attributes, $cookies, $files, $server, $content, $session );
    }
}
