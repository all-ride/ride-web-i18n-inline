This module enables a quickedit feature to edit translateable labels on your website instead of having to edit translation files.

**This module should only be used in a development environment**

### Setup

At this moment you can just add this module in your ``modules`` folder (you can manually create this folder in the project root) and enable modules to load from this folder in ``parameters.php``.

```bash
cd modules
git clone https://github.com/all-ride/ride-web-i18n-quickadmin.git
```

```php
// application/config/parameters.php:49
"initializers" => array(
    // ...
    new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
    // ...
),
```

Now all translateable labels will be highlighted. By holding alt and clicking a label, you will open a popup in which you can edit the label for all translations.
