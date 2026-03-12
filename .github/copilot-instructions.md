# Copilot Instructions for Mendnest Theme

## Overview
This codebase is a custom WordPress theme. It consists of core PHP files for theme structure and styling. There are no build scripts, tests, or advanced developer workflows—changes are made directly to PHP and CSS files.

## Key Files
- `header.php`, `footer.php`: Define the HTML structure for the site's header and footer.
- `index.php`: Main template file for rendering content.
- `functions.php`: Contains theme setup, hooks, and custom functions.
- `style.css`: Main stylesheet; also includes theme metadata for WordPress.

## Patterns & Conventions
- All PHP files use standard WordPress template hierarchy and functions.
- Custom functions and hooks are placed in `functions.php`.
- Avoid direct database queries; use WordPress APIs for data access.
- Theme metadata (name, author, etc.) is defined at the top of `style.css`.
- Use `get_header()` and `get_footer()` to include header/footer in templates.

## Developer Workflow
- Edit PHP/CSS files directly; no build or test steps required.
- To preview changes, reload the site in a local WordPress environment.
- No external dependencies or package managers are used.

## Integration Points
- Theme integrates with WordPress core via standard hooks and template functions.
- No custom REST APIs or external services are referenced.

## Examples
- To add a custom function, edit `functions.php`:
  ```php
  // ...existing code...
  function mendnest_custom_function() {
      // Custom logic
  }
  // ...existing code...
  ```
- To change site layout, modify `header.php`, `footer.php`, or `index.php`.

## Additional Notes
- If you add new files, follow WordPress naming conventions (e.g., `page.php`, `single.php`).
- Keep code readable and maintainable; follow WordPress PHP coding standards.

---
If any section is unclear or missing, please provide feedback for further refinement.
