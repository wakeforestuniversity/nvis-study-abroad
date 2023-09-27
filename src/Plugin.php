<?php

namespace InvisibleUs\StudyAbroad;

class Plugin {
    public static $name = 'nvis-study-abroad';
    public static $path = '';
    public static $url = '';
    public static $template_path = '/templates';
    private static $_init = false;

    public static $options_page = [];

    public static $options_page_slug = 'nvis-study-abroad-settings';

    public static $options_page_parent = 'edit.php?post_type=' . Program::POST_TYPE;

    public static $field_groups = [];

    public static $labels = [];

    /**
     * Stores the list of post types available in this plugin.
     *
     * @var array
     */
    private static $post_types = [];

    /**
     * Stores the list of post types currently enabled and registered by this plugin.
     *
     * @var array
     */
    private static $post_types_enabled = [];

    /**
     * Stores the list of taxonomies available in this plugin.
     *
     * @var array
     */
    private static $taxonomies = [];

    /**
     * Stores the list of taxonomies currently enabled and registered by this plugin.
     *
     * @var array
     */
    private static $taxonomies_enabled = [];

    public function __construct() {
        if (self::$_init) {
            return;
        }

        self::$path = dirname(__DIR__);
        self::$url = plugins_url(self::$name);
        self::$template_path = self::$path . self::$template_path;

        self::setup_options_page();
        self::setup_labels();
        self::setup_field_group();

        add_action('init', [self::class, 'plugin_init'], 1);
        add_action('admin_init', [self::class, 'admin_init'], 1);
        add_action('after_setup_theme', [self::class, 'after_setup_theme'], 1);

        self::$_init = true;
    }

    public static function install() {
        self::register_content_model();
        self::install_capabilities();
        flush_rewrite_rules();
    }

    public static function install_capabilities() {
        $role = get_role('administrator');
        $args = (object) Program::get_instance()->args;
        $args->capabilities = [];

        $caps = get_post_type_capabilities($args);

        foreach ($caps as $cap) {
            $role->add_cap($cap);
        }
    }

    public static function plugin_init(): void {
        load_plugin_textdomain(
            self::$name,
            false,
            self::$name . '/languages/'
        );

        self::register_content_model();
        self::register_custom_blocks();
        self::setup_template_manager();
    }

    public static function admin_init(): void {
        add_action('admin_notices', [__NAMESPACE__ . '\AdminNotice', 'render_delayed']);
        SyncActionManager::init();
    }

    public static function after_setup_theme() {
        if (!isset($GLOBALS['content_width'])) {
            $GLOBALS['content_width'] = 1200;
        }
    }

    protected static function setup_options_page(): void {
        self::$options_page = [
            'page_title'  => __('Study Abroad Pro Settings', 'nvis-career-profiles'),
            'menu_title'  => __('Settings', 'nvis-career-profiles'),
            'menu_slug'   => self::$options_page_slug,
            'capability'  => 'manage_options',
            'parent_slug' => self::$options_page_parent,
            'position'    => 7,
            'redirect'    => false,
        ];
    }

    // Can not be called before `current_screen` hook.
    public static function is_options_page() {
        if (!is_admin()) {
            return;
        }

        $screen = get_current_screen();
        $options_page_id = sprintf('%s_page_%s', Program::POST_TYPE, self::$options_page_slug);

        return $screen->id === $options_page_id;
    }

    protected static function setup_labels(): void {
        self::$labels = require_once self::$path . '/src/label-set-plugin.php';
    }

    protected static function setup_field_group(): void {
        self::$field_groups[] = require_once self::$path . '/src/field-group-plugin.php';

        return;
    }

    public static function get_field_group(): array {
        return static::$field_groups[0] ?? [];
    }

    public static function register_content_model(): void {
        Program::get_instance()->register();
        self::$post_types[] = Program::POST_TYPE;
        self::$post_types_enabled[] = Program::POST_TYPE;

        Location::get_instance()->register();
        self::$taxonomies[] = Location::TAXONOMY;
        self::$taxonomies_enabled[] = Location::TAXONOMY;

        Subject::get_instance()->register();
        self::$taxonomies[] = Subject::TAXONOMY;
        self::$taxonomies_enabled[] = Subject::TAXONOMY;
        
        self::$taxonomies[] = Sponsor::TAXONOMY;
        
        if (self::get_option('sponsor_enable', true)) {
            Sponsor::get_instance()->register();
            self::$taxonomies_enabled[] = Sponsor::TAXONOMY;
        }

        Term::get_instance()->register();
        self::$taxonomies[] = Term::TAXONOMY;
        self::$taxonomies_enabled[] = Term::TAXONOMY;

        return;
    }

    /**
     * Gets the list of post types available or registered by this plugin.
     *
     * @since 0.1.0
     *
     * @param bool $enabled_only Whether or not to only return enabled post types.
     * @return array An array of post type keys.
     */
    public static function post_types(bool $enabled_only = true): array {
        if ($enabled_only) {
            return self::$post_types_enabled;
        }

        return self::$post_types;
    }

    /**
     * Gets the list of taxonomies available or registered by this plugin.
     *
     * @since 0.1.0
     *
     * @param bool $enabled_only Whether or not to only return enabled taxonomies.
     * @return array An array of taxonomy keys.
     */
    public static function taxonomies(bool $enabled_only = true): array {
        if ($enabled_only) {
            return self::$taxonomies_enabled;
        }

        return self::$taxonomies;
    }

    /**
     * Returns an associative array of taxonomies indexed by their search filter name.
     *
     * @return array The search filter to taxonomy map.
     */
    public static function get_tax_filters_map(): array {
        static $map = null;

        if (!$map) {
            $filters = array_map(
                function($a) {
                    return str_replace(
                        ['nvis_', '_'],
                        ['', '-'],
                        $a
                    );
                },
                self::$taxonomies
            );

            $map = array_combine($filters, self::$taxonomies);
        }

        return $map;
    }

    public static function register_custom_blocks(): void {
        return;
    }

    public static function setup_template_manager(): void {
        $templates = [
            [
                'name'     => 'single-program',
                'callback' => 'is_singular',
                'args'     => [Program::POST_TYPE]
            ],
            [
                'name'     => 'archive-program',
                'callback' => 'is_post_type_archive',
                'args'     => [Program::POST_TYPE]
            ],
            [
                'name'     => 'taxonomy-location',
                'callback' => 'is_tax',
                'args'     => [Location::TAXONOMY]
            ],
            [
                'name'     => 'taxonomy-term',
                'callback' => 'is_tax',
                'args'     => [Term::TAXONOMY]
            ]
        ];

        $NVIS_TemplateManager = new TemplateManager(
            self::$template_path,
            self::$name,
            $templates
        );

        add_filter('template_include', [$NVIS_TemplateManager, 'maybe_use_template'], PHP_INT_MAX);

        return;
    }

    public static function get_label(string $label_key): string {
        $label = self::get_option('label_' . $label_key);

        if (!$label) {
            $label = self::$labels[$label_key] ?? $label_key;
        }

        $label = apply_filters('nvis/get_label', $label, $label_key, 'study-abroad');

        return $label;
    }

    /**
     * Retrieves a plugin setting.
     *
     * A wrapper around get_option that handles:
     * 
     *  1. Prefixing the setting name.
     *  2. Allowing override by a defined constant.
     *  3. Allowing filtering.
     *
     * @param string $option The name of the setting.
     * @param string $default The value to return if the option is not found.
     * @return mixed The value of the setting.
     */
    public static function get_option(string $option, $default=null) {
        $const = 'NVIS_' . strtoupper($option);
        
        if (defined($const)) {
            $value = constant($const);
        } else {
            $value = get_option('options_nvis_' . $option, $default);
        }

        /**
         * Filters the value of an option. The last part is the name of the option.
         *
         * @since 0.1
         *
         * @param $value The value of the option.
         */
        return apply_filters("nvis/studyabroad/options/{$option}", $value);
    }

    /**
     * Gets an option that can be overrided by a post meta field.
     *
     * @param string $option The name of the option to retrieve.
     * @param mixed $post The ID or WP_Post object of the post to check. Defaults to the current post.
     * @return mixed The requested value.
     */
    public static function get_overridable_option(string $option, $post = null) {
        $post = get_post($post);
        $value = get_field($option, $post);

        /*
        The mismatch between get_field (which returns formatted data based on
        the field type) and get_option (which returns raw field value) is
        what's causing the issue with default lead text to render without a
        paragraph tag wrapping it.

        Really, we should only be using equivalent functions. So, get_post_meta
        with get_option, or get_field for both.

        Sadly, this is a deep architectural issue that arose from Delicious
        Brains' decision to stop allowing get_field before acf/init had been
        called.
        */

        if (!$value) {
            $value = self::get_option($option);
        }

        return $value;
    }
}
