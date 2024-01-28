<?php
/**
 * WordPress Plugin Framework
 *
 * @package           WordpressPluginFramework
 * @author            Cram42
 * @copyright         2024 Grant Le Roux
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Plugin Framework
 * Plugin URI:        https://github.com/Cram42/wordpress-plugin-framework
 * Version:           0.2.0
 * Requires at least: 6.4.2
 * Requires PHP:      8.2
 * Author:            Cram42
 * Author URI:        https://github.com/Cram42
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       cram42-wpf
 * Update URI:        https://github.com/Cram42/wordpress-plugin-framework
 */

namespace WPPluginFramework;

defined('WPF_NAME') ?: define('WPF_NAME', 'WordPress Plugin Framework');
defined('WPF_FILE') ?: define('WPF_FILE', __FILE__);

require_once 'src/Core/ClassFinder.php';
require_once 'src/constants.php';
require_once 'src/functions.php';

ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src', true);
ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src/Core', true);
ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src/Events', true);
ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src/Logging', true);
ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src/Plugin', true);
ClassFinder::addNamespacePath(__NAMESPACE__, dirname(__FILE__) . '/src/Woo', true);

ClassFinder::addNamespacePath(__NAMESPACE__ . '\Data', dirname(__FILE__) . '/src/Data', true);
