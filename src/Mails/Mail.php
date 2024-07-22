<?php
namespace Undefined\Core\Mails;

/**
 * Mail Helper
 *
 * @doc https://github.com/anthonybudd/WP_Mail
 * @name Mail
 * @since 1.0.0
 * @package Undefined\Core\Mails
 */

class Mail
{
    /**
     * @var array
     */
    private $to 			    = [];

    /**
     * @var array
     */
    private $cc 			    = [];

    /**
     * @var array
     */
    private $bcc 			    = [];

    /**
     * @var array
     */
    private $headers 		    = [];

    /**
     * @var array
     */
    private $attachments 	    = [];

    /**
     * @var bool
     */
    private $sendAsHTML 	    = true;

    /**
     * @var string
     */
    private $subject 		    = '';

    /**
     * @var string
     */
    private $from 			    = '';

    /**
     * @var bool
     */
    private $template 		    = false;

    /**
     * @var array
     */
    private $variables 		    = [];

    /**
     * @return Mail
     */
    public static function init()
    {
        return new self;
    }

    /**
     * @return void
     */
    public function __construct()
    {
        $this->variables  = [
            'blogname'          => get_bloginfo( 'blogname' ),
            'home_url'          => site_url(),
            'stylesheet_uri'    => get_stylesheet_directory_uri(),
            'blogdescription'   => get_bloginfo( 'blogdescription' ),
        ];
    }

    /**
     * Set recipients
     *
     * @param  array|String $to
     * @return Object $this
     */
    public function to( $to )
    {
        $this->to = is_array( $to ) ? $to : [ $to ];

        return $this;
    }

    /**
     * Get recipients
     *
     * @return array $to
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set Cc recipients
     *
     * @param  String|array $cc
     * @return Object $this
     */
    public function cc( $cc )
    {
        $this->cc = is_array( $cc ) ? $cc : [ $cc ];

        return $this;
    }

    /**
     * Get Cc recipients
     *
     * @return array $cc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set Email Bcc recipients
     *
     * @param  String|array $bcc
     * @return Object $this
     */
    public function bcc( $bcc )
    {
        $this->bcc = is_array( $bcc ) ? $bcc : [ $bcc ];

        return $this;
    }

    /**
     * Set email Bcc recipients
     *
     * @return array $bcc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set email Subject
     *
     * @param  string $subject
     * @return Object $this
     */
    public function subject( $subject )
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Retruns email subject
     *
     * @return array
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set From header
     *
     * @param  String
     * @return Object $this
     */
    public function from( $from )
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the email's headers
     *
     * @param  String|array  $headers [description]
     * @return Object $this
     */
    public function headers( $headers )
    {
        $this->headers = is_array( $headers ) ? $headers : [ $headers ];

        return $this;
    }

    /**
     * Retruns headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns email content type
     *
     * @return String
     */
    public function HTMLFilter()
    {
        return 'text/html';
    }

    /**
     * Set email content type
     *
     * @param  Bool $html
     * @return Object $this
     */
    public function sendAsHTML( $html )
    {
        $this->sendAsHTML = $html;

        return $this;
    }

    /**
     * Attach a file or array of files.
     * Filepaths must be absolute.
     *
     * @param  String|array $path
     * @throws \Exception
     * @return Object $this
     */
    public function attach( $path )
    {
        if( is_array( $path ) ) {
            $this->attachments = [];
            foreach( $path as $path_ ) {
                if( !file_exists( $path_ ) ){
                    throw new \Exception( "Attachment not found at $path" );
                }else{
                    $this->attachments[] = $path_;
                }
            }
        } else {
            if( !file_exists( $path ) ) {
                throw new \Exception("Attachment not found at $path");
            }
            $this->attachments = [$path];
        }

        return $this;
    }

    /**
     * Set the template file
     * @param  String $template  Path to HTML template
     * @param  array  $variables
     * @throws \Exception
     * @return Object $this
     */
    public function template( $template, $variables = [] )
    {
        if( is_array( $variables ) ) {
            $this->variables = array_merge( $this->variables, $variables );
        }

        $this->template = $template;

        return $this;
    }

    /**
     * Renders the template
     * @return String
     */
    public function render()
    {
        $variables = $this->variables;
        foreach ( $variables as $key => &$variable ) {
            if( strpos( $key, '_html_' ) === false )
                $variable = esc_html( $variable );
        }
        return \Timber::compile( $this->template, $this->variables );
    }

    public function buildSubject()
    {
        return $this->parseAsMustache(
            $this->subject,
            $this->variables
        );
    }

    public function parseAsMustache( $string, $variables = [] )
    {
        preg_match_all( '/\{\{\s*.+?\s*\}\}/', $string, $matches );
        foreach( $matches[0] as $match ){
            $var = str_replace( '{', '', str_replace( '}', '', preg_replace( '/\s+/', '', $match ) ) );

            if( isset( $variables[$var] ) && !is_array( $variables[$var] ) ){
                $string = str_replace( $match, $variables[$var], $string );
            }
        }

        return $string;
    }

    /**
     * Builds Email Headers
     * @return String email headers
     */
    public function buildHeaders()
    {
        $headers = '';
        $headers .= implode( "\r\n", $this->headers ) ."\r\n";

        foreach( $this->bcc as $bcc ) {
            $headers .= sprintf( "Bcc: %s \r\n", $bcc );
        }

        foreach( $this->cc as $cc ) {
            $headers .= sprintf( "Cc: %s \r\n", $cc );
        }

        if( !empty($this->from ) ) {
            $headers .= sprintf( "From: %s \r\n", $this->from );
        }

        return $headers;
    }

    /**
     * Sends a rendered email using
     * WordPress's wp_mail() function
     * @return Bool
     */
    public function send()
    {
        if( count( $this->to ) === 0 ) {
            throw new \Exception( 'You must set at least 1 recipient' );
        }
        if( empty( $this->template ) ) {
            throw new \Exception( 'You must set a template' );
        }

        if( $this->sendAsHTML  ){
            add_filter( 'wp_mail_content_type', [$this, 'HTMLFilter'] );
        }

        return wp_mail( $this->to, $this->buildSubject(), $this->render(), $this->buildHeaders(), $this->attachments );
    }
}
