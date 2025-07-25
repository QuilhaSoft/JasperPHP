# Refactoring Plan and Progress

This file tracks the progress of the `src` directory refactoring.

## Proposed Directory Structure

- `src/core/`: For core and orchestrator classes.
- `src/elements/`: For visual report components.
- `src/processors/`: For report processing and output generation classes.
- `src/database/`: For database connection and transaction classes (from `ado`).
- `src/exception/`: For exception classes.

## Action Plan

- [x] 1. Create the new directories: `src/core`, `src/elements`, `src/processors`, `src/database`, and `src/exception`.
- [x] 2. Move the files to the new directories.
- [x] 3. Update namespaces in all moved files to reflect the new structure (e.g., `JasperPHP\Element` to `JasperPHP\Elements\Element`).
- [x] 4. Adjust `composer.json` for the PSR-4 autoloader to find classes in the new directories.
- [x] 5. Run `composer dump-autoload` to regenerate autoloader files.
- [x] 6. Run tests (`phpunit`) to ensure the refactoring did not break any existing functionality.
- [x] 7. Refactor all elements in `src/elements/` to use the injected `Report` object.