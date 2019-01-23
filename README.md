# Contao Rate It

This package **hofff/contao-rate-it** is a fork of Contao extension **Rate It**, provided as 
[cgoIT/contao-rate-it-bundle](https://github.com/cgoIT/contao-rate-it-bundle) with following differences:

**Changed**

 * Change namespaces of every class
 * Use microdata templates as default.
 * Do not use client ip to detect votes of the same user. Use session id instead.
 * The configured rating templates is used everywhere where ratings are used.
 * Use Font Awesome as `rating_default` template
 
**Dropped**

 * Drop export feature
 * Drop colorbox/mediabox rating
 * Drop non microdata templates
 * Drop support of heart ratings. Define your icons in a template
 * Gallery picture rating

## Requirements

 * At least Contao 4.6
 * AT least PHP 7.1
