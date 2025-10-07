# Copilot Instructions for AI Agents

## Project Overview

- This is a multi-user ticket purchasing platform built with PHP and SQLite.
- The main business logic is in `src/`, with authentication, database connection, helpers, and trip search modules.
- The `public/` directory contains user-facing PHP pages (login, register, dashboard, search, etc.).
- The `db/` directory holds the SQLite database and schema.
- `config.php` and `init_db.php` are for configuration and database initialization.

## Key Architectural Patterns

- **Separation of Concerns:**
  - `src/` contains reusable logic (e.g., `auth.php`, `db_connect.php`, `tripsearch.php`).
  - `public/` contains entry points for web requests, each page typically includes `header.php` and `footer.php`.
- **Database Access:**
  - All DB access should use the connection logic in `src/db_connect.php`.
  - Schema is defined in `db/schema.sql`.
- **Authentication:**
  - User auth logic is in `src/auth.php`.
  - Session management is handled via PHP sessions, typically started in each public page.

## Developer Workflows

- **Initialize Database:**
  - Run `php init_db.php` to set up the SQLite database using `db/schema.sql`.
- **Run Locally:**
  - Use the provided `Dockerfile` and `docker-compose.yml` for containerized development.
  - Or run with a local PHP server: `php -S localhost:8000 -t public/`.
- **Debugging:**
  - Errors are logged by default to the PHP error log. Check your PHP config for details.

## Project-Specific Conventions

- Always use prepared statements for database queries (see `src/db_connect.php` for examples).
- Include `header.php` and `footer.php` in all public-facing pages for consistent layout.
- Store all images in the `images/` directory.
- Use relative paths for includes (e.g., `include '../src/auth.php';`).

## Integration Points

- No external APIs are used; all logic and data are local.
- Docker is used for environment consistency.

## Example: Adding a New Page

1. Create a new PHP file in `public/`.
2. Start with `session_start();` and include `header.php`.
3. Use logic from `src/` as needed.
4. End with `footer.php`.

---

For more details, see the `README.md` and source files in `src/` and `public/`.
