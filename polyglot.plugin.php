<?php
/**
 * Plugin Name: Polyglot
 * Plugin URI: -
 * Description: Translate Your SLiMS
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerAutoload();

$plugin->registerMenu('system', 'Translatation', __DIR__ . '/pages/translation.php');
