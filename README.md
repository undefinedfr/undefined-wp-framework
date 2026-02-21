# Undefined WP Framework

Mini Framework MVC pour WordPress basé sur Timber/Twig.

## Installation

```bash
composer require undefinedfr/undefined-wp-framework
```

Dans votre `functions.php` :

```php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/undefinedfr/undefined-wp-framework/autoload.php';
```

## Dépendances

- [Timber](https://timber.github.io/docs/) ^2.0
- [Extended ACF](https://github.com/vinkla/extended-acf) ^14.2

## Structure recommandée

```
theme/
├── app/
│   ├── Block/           # Blocs ACF Gutenberg (legacy)
│   ├── blocks/          # Blocs Timber-style (nouveau)
│   ├── Command/         # Commandes WP-CLI
│   ├── PostType/        # Custom Post Types
│   ├── Taxonomy/        # Taxonomies personnalisées
│   ├── Controllers/     # Contrôleurs MVC
│   ├── Actions/         # Actions WordPress
│   ├── Filters/         # Filtres WordPress
│   └── Ajax/            # Fonctions Ajax
├── templates/           # Templates Twig
├── public/
│   └── assets/          # CSS, JS, images
└── functions.php
```

---

## Custom Post Types

Créez vos CPT dans `app/PostType/` en étendant `PostType` :

```php
<?php
namespace App\PostType;

use Undefined\Core\PostType\PostType;

class Project extends PostType
{
    public static function getPostType(): string
    {
        return 'project';
    }

    protected static function getPostTypeConfig(): array
    {
        return [
            'singulier'  => 'Projet',
            'pluriel'    => 'Projets',
            'feminin'    => false,
            'menu_icon'  => 'dashicons-portfolio',
            'supports'   => ['title', 'editor', 'thumbnail'],
            'taxonomies' => ['project_category'],
        ];
    }

    // Hook appelé à la sauvegarde
    public static function onSavePost($post_id): void
    {
        // Logique personnalisée
    }
}
```

### Options de configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `singulier` | string | ucfirst(slug) | Nom singulier |
| `pluriel` | string | singulier + 's' | Nom pluriel |
| `feminin` | bool | false | Genre féminin (labels FR) |
| `public` | bool | true | Accessible publiquement |
| `show_in_rest` | bool | true | API REST & Gutenberg |
| `menu_icon` | string | null | Dashicon ou SVG |
| `supports` | array | [...] | Fonctionnalités supportées |
| `taxonomies` | array | ['post_tag', 'category'] | Taxonomies associées |
| `rewrite` | string/array | slug | Règles de réécriture |

---

## Custom Taxonomies

Créez vos taxonomies dans `app/Taxonomy/` :

```php
<?php
namespace App\Taxonomy;

use Undefined\Core\Taxonomy\Taxonomy;

class ProjectCategory extends Taxonomy
{
    public static function getTaxonomy(): string
    {
        return 'project_category';
    }

    protected static function getTaxonomyConfig(): array
    {
        return [
            'name'        => 'Catégorie',
            'pluriel'     => 'Catégories',
            'feminin'     => true,
            'post_types'  => ['project'],
            'hierarchical' => true,
        ];
    }

    // Hook appelé à la sauvegarde d'un terme
    public static function onSaveTerm($term_id, $tt_id, $update, $args): void
    {
        // Logique personnalisée
    }
}
```

---

## Blocs Gutenberg (ACF)

### Structure Timber-style (recommandée)

```
app/blocks/
└── hero/
    ├── block.json      # Métadonnées (optionnel)
    ├── hero.php        # Classe du bloc
    ├── hero.twig       # Template
    ├── hero.css        # Styles (optionnel)
    ├── hero.js         # Scripts (optionnel)
    └── icon.svg        # Icône (optionnel)
```

### Exemple de bloc

```php
<?php
namespace App\blocks\hero;

use Undefined\Core\Block\Block;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Image;

class Hero extends Block
{
    public $name = 'hero';
    public $title = 'Hero Banner';
    public $description = 'Bannière principale';
    public $category = 'custom';
    public $icon = 'cover-image';
    public $mode = 'preview';
    public $keywords = ['banner', 'header'];

    protected function _setGroupField(): void
    {
        parent::_setGroupField();

        $this->groupField['fields'] = [
            Text::make('Titre', 'title')
                ->required(),
            Text::make('Sous-titre', 'subtitle'),
            Image::make('Image', 'image')
                ->returnFormat('array'),
        ];
    }

    // Données pour la preview dans l'éditeur
    protected function getExampleData(): array
    {
        return [
            'title' => 'Exemple de titre',
            'subtitle' => 'Sous-titre',
        ];
    }

    // Contexte additionnel pour le template
    protected function getContext(array $block, bool $is_preview, int $post_id): array
    {
        return [
            'custom_data' => 'value',
        ];
    }
}
```

### Template Twig

```twig
{# app/blocks/hero/hero.twig #}
<section class="hero-block" {% if block.anchor %}id="{{ block.anchor }}"{% endif %}>
    {% if block.data.title %}
        <h1>{{ block.data.title }}</h1>
    {% endif %}

    {% if block.data.subtitle %}
        <p>{{ block.data.subtitle }}</p>
    {% endif %}

    {% if block.data.image %}
        <img src="{{ block.data.image.url }}" alt="{{ block.data.image.alt }}">
    {% endif %}
</section>
```

### Hooks disponibles

| Hook | Description |
|------|-------------|
| `undfnd_block_icon_paths` | Chemins de recherche pour l'icône |
| `undfnd_block_assets` | Assets CSS/JS du bloc |
| `undfnd_block_args` | Arguments d'enregistrement du bloc |
| `undfnd_block_prepared` | Données préparées du bloc |
| `undfnd_block_field_group` | Groupe de champs ACF |
| `undfnd_block_preview_template` | Template de preview vide |

---

## Controllers & Routing

### Créer un contrôleur

```php
<?php
// app/Controllers/AccountController.php

use Undefined\Core\Controllers\AbstractController;

class AccountController extends AbstractController
{
    // Action par défaut: /account/
    public function indexAction()
    {
        if (!is_user_logged_in()) {
            $this->_redirect('/login');
        }

        $this->_setTitle('Mon compte');
        $this->_setData([
            'user' => wp_get_current_user(),
        ]);

        $this->render();
    }

    // Action: /account/orders
    public function ordersAction()
    {
        $this->_setData([
            'orders' => $this->getOrders(),
        ]);

        $this->render();
    }

    // POST sur /account/orders
    public function ordersPostAction()
    {
        // Traitement du formulaire
        $this->_addNotice('success', 'Commande mise à jour');
        $this->_redirect('/account/orders');
    }
}
```

### Définir les routes

```php
<?php
// app/Router.php

use Undefined\Core\Router;

class AppRouter extends Router
{
    public function __construct()
    {
        parent::__construct();

        // Route simple
        $this->addRule('account');

        // Route avec section
        $this->addRule('account', 'orders');

        // Route avec paramètres dynamiques
        $this->addRule('account', 'order', [
            'order_id' => '([0-9]+)'
        ]);
    }
}
```

### Méthodes du contrôleur

| Méthode | Description |
|---------|-------------|
| `render($data)` | Rend le template Twig |
| `_setTitle($title)` | Définit le titre de la page |
| `_setData($data, $merge)` | Ajoute des données au contexte |
| `_redirect($url)` | Redirige vers une URL |
| `_addNotice($type, $message)` | Ajoute une notification session |
| `getNotices()` | Récupère les notifications |
| `isCurrentRoute($route)` | Vérifie la route actuelle |

---

## Request

Gestion des requêtes HTTP inspirée de Symfony :

```php
use Undefined\Core\Request;

$request = Request::createFromGlobals();

// Accès aux paramètres
$request->query->get('page');           // $_GET['page']
$request->request->get('email');        // $_POST['email']
$request->cookies->get('session_id');   // $_COOKIE['session_id']
$request->server->get('REQUEST_METHOD');
$request->headers->get('Content-Type');

// Méthode générique (cherche dans attributes, query, request, session)
$request->get('param', 'default');

// Contenu brut
$request->getContent();
```

### ParameterBag - Méthodes de sanitization

```php
$request->request->getText('name');      // sanitize_text_field
$request->request->getEmail('email');    // sanitize_email
$request->request->getTextarea('bio');   // sanitize_textarea_field
$request->request->getUrl('website');    // esc_url_raw
$request->request->getFilename('file');  // sanitize_file_name
$request->request->getKey('slug');       // sanitize_key
$request->request->getSlug('title');     // sanitize_title

// Méthodes standard
$request->request->get('key', 'default');
$request->request->getInt('page', 1);
$request->request->getBoolean('active');
$request->request->getAlpha('code');
$request->request->getAlnum('ref');
$request->request->getDigits('phone');
$request->request->has('field');
$request->request->all();
```

---

## Actions & Filters

### Actions

```php
<?php
namespace App\Actions;

use Undefined\Core\Actions;

class ThemeActions extends Actions
{
    protected $_hooks = [
        // Format simple
        'setupTheme' => 'after_setup_theme',

        // Format avancé
        'loadScripts' => [
            'hook'          => 'wp_enqueue_scripts',
            'priority'      => 20,
            'accepted_args' => 1,
            'remove_on_admin' => true,
        ],
    ];

    public function theme_setupTheme()
    {
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        register_nav_menus([
            'primary' => 'Menu principal',
        ]);
    }

    public function theme_loadScripts()
    {
        wp_enqueue_script('custom-script', '...');
    }
}
```

### Filters

```php
<?php
namespace App\Filters;

use Undefined\Core\Filters;

class ThemeFilters extends Filters
{
    protected $_hooks = [
        'excerpt' => 'excerpt_length',
        'mimeTypes' => [
            'hook'     => 'upload_mimes',
            'priority' => 10,
        ],
    ];

    public function theme_excerpt($length)
    {
        return 30;
    }

    public function theme_mimeTypes($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
}
```

---

## Ajax

```php
<?php
namespace App\Ajax;

use Undefined\Core\Ajax;

class FormAjax extends Ajax
{
    protected $_ajaxFunctions = [
        'submit_contact_form',
        'load_more_posts',
    ];

    public function submit_contact_form()
    {
        check_ajax_referer('undefined_ajax_nonce', 'nonce');

        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        // Traitement...

        wp_send_json_success(['message' => 'Email envoyé']);
    }

    public function load_more_posts()
    {
        $page = intval($_POST['page']);

        $posts = get_posts([
            'posts_per_page' => 10,
            'offset' => ($page - 1) * 10,
        ]);

        wp_send_json_success(['posts' => $posts]);
    }
}
```

### Appel côté JavaScript

```javascript
fetch(args.ajax_url + '?is_ajax=1', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'submit_contact_form',
        nonce: args.ajax_nonce,
        email: 'test@example.com',
        message: 'Hello'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Assets

```php
<?php
namespace App\Assets;

use Undefined\Core\Assets\Assets;

class ThemeAssets extends Assets
{
    protected $_scripts = [
        'vendor' => [
            'handle'   => 'vendor',
            'filename' => 'vendor.js',
            'deps'     => ['jquery'],
            'version'  => '1.0',
            'infooter' => false,
        ],
        'app' => [
            'handle'   => 'app',
            'filename' => 'app.js',
            'deps'     => ['vendor'],
            'version'  => '1.0',
            'infooter' => true,
            'args'     => [], // Données localisées
        ],
    ];

    protected $_styles = [
        'theme' => [
            'handle'   => 'theme',
            'filename' => 'theme.css',
            'deps'     => [],
            'version'  => '1.0',
        ],
    ];
}
```

### Cache-busting avec hash.json

Créez `public/assets/hash.json` pour le versioning :

```json
{
    "app.js": "js/app.a1b2c3d4.js",
    "theme.css": "css/theme.e5f6g7h8.css"
}
```

### Hook pour le nom de l'objet localisé

```php
add_filter('undfnd_assets_app_object_name', function($name) {
    return 'myApp'; // window.myApp au lieu de window.args
});
```

---

## Mail

Envoi d'emails avec templates Twig :

```php
use Undefined\Core\Mails\Mail;

Mail::init()
    ->to('client@example.com')
    ->cc('copy@example.com')
    ->bcc('hidden@example.com')
    ->from('Mon Site <noreply@example.com>')
    ->subject('Confirmation de commande #{{ order_id }}')
    ->template('emails/order-confirmation.twig', [
        'order_id'   => 123,
        'items'      => $orderItems,
        'total'      => '99.00€',
        '_html_content' => '<strong>HTML non échappé</strong>',
    ])
    ->attach('/path/to/invoice.pdf')
    ->send();
```

### Variables globales disponibles

- `{{ blogname }}` - Nom du site
- `{{ home_url }}` - URL du site
- `{{ stylesheet_uri }}` - URL du thème
- `{{ blogdescription }}` - Description du site

### Template email

```twig
{# templates/emails/order-confirmation.twig #}
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
    </style>
</head>
<body>
    <h1>Merci pour votre commande !</h1>
    <p>Commande #{{ order_id }}</p>

    <table>
        {% for item in items %}
        <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.price }}</td>
        </tr>
        {% endfor %}
    </table>

    <p><strong>Total: {{ total }}</strong></p>
</body>
</html>
```

---

## WP-CLI Commands

```php
<?php
namespace App\Command;

class ImportCommand
{
    public $name = 'import';

    /**
     * Import des produits
     *
     * ## OPTIONS
     *
     * [--file=<file>]
     * : Fichier CSV à importer
     *
     * ## EXAMPLES
     *
     *     wp import products --file=products.csv
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function products($args, $assoc_args)
    {
        $file = $assoc_args['file'] ?? 'import.csv';

        \WP_CLI::log("Import depuis $file...");

        // Logique d'import...

        \WP_CLI::success('Import terminé !');
    }
}
```

Usage :
```bash
wp import products --file=data.csv
```

---

## Security

Le module Security désactive automatiquement l'endpoint REST `/wp/v2/users` pour éviter l'énumération des utilisateurs.

```php
// Désactivé automatiquement :
// GET /wp-json/wp/v2/users
// GET /wp-json/wp/v2/users/1
```

---

## App Singleton

Classe de base pour créer une instance unique de votre application :

```php
<?php
namespace App;

use Undefined\Core\App;

class MyApp extends App
{
    protected function __construct()
    {
        // Initialisation
    }
}

// Usage
$app = \App\MyApp::getInstance();
```

### Helper global

```php
// Accès rapide via la fonction app()
$app = app();
```

---

## Hooks & Filtres de référence

### Blocs

| Hook | Args | Description |
|------|------|-------------|
| `undfnd_acf_blocks_path` | `$path` | Chemin des blocs ACF legacy |
| `undfnd_gutenberg_blocks_path` | `$path` | Chemin des blocs Timber |
| `undfnd_block_namespaces` | `$namespaces` | Namespaces de recherche |
| `undfnd_block_icon_paths` | `$paths, $block` | Chemins des icônes |
| `undfnd_block_assets` | `$assets, $block` | Assets du bloc |
| `undfnd_block_args` | `$args, $block` | Args d'enregistrement |
| `undfnd_block_prepared` | `$block, $this` | Block préparé |
| `undfnd_block_field_group` | `$group, $name` | Champs ACF |

### CPT & Taxonomies

| Hook | Args | Description |
|------|------|-------------|
| `undfnd_cpt_save_post_priority` | `$priority, $post_type` | Priorité du hook save |
| `undfnd_cpt_save_post_accepted_args` | `$args, $post_type` | Nombre d'args |

### Assets

| Hook | Args | Description |
|------|------|-------------|
| `undfnd_hash_assets_path` | `$path` | Chemin du fichier hash.json |
| `undfnd_assets_{handle}_object_name` | `$name` | Nom de l'objet JS |

### Templating

| Hook | Args | Description |
|------|------|-------------|
| `timber_default_options` | `$options` | Options par défaut |
| `timber_global_context_data` | `$data` | Données globales |
| `undfnd_controller_wp_title_action` | `$action` | Action pour wp_title |

---

## Licence

GPL-3.0-or-later

## Auteur

Nicolas RIVIERE - [undefined.fr](https://www.undefined.fr)
