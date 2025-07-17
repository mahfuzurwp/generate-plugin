# 🛠️ WordPress Plugin Scaffold Generator

A simple interactive PHP script to generate a modern WordPress plugin structure with:

- PSR-4 autoloading via Composer
- OOP with namespaced classes
- Assets and Core class folders
- Optional WooCommerce support
- Up-to-date `readme.txt` metadata

---

## 🚀 Getting Started

### ✅ Requirements

- PHP 7.4 or higher
- Composer (globally installed)
- Internet access (to fetch latest WP version)

---

### 📥 Usage

```bash
php generate-plugin.php
```
You’ll be prompted to enter:
- Plugin Name
- Description
- Author
- License (select from a list)
- Whether it requires WooCommerce

After confirmation, it will:
- Scaffold a plugin folder with your input
- Create:
  - composer.json with PSR-4 autoloading
  - readme.txt with metadata
  - OOP structure under src/
  - Basic assets folder
  - Run composer install (if Composer is available)

 # 🧪 Example

 ```bash
php generate-plugin.php
```
 ```bash
Plugin Name: Cool Plugin
Description: A modern starter plugin
Author: John Doe
Choose a license:
  [1] GPL-2.0-or-later ...
  ...
Select a license [default 1]: 3
Requires WooCommerce? (yes/no): yes

📋 Summary:
Plugin Name: Cool Plugin
Description: A modern starter plugin
...
✅ Continue with these settings? (yes/no): yes

✅ Plugin scaffold created at: ./cool-plugin
```

# 📂 Plugin Structure

```
cool-plugin/
├── assets/
├── composer.json
├── cool-plugin.php
├── readme.txt
└── src/
    └── Core/
        ├── Plugin.php
        └── Assets.php
```

# 📦 Composer Autoloading

After generation, you can require classes like:
```
use CoolPlugin\Core\Plugin;
```

# 📌 Notes

- Cancel anytime during setup by typing q
- Default values are provided for quick entry
- Autoloading is set up with Composer, so you can require_once vendor/autoload.php in your main plugin file

# 🙌 Contributing

Pull requests and issues welcome!
