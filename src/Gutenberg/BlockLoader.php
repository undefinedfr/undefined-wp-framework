<?php

namespace Undefined\Core\Gutenberg;

/**
 * Blocks Loader
 * @since 1.0.4
 * @package Undefined\Core\Gutenberg
 */
class BlockLoader{

	protected $_blocks = [];

	public function loadAll( $path = __DIR__ )
	{
		$plugins = scandir($path);

		foreach($plugins as $plugin){

			if( !in_array($plugin, ['.','..','BlockLoader.php','Block.php']) )
			{
                BlockLoader::load($plugin, $path);
			}
		}
	}

	public function load($file, $path)
	{
		if( $path != __DIR__ )
			$classname = '\App\Project\Gutenberg\\'.$file;
		else
			$classname = '\Undefined\Core\Gutenberg\\'.$file;

		if(!class_exists($classname)){
			require_once $path . DIRECTORY_SEPARATOR . $file;
		}

		$classname = str_replace('.php', '', $classname);

		$class = new $classname();

		$this->set(str_replace('.php', '', $file), $class);
	}

	public function get( $plugin )
	{
		if( !array_key_exists($plugin, $this->_blocks)){
			return false;
		}

		return $this->_blocks[$plugin];
	}

	public function getAll()
	{
		return $this->_blocks;
	}

	public function set( $plugin, $class )
	{
		$this->_blocks[$plugin] = $class;
	}
}
