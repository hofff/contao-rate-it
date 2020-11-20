# Changelog

## Unreleased

### Fixed

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


[0.3.5]: https://github.com/hofff/contao-rate-it/compare/0.3.4...0.3.5
[0.3.4]: https://github.com/hofff/contao-rate-it/compare/0.3.3...0.3.4
[0.3.3]: https://github.com/hofff/contao-rate-it/compare/0.3.2...0.3.3
[0.3.2]: https://github.com/hofff/contao-rate-it/compare/0.3.1...0.3.2
[0.3.1]: https://github.com/hofff/contao-rate-it/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/hofff/contao-rate-it/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/hofff/contao-rate-it/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/hofff/contao-rate-it/compare/0.1.2...0.2.0
