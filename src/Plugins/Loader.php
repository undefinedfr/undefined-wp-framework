<?php

namespace Undefined\Core\Plugins;

/**
 * Plugins Loader
 * @since 1.0.4
 * @package Undefined\Core\Plugins
 * TODO : Activate / Desactivate Plugins
 */
class Loader{

	protected $_plugins = [];

	public function loadAll( $path = __DIR__ )
	{
		$plugins = scandir($path);

		foreach($plugins as $plugin){

			if( !in_array($plugin, ['.','..','Loader.php']) )
			{
				Loader::load($plugin, $path);
			}
		}
	}

	public function load($file, $path)
	{
		if( $path != __DIR__ )
			$classname = '\App\Project\Plugins\\'.$file;
		else
			$classname = '\Undefined\Core\Plugins\\'.$file;

		if(!class_exists($classname)){
			require_once $path . DIRECTORY_SEPARATOR . $file;
		}

		$classname = str_replace('.php', '', $classname);

		$class = new $classname();
		
		$this->set(str_replace('.php', '', $file), $class);
	}

	public function get( $plugin )
	{
		if( !array_key_exists($plugin, $this->_plugins)){
			return false;
		}

		return $this->_plugins[$plugin];
	}

	public function set( $plugin, $class )
	{
		$this->_plugins[$plugin] = $class;
	}
}