<?php

/**
 * Plugin Name: Invisible Us: Study Abroad Pro
 * Plugin URI: https://invisible.us
 * Description: Sync Study Abroad programs from Terra Dotta and manage them locally. Provides study abroad program profile and search capabilities.
 * Requires PHP: 8.0
 * Requires at least: 5.6
 * Version: 0.1
 * Author: invisibleus
 * Author URI: https://invisible.us
 * Text Domain: nvis-study-abroad
 * License: GPL 2.0 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * ---
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses.
 * 
 * --- 
 *
 * @package NVISStudyAbroad
 * 
 */

namespace InvisibleUs\StudyAbroad;

defined('ABSPATH') || exit;

$includes = [
    '/src/_autoload.php',
    '/src/vendor/autoload.php',
    '/src/acf.php',
    '/src/acf-relationships.php',
    '/src/data-sync.php',
    '/src/assets.php',
    '/src/shortcodes.php',
    '/src/hooks.php',
    '/src/breadcrumbs.php',
    '/src/utils.php',
    '/src/vendor/woocommerce/action-scheduler/action-scheduler.php'
];

foreach ($includes as $subpath) {
    require_once __DIR__ . $subpath;
}

add_action('after_setup_theme', function () {
    require_once __DIR__ . '/src/template-tags.php';
    require_once __DIR__ . '/src/template-tags-shared.php';
});

$plugin = new Plugin();

register_activation_hook(__FILE__, [__NAMESPACE__ . '\Plugin', 'install']);
