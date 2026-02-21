<?php

namespace Undefined\Core\Block;

use Extended\ACF\Location;
use Timber;

/**
 * Block - ACF Gutenberg Block with Timber support
 *
 * Structure recommandée (Timber-style):
 * app/blocks/{name}/
 *   ├── block.json      # Métadonnées du bloc (optionnel si défini en PHP)
 *   ├── {name}.php      # Cette classe
 *   ├── {name}.twig     # Template Twig
 *   ├── {name}.css      # Styles (optionnel)
 *   ├── {name}.js       # Scripts (optionnel)
 *   └── icon.svg        # Icône (optionnel)
 *
 * @name Block
 * @since 1.0.9
 * @update 2.1.0
 * @package Undefined\Core\Block
 */
class Block
{
    /**
     * @var string Block name (slug)
     */
    public $name;

    /**
     * @var string Block title
     */
    public $title;

    /**
     * @var string Block description
     */
    public $description = '';

    /**
     * @var string Block category
     */
    public $category = 'custom';

    /**
     * @var string Block icon (dashicon or SVG)
     */
    public $icon = 'slides';

    /**
     * @var string Edit mode: preview, edit, auto
     */
    public $mode = 'preview';

    /**
     * @var array Block keywords for search
     */
    public $keywords = [];

    /**
     * @var array Block supports
     */
    public $supports = [];

    /**
     * @var array ACF field group definition
     */
    public $groupField = [];

    /**
     * @var array Style dependencies
     */
    public $styleDependencies = [];

    /**
     * @var array Script dependencies
     */
    public $scriptDependencies = [];

    /**
     * @var string|null Block directory path
     */
    protected $blockDir = null;

    /**
     * @var string|null Template path for rendering
     */
    protected $templatePath = null;

    /**
     * Block constructor
     */
    public function __construct()
    {
        // Detect block directory
        $this->detectBlockDir();

        // Load block.json if exists
        $this->loadBlockJson();

        // Translate title
        $this->title = __($this->title, 'blok_lang');

        // Load icon
        $this->loadIcon();

        // Set template path
        $this->setTemplatePath();

        // Register ACF fields
        if (class_exists('acf_field')) {
            $this->_setGroupField();

            if (!empty($this->groupField['fields'])) {
                add_action('acf/init', [$this, 'registerGroupField']);
            }
        }

        // Register block
        add_action('acf/init', [$this, 'registerBlock']);
    }

    /**
     * Detect block directory (Timber structure)
     */
    protected function detectBlockDir(): void
    {
        // Check app/blocks/{name}/
        $path = get_template_directory() . '/app/blocks/' . $this->name;

        if (is_dir($path)) {
            $this->blockDir = $path;
        }
    }

    /**
     * Load block.json metadata
     */
    protected function loadBlockJson(): void
    {
        if (!$this->blockDir) {
            return;
        }

        $jsonPath = $this->blockDir . '/block.json';

        if (!file_exists($jsonPath)) {
            return;
        }

        $json = json_decode(file_get_contents($jsonPath), true);

        if (!$json) {
            return;
        }

        // Map block.json to class properties (class properties take precedence if already set)
        $this->title = $this->title ?: ($json['title'] ?? '');
        $this->description = $this->description ?: ($json['description'] ?? '');
        $this->category = $json['category'] ?? $this->category;
        $this->keywords = !empty($json['keywords']) ? $json['keywords'] : $this->keywords;
        $this->supports = !empty($json['supports']) ? $json['supports'] : $this->supports;

        // ACF specific options from block.json
        if (!empty($json['acf']['mode'])) {
            $this->mode = $json['acf']['mode'];
        }
    }

    /**
     * Load block icon
     */
    protected function loadIcon(): void
    {
        $iconPaths = [];

        // Timber structure icon
        if ($this->blockDir) {
            $iconPaths[] = $this->blockDir . '/icon.svg';
        }

        // Legacy path
        $iconPaths[] = get_template_directory() . '/public/assets/images/icons/gutenberg-icons/' . $this->name . '.svg';

        // Apply filter for custom paths
        $iconPaths = apply_filters('undfnd_block_icon_paths', $iconPaths, $this);

        foreach ($iconPaths as $path) {
            if (file_exists($path)) {
                $this->icon = file_get_contents($path);
                return;
            }
        }
    }

    /**
     * Set template path
     */
    protected function setTemplatePath(): void
    {
        // Timber structure: app/blocks/{name}/{name}.twig
        if ($this->blockDir) {
            $twigFile = $this->blockDir . '/' . $this->name . '.twig';
            if (file_exists($twigFile)) {
                $this->templatePath = $this->name . '/' . $this->name . '.twig';
                return;
            }
        }

        // Legacy: templates/components/{name}.twig
        $this->templatePath = apply_filters(
            'undfnd_gutenberg_bloc_template',
            'components/' . $this->name . '.twig',
            $this->name
        );
    }

    /**
     * Get block assets paths
     *
     * @return array{style: string|null, script: string|null}
     */
    protected function getAssets(): array
    {
        $assets = ['style' => null, 'script' => null];

        if ($this->blockDir) {
            // Timber structure
            $cssFile = $this->blockDir . '/' . $this->name . '.css';
            $jsMinFile = $this->blockDir . '/' . $this->name . '.min.js';
            $jsFile = file_exists($jsMinFile) ? $jsMinFile : $this->blockDir . '/' . $this->name . '.js';

            if (file_exists($cssFile)) {
                $assets['style'] = str_replace(
                    get_template_directory(),
                    get_template_directory_uri(),
                    $cssFile
                );
            }

            if (file_exists($jsFile)) {
                $assets['script'] = str_replace(
                    get_template_directory(),
                    get_template_directory_uri(),
                    $jsFile
                );
            }
        } else {
            // Legacy: public/blocks/{name}.css/.js
            $basePath = get_template_directory() . '/public/blocks/' . $this->name;
            $baseUri = get_template_directory_uri() . '/public/blocks/' . $this->name;

            if (file_exists($basePath . '.css')) {
                $assets['style'] = $baseUri . '.css';
            }

            if (file_exists($basePath . '.js')) {
                $assets['script'] = $baseUri . '.js';
            }
        }

        return apply_filters('undfnd_block_assets', $assets, $this);
    }

    /**
     * Register ACF block
     */
    public function registerBlock(): void
    {
        if (!function_exists('acf_register_block_type')) {
            return;
        }

        $assets = $this->getAssets();

        $blockArgs = [
            'name'            => $this->name,
            'title'           => $this->title,
            'description'     => $this->description,
            'category'        => $this->category,
            'icon'            => $this->icon,
            'mode'            => $this->mode,
            'keywords'        => array_merge([$this->name], $this->keywords),
            'render_callback' => [$this, 'render'],
            'enqueue_style'   => $assets['style'],
            'enqueue_script'  => $assets['script'],
            'supports'        => array_merge([
                'align'  => false,
                'anchor' => true,
                'jsx'    => true,
            ], $this->supports),
            'example'         => [
                'attributes' => [
                    'mode' => 'preview',
                    'data' => $this->getExampleData(),
                ],
            ],
        ];

        acf_register_block_type(apply_filters('undfnd_block_args', $blockArgs, $this));
    }

    /**
     * Get example data for block preview
     *
     * @return array
     */
    protected function getExampleData(): array
    {
        return [];
    }

    /**
     * Render block
     *
     * @param array  $block
     * @param string $content
     * @param bool   $is_preview
     * @param int    $post_id
     */
    public function render($block, $content = '', $is_preview = false, $post_id = 0): void
    {
        // Prepare block data (use _prepareBlock for legacy compatibility)
        $block = $this->_prepareBlock($block, $post_id);

        // Allow child classes to add context
        $context = $this->getContext($block, $is_preview, $post_id);

        // Render with Timber
        $this->renderTemplate($block, $context, $is_preview);
    }

    /**
     * Prepare block data
     *
     * @param array $block
     * @param int   $post_id
     * @return array
     */
    protected function prepareBlock(array $block, int $post_id = 0): array
    {
        if (empty($block['data'])) {
            $block['data'] = [];
            return $block;
        }

        $keys = array_keys($block['data']);
        $data = $block['data'];
        $block['data'] = [];

        foreach ($data as $key => $field) {
            // Skip ACF metadata keys (e.g. _title => field_xxx)
            if (strpos($key, '_') === 0) {
                continue;
            }

            $acfObj = get_field_object($key);

            if (!empty($acfObj)) {
                if ($acfObj['type'] === 'clone') {
                    $block['data'] = array_merge($block['data'], $acfObj['value'] ?? []);
                } else {
                    $block['data'][$acfObj['name']] = $acfObj['value'];
                }
            } else {
                // Fallback: keep raw value if get_field_object fails
                $block['data'][$key] = $field;
            }
        }

        return apply_filters('undfnd_block_prepared', $block, $this);
    }

    /**
     * Get additional context for template
     * Override in child class to add custom data
     *
     * @param array $block
     * @param bool  $is_preview
     * @param int   $post_id
     * @return array
     */
    protected function getContext(array $block, bool $is_preview, int $post_id): array
    {
        return [];
    }

    /**
     * Render Twig template
     *
     * @param array $block
     * @param array $context
     * @param bool  $is_preview
     */
    protected function renderTemplate(array $block, array $context, bool $is_preview): void
    {
        // Check if block has data, show preview image in editor if empty
        $hasData = !empty(array_filter($block['data'], [$this, 'filterEmpty'], ARRAY_FILTER_USE_BOTH));

        if (!$hasData && is_admin()) {
            $previewTemplate = apply_filters(
                'undfnd_block_preview_template',
                'layout/gutenberg-preview.twig',
                $block,
                $this
            );

            if (Timber::compile($previewTemplate)) {
                Timber::render($previewTemplate, ['image' => $this->name]);
                return;
            }
        }

        // Merge context
        $templateContext = array_merge([
            'block'      => $block,
            'is_preview' => $is_preview,
        ], $context);

        Timber::render($this->templatePath, $templateContext);
    }

    /**
     * Filter empty values
     *
     * @param mixed  $value
     * @param string $key
     * @return bool
     */
    private function filterEmpty($value, $key): bool
    {
        if (is_array($value)) {
            return !empty(array_filter($value, [$this, 'filterEmpty'], ARRAY_FILTER_USE_BOTH));
        }

        return !empty($value) && $key !== 'hn';
    }

    /**
     * Register ACF field group
     */
    public function registerGroupField(): void
    {
        if (empty($this->groupField['fields'])) {
            return;
        }

        register_extended_field_group(
            apply_filters('undfnd_block_field_group', $this->groupField, $this->name)
        );
    }

    /**
     * Set up ACF field group
     * Note: No return type for backward compatibility with legacy blocks
     */
    protected function _setGroupField()
    {
        if (empty($this->groupField['title'])) {
            $this->groupField['title'] = '[Bloc] ' . $this->title;
        }

        if (empty($this->groupField['location'])) {
            $this->groupField['location'] = [
                Location::where('block', 'acf/' . $this->name),
            ];
        }
    }

    /**
     * Legacy alias for prepareBlock
     * Note: No type hints for backward compatibility
     *
     * @param array $block
     * @param int   $post_id
     * @return array
     */
    protected function _prepareBlock($block, $post_id = 0)
    {
        return $this->prepareBlock($block, $post_id);
    }
}
