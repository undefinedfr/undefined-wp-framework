<?php
namespace Undefined\Core;

use Undefined\Core\Loaders\Loader;

/**
 * Add Custom Gutenberg Blocks
 *
 * Supports two block structures:
 * - app/blocks/ (lowercase, plural) : Timber-style folder structure for Gutenberg blocks
 * - app/Block/ (uppercase, singular) : ACF custom blocks (legacy class-based)
 *
 * @name Blocks
 * @since 1.0.0
 * @update 2.1.0
 * @package Undefined\Core
 */
class Blocks {

	/**
	 * @var Loader
	 */
	protected $_blocks;

	/**
	 * @var array Loaded block instances
	 */
	protected $_loadedBlocks = [];

	/**
	 * @return void
	 */
	public function __construct() {
		$this->_blocks = new Loader();

		// Load ACF custom blocks from app/Block/ (uppercase, singular - legacy)
		$acfBlocksPath = apply_filters( 'undfnd_acf_blocks_path', get_template_directory() . '/app/Block/' );
		if ( is_dir( $acfBlocksPath ) ) {
			$this->_blocks
				->setType( 'Block' )
				->setAll( $acfBlocksPath )
				->loadAll();
		}

		// Load Gutenberg blocks from app/blocks/ (lowercase, plural - Timber structure)
		$gutenbergBlocksPath = apply_filters( 'undfnd_gutenberg_blocks_path', get_template_directory() . '/app/blocks/' );
		if ( is_dir( $gutenbergBlocksPath ) ) {
			$this->loadTimberBlocks( $gutenbergBlocksPath );
		}
	}

	/**
	 * Load blocks from Timber-style folder structure
	 *
	 * @param string $path
	 * @return void
	 */
	protected function loadTimberBlocks( string $path ): void {
		$directories = glob( $path . '*', GLOB_ONLYDIR );

		foreach ( $directories as $dir ) {
			$blockName = basename( $dir );
			$blockFile = $dir . '/' . $blockName . '.php';

			if ( file_exists( $blockFile ) ) {
				require_once $blockFile;

				// Try to instantiate the class
				$className = $this->getBlockClassName( $blockName );

				if ( class_exists( $className ) ) {
					$this->_loadedBlocks[ $blockName ] = new $className();
				}
			}
		}
	}

	/**
	 * Get the class name for a block
	 *
	 * @param string $blockName
	 * @return string
	 */
	protected function getBlockClassName( string $blockName ): string {
		// Convert kebab-case to PascalCase for class name
		$className = str_replace( '-', ' ', $blockName );
		$className = ucwords( $className );
		$className = str_replace( ' ', '', $className );

		// Check common namespaces
		$namespaces = apply_filters(
			'undfnd_block_namespaces',
			[
				'App\\Blocks\\' . $className . '\\',
				'App\\Block\\',
				'',
			]
		);

		foreach ( $namespaces as $namespace ) {
			$fullClassName = $namespace . $className;
			if ( class_exists( $fullClassName ) ) {
				return $fullClassName;
			}
		}

		return $className;
	}

	/**
	 * Retrieve all blocks (Loader instance)
	 *
	 * @return Loader
	 */
	public function getBlocks() {
		return $this->_blocks;
	}

	/**
	 * Get all loaded block instances
	 *
	 * @return array
	 */
	public function getAll(): array {
		$blocks = [];

		// Get blocks from Loader
		if ( $this->_blocks instanceof Loader ) {
			foreach ( $this->_blocks->getAll() as $block ) {
				$blocks[] = $block;
			}
		}

		// Add Timber-loaded blocks
		foreach ( $this->_loadedBlocks as $block ) {
			$blocks[] = $block;
		}

		return $blocks;
	}
}
