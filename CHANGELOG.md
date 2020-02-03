# Changelog

## Unreleased

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

