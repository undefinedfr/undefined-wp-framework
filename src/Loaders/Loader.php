<?php

namespace Undefined\Core\Loaders;

/**
 * Loader Helper
 * @since 2.0.0
 * @package Undefined\Core\Loaders
 */
class Loader
{
    /**
     * @var array
     */
	protected $_classes = [];

    /**
     * @var array
     */
	protected $_type = 'Plugins';

    /**
     * Load All
     *
     * @param $classes
     * @return void
     */
	public function loadAll( $classes = false )
	{
        if( !$classes )
		    $classes = $this->getAll();

		foreach( $classes as $class => $path ) {
            self::load( $class, $path );
		}
	}

    /**
     * Set all classes
     *
     * @param $path
     * @param $excluded
     * @return $this
     */
	public function setAll( $path = null,  $excluded = [ '.', '..', 'Loader.php' ] )
	{
        $classes = $this->_getClasses( $path, $excluded );

        foreach( $classes as $className => $class ) {
            $this->set( $className, $class );
        }

        return $this;
	}

    /**
     * Load class
     *
     * @param $classname
     * @param $path
     * @return void
     */
	public function load( $classname, $path )
	{
		if( !class_exists( $classname ) ) {
			require_once $path;
		}

		$class = new $classname();

        $classNameArr = explode( '\\', $classname );

        $this->remove( $classname );

        $this->set( end( $classNameArr ), $class );
	}

    /**
     * Get specific class
     *
     * @param $class
     * @return false|mixed
     */
	public function get( $class )
	{
		if( !array_key_exists( $class, $this->_classes ) ) {
			return false;
		}

		return $this->_classes[ $class ];
	}

    /**
     * Remove classe from classes
     *
     * @param $class
     * @return void
     */
	public function remove( $class )
	{
		if( array_key_exists( $class, $this->_classes ) ) {
            unset( $this->_classes[ $class ] );
		}
	}

    /**
     * Get all classes
     *
     * @return array
     */
	public function getAll()
	{
		return $this->_classes;
	}

    /**
     * Set class to classes
     *
     * @param $className
     * @param $class
     * @return void
     */
	public function set( $className, $class )
	{
		$this->_classes[ $className ] = $class;
	}

    /**
     * Set loader type
     *
     * @param $type
     * @return $this
     */
	public function setType( $type = 'Plugins' )
	{
		$this->_type = $type;

        return $this;
	}

    /**
     * Get classes from folder files
     *
     * @param $path
     * @param $excluded
     * @return array|false
     */
    protected function _getClasses( $path = null, $excluded = [ '.', '..', 'Loader.php' ] )
    {
        if( empty( $path ) )
            $path = __DIR__ . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR;

        if( empty( $excluded ) )
            $excluded = array_merge( $excluded, [ $this->_type . '.php' ]);

        $classes = scandir( $path );

        foreach( $classes as $k => $class ) {
            $className = str_replace( '.php', '', $class );

            if( !in_array( $class, $excluded ) ) {
                $classes[ '\App\\' . $this->_type . '\\' . $className ] = $path . $class;
            }
            unset( $classes[ $k ] );
        }

        return $classes;
    }
}
