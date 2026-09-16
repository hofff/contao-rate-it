# Changelog

## [0.5.0] (2026-09-16)

### Changed

- Replace the legacy `Contao\Module`/`Contao\Hybrid`-based `RateItModule`, `RateItCE` and `RateItTopRatingsModule` with Contao 5 Fragment controllers (`#[AsContentElement]`/`#[AsFrontendModule]`) and port their templates to Twig
- Replace the schema.org microdata in `rateit_default` with JSON-LD via Contao's `add_schema_org()` Twig function
- Replace the legacy `Contao\BackendModule`-based `rateit` back end module with `RateItListController`/`RateItViewController`, a set of dedicated back end routes, and port its templates to Twig
- Use `Doctrine\DBAL\Connection` instead of `Contao\Database` throughout
- Move the back end module's session state to the dedicated `contao_backend` session bag and its language strings to the Symfony translator (`tl_rateit.xlf`, domain `contao_tl_rateit`)
- Remove the now-unused `RateItBackend` helper class

### Upgrade notes

- The `mod_rateit_top_ratings` template's loop variable was renamed from `arrRatings` to `ratings`. If your project has a copy of this template selected via the `tl_module.rateit_template` picker, update it to match.
- The `rateit_top_ratings` module moved from a `$GLOBALS['FE_MOD']` registration to a Symfony DI-tagged Fragment controller. Clear the container cache after upgrading.
- Content-element/module type gating (`hofff_contao_rate_it.types.ce`/`.module`) now happens at container-compile time instead of via a runtime hook. Toggling it requires a container rebuild, not just a page reload.
- The `rateit` back end module no longer responds to `contao/?do=rateit`; it now lives at `contao/rate-it`. The "Allowed back end modules" permission of existing user groups is preserved unchanged, but any bookmarked `do=rateit` links need updating.

### Fixed

- The backend wildcard for the `rateit` content element no longer points its edit link at `tl_module` (the link is now dropped instead of pointing somewhere wrong)
- `RatingListener`'s rating-template fallback no longer resolves to the non-existent `ratit_default` (typo); it now falls back to `rateit_default`
- The back end module's rating list search filter no longer appends a stray `%s` to the `LIKE` pattern, which previously broke the search
- Two non-existent `tl_rateit.*` labels in the back end module (rating list column header, "Apply" button) now resolve correctly

## [0.4.2] (2022-12-21)

### Fixed

 - Fix unparanthesized error with PHP 8.0
 - Fix strict type errors


## [0.4.1] (2022-03-04)

### Changed

- Add doctrine/dbal ^3.0 compatibility

## [0.4.0] (2022-03-04)

### Changed

- Bump requirement of Contao to ^4.9
- Bump requirement of Symfony components to ^4.4 || ^5.1

### Fixed

- Improve future compatibility by fixing deprecations (#20)
- Add missing `itemReviewed` to the schema.org aggregateRating

## [0.3.5] (2020-11-20)

- Fix broken javscript in some browsern (IE 11, Edge 18, and others)

## [0.3.4] (2020-10-26)

- Fix Contao 4.9 compatibility (rootfallback palette)

## [0.3.3] (2020-09-03)

- Use ewb directory from the container configuration

## [0.3.2] (2020-05-29)

- Fix Contao 4.9 compatibility caused of changed security token in Symfony 4.4

## [0.3.1] (2020-03-02)

### Fixed

- Recognize rating setting on current page in *rateit_page_rating* insert tag

## [0.3.0] (2020-03-02)

### Added

- Add inserttag *rateit_page_rating* and custom position for page ratings

## [0.2.1] (2020-02-04)

### Fixed

- News rating label

## [0.2.0] (2020-02-03)

### Added

- A new function to delete ratings in the back end.
- Ratings reflect the published state of the corresponding element.
- Add bundle configuration to disable rating types.
- News comments can be rated now.
- Introduce rating types as abstraction for rating item related logic 
- Add migration command to migrate article ratings to page ratings

### Breaking

 - Renamed `ArticleBaseDcaListener` to `ArticleDcaListener`
 - `BaseDcaListener` does not inherit from `Backend` anymore
 - Rework all dca listeners

### Changed:

- Deleting an elements sets the field `parentstatus` of the corresponding rating item to 'r' (removed).


[0.5.0]: https://github.com/hofff/contao-rate-it/compare/0.4.4...0.5.0
[0.4.0]: https://github.com/hofff/contao-rate-it/compare/0.3.5...0.4.0
[0.3.5]: https://github.com/hofff/contao-rate-it/compare/0.3.4...0.3.5
[0.3.4]: https://github.com/hofff/contao-rate-it/compare/0.3.3...0.3.4
[0.3.3]: https://github.com/hofff/contao-rate-it/compare/0.3.2...0.3.3
[0.3.2]: https://github.com/hofff/contao-rate-it/compare/0.3.1...0.3.2
[0.3.1]: https://github.com/hofff/contao-rate-it/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/hofff/contao-rate-it/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/hofff/contao-rate-it/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/hofff/contao-rate-it/compare/0.1.2...0.2.0
