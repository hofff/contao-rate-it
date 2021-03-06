# Contao Rate It

This package **hofff/contao-rate-it** is a fork of Contao extension **Rate It**, provided as 
[cgoIT/contao-rate-it-bundle](https://github.com/cgoIT/contao-rate-it-bundle) with following differences:

**Added**

 * Disable ratings for types which should not be used
 * Migration command to migrate article ratings to page ratings
 * Custom position for page ratings and `rateit_page_rating` insert tag

**Changed**

 * Change namespaces of every class
 * Use microdata templates as default.
 * Do not use client ip to detect votes of the same user. Use session id instead.
 * The configured rating templates is used everywhere where ratings are used.
 * Use Font Awesome as `rating_default` template
 * The rating for each element is retained when the element gets deleted. Ratings can be deleted in the backend.
 
**Dropped**

 * Drop export feature
 * Drop colorbox/mediabox rating
 * Drop non microdata templates
 * Drop support of heart ratings. Define your icons in a template
 * Drop gallery picture rating
 * Drop faq rating

## Requirements

 * At least Contao 4.6
 * AT least PHP 7.1
 * Integrated Font Awesome 5 (or a custom `rating_*` template)

## Configuration

You man want to configure the bundle using the application configuration (`config/config.yml` or `app/config/config.yml`)
depending on your project. Right now you are only able to disable supported content types.

```
# Default configuration
hofff_contao_rate_it:
    types:
        page:                 true
        article:              true
        news:                 true
        module:               true
        ce:                   true
```

If you use Contao just as a Symfony bundle (not a managed edition), don't forget to add the routing information to your `routes.yaml`:
```
# routes.yaml
HofffContaoRateItBundle:
    resource: '@HofffContaoRateItBundle/Resources/config/routing.xml'
```

## How to

### Migrate article ratings to page ratings

The migration command migrates all article ratings to the corresponding pages

 - Enables rating for pages with rated articles
 - Create rating items for pages if not exist
 - Reassign article ratings to the page
 - Deletes article rating items
 
1. Backup your database!
2. Run vendor/bin/contao-console hofff-rate-it:migrate
