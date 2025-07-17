#!/usr/bin/env php
<?php
// Utility prompt function with cancel option
function prompt($question, $default = "")
{
    $answer = readline(
        $question .
            ($default ? " [$default]" : "") .
            " (or type 'q' to quit): ",
    );
    if (strtolower(trim($answer)) === "q") {
        echo "\n👋 Cancelled by user. Exiting...\n";
        exit();
    }
    return $answer ?: $default;
}

// License selector with numbered options
function select_license()
{
    $licenses = [
        "GPL-2.0-or-later" => "General Public License v2.0 or later",
        "GPL-3.0-or-later" => "General Public License v3.0 or later",
        "MIT" => "MIT License",
        "Apache-2.0" => "Apache License 2.0",
        "BSD-3-Clause" => "BSD 3-Clause License",
    ];

    echo "\nChoose a license:\n";
    $i = 1;
    foreach ($licenses as $key => $label) {
        echo "  [$i] $label ($key)\n";
        $i++;
    }

    $choice = readline("Select a license [default 1] (or type 'q' to quit): ");
    if (strtolower(trim($choice)) === "q") {
        echo "\n👋 Cancelled by user. Exiting...\n";
        exit();
    }

    $index =
        (int) $choice >= 1 && (int) $choice <= count($licenses)
            ? (int) $choice - 1
            : 0;
    return array_keys($licenses)[$index];
}

// Fetch latest WordPress version
function get_latest_wp_version()
{
    $response = @file_get_contents(
        "https://api.wordpress.org/core/version-check/1.7/",
    );
    if ($response) {
        $data = json_decode($response, true);
        return $data["offers"][0]["version"] ?? "6.5";
    }
    return "6.5";
}

// 🔧 Start
echo "\n🛠️  WordPress Plugin Scaffold Generator\n";
echo "----------------------------------------\n";

// 🔎 Collect input
$pluginName = prompt("Plugin Name");
$description = prompt("Description");
$author = prompt("Author");
$license = select_license();
$requiresPlugin =
    strtolower(prompt("Requires WooCommerce? (yes/no)", "no")) === "yes";

$version = "1.0.0";
$slug = strtolower(
    trim(preg_replace("/[^a-z0-9\-]/", "", str_replace(" ", "-", $pluginName))),
);
$namespace = str_replace(" ", "", ucwords($pluginName));
$latestWP = get_latest_wp_version();
$vendor = strtolower(preg_replace("/[^a-z0-9]/", "", $author)) ?: "vendor";
$package = strtolower(preg_replace("/[^a-z0-9\-]/", "", $slug));
$baseDir = __DIR__ . "/" . $slug;

// 📋 Summary
echo "\n📋 Summary:";
echo "\nPlugin Name: $pluginName";
echo "\nDescription: $description";
echo "\nAuthor: $author";
echo "\nLicense: $license";
echo "\nRequires WooCommerce: " . ($requiresPlugin ? "Yes" : "No");
echo "\nNamespace: $namespace";
echo "\nSlug: $slug";
echo "\nTested up to: $latestWP";

$confirm = readline("\n\n✅ Continue with these settings? (yes/no): ");
if (strtolower(trim($confirm)) !== "yes") {
    echo "\n👋 Cancelled by user. Nothing was created.\n";
    exit();
}

// 🗂️ Create structure
@mkdir("$baseDir/assets", 0777, true);
@mkdir("$baseDir/src/Core", 0777, true);

// 📦 Create composer.json
$composer = [
    "name" => "$vendor/$package",
    "description" => $description,
    "type" => "wordpress-plugin",
    "license" => $license,
    "authors" => [["name" => $author]],
    "autoload" => [
        "psr-4" => [
            "$namespace\\" => "src/",
        ],
    ],
    "require" => new stdClass(),
];
file_put_contents(
    "$baseDir/composer.json",
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
);

// 📂 Main plugin file
$mainFile = <<<PHP
<?php
/**
 * Plugin Name: $pluginName
 * Description: $description
 * Version: $version
 * Author: $author
 * License: $license
 * Requires at least: 5.5
 * Tested up to: $latestWP
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

add_action('plugins_loaded', function () {
    \$plugin = new {$namespace}\Core\Plugin();
    \$plugin->run();
});
PHP;
file_put_contents("$baseDir/{$slug}.php", $mainFile);

// 🔧 Plugin.php
$pluginClass = <<<PHP
<?php

namespace {$namespace}\Core;

defined('ABSPATH') || exit;

class Plugin {

    public function __construct() {}

    public function run() {
        \$this->load_dependencies();
    }

    private function load_dependencies() {
        new Assets();
    }
}
PHP;
file_put_contents("$baseDir/src/Core/Plugin.php", $pluginClass);

// 🔧 Assets.php
$assetsClass = <<<PHP
<?php

namespace {$namespace}\Core;

defined('ABSPATH') || exit;

class Assets {

    public function __construct() {
        add_action('wp_enqueue_scripts', [\$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [\$this, 'enqueue_admin_assets']);
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('{$slug}-style', plugin_dir_url(__FILE__) . '../../../assets/style.css');
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style('{$slug}-admin-style', plugin_dir_url(__FILE__) . '../../../assets/admin.css');
    }
}
PHP;
file_put_contents("$baseDir/src/Core/Assets.php", $assetsClass);

// 📝 readme.txt
$readme = <<<TXT
=== $pluginName ===
Contributors: $author
Tags: boilerplate, OOP, demo
Requires at least: 5.5
Tested up to: $latestWP
Requires PHP: 7.4
Stable tag: $version
License: $license
License URI: https://opensource.org/licenses/" . urlencode($license) . "

== Description ==
$description

== Installation ==
1. Upload to /wp-content/plugins/
2. Activate from WP Admin.

== Changelog ==
= $version =
* Initial release.

== Requirements ==
TXT;

if ($requiresPlugin) {
    $readme .=
        "\nThis plugin requires WooCommerce to be installed and activated.";
}

file_put_contents("$baseDir/readme.txt", $readme);

// ⚙️ Composer install (optional)
echo "\n🔧 Attempting to install Composer autoloader...\n";
chdir($baseDir);
exec("composer install");

// ✅ Done!
echo "\n✅ Plugin scaffold created at: $baseDir\n\n";

