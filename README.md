# ride/web-i18nquickadmin

This module enables a quickedit feature to edit translateable labels on your website instead of having to edit translation files.

**Important**: This module adds extra markup to all translations, this might result in strange behaviour. Be careful when using and be aware some errors may occure in the application flow.

### Setup

This modules works with API calls on the ``/l10n`` endpoint. So you probably want to secure this path. Go to the top right user menu and navigate to "access control". here you can secure the path by adding ``/l10n**`` in the list.

Only thing left to do is to set role permissions, go to the top right user menu, navigate to "Users", press the array next to "Add new user" and choose "Manage roles". Add the same path (``/l10n**``) for each role which should be able to use this module.

### Usage

You can now toggle the translator in the top menu in the backend.

### Download

At this moment you can just add this module in your ``modules`` folder (you can manually create this folder in the project root).

```bash
cd modules
git clone https://github.com/all-ride/ride-web-i18n-quickadmin.git
```

You might need to enable modules to load from this folder in ``parameters.php``.

```php
// application/config/parameters.php:49
"initializers" => array(
    // ...
    new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
    // ...
),
```

Now all translateable labels will be highlighted. By clicking a label, you will open a popup in which you can edit the label for all translations.
