# Changelog

## Unreleased

### Added

- A new function to delete ratings in the back end.
- Ratings reflect the published state of the corresponding element.
- Add bundle configuration to disable rating types.
- News comments can be rated now. 

### Breaking

 - Renamed `ArticleBaseDcaListener` to `ArticleDcaListener`
 - `BaseDcaListener` does not inherit from `Backend` anymore

### Changed:

- Deleting an elements sets the field `parentstatus` of the corresponding rating item to 'r' (removed).

