# ride/web-i18n-inline

This module enables a feature to edit translateable labels on your website instead of having to edit translation files.

**Important**: This module adds extra markup to all translateable labels, this might result in strange behaviour. Be careful when using and be aware some errors may occure in the application flow when the translator option is enabled.

### Setup

This modules works with API calls on the ``/api/v1/i18n`` endpoint. So you want to secure this path. Go to the top right user menu and navigate to "access control". Here you can secure the path by adding ``/api**`` or ``/api/v1/i18n**`` to the list.

A user has to be logged in and have the right permissions in order to use this module.

- Whitelist the ``/api/v1/i18n**`` path.
- Enable the ``permission.i18n.inline.translate`` permission.

### Usage

![Translation toggle button](/public/img/img-1.png?raw=true "Toggle the inline translator")

You can now toggle the translator in the top menu in the backend.
