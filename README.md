# OJT Tracker (PHP Edition)

Full-stack On-the-Job Training hours tracker with PHP backend & MySQL, Tailwind CSS frontend.

## Folder Structure

- `config/` - configuration (database)
- `models/` - data model classes
- `views/` - HTML/PHP view templates
- `helpers/` - utility functions (CSRF, role check, sanitization)
- `public/` - web accessible files (entrypoint `index.php`, assets)
- `sql/` - database schema

## Setup

1. Ensure PHP (7.4+) and MySQL are installed (e.g., XAMPP). Place project in your web root (e.g., `htdocs/ojt-tracker`).
2. Create the database and tables using the schema file:
   ```sql
   SOURCE /path/to/ojt-tracker/sql/schema.sql;
   ```
3. Update database connection in `config/database.php` if credentials differ.
4. Start your web server and navigate to `http://localhost/ojt-tracker/public/index.php`.
5. Register a student account through the UI.

## Usage

- Students can log hours (AJAX submission), view history with client-side filtering, delete entries, track progress, and generate printable reports with CSV export.
- Each user can upload a profile photo and specify their course/program; these appear on the dashboard and account pages.

- Toast notifications now support success/error/info icons, manual close buttons, and automatically display the full server message (for example, the exact hours and date when a log is recorded).

**Database upgrade:** the `users` table now includes `course` and `photo` columns. Run the following SQL on existing databases:

```sql
ALTER TABLE users
  ADD COLUMN course VARCHAR(100) DEFAULT '',
  ADD COLUMN photo VARCHAR(255) DEFAULT NULL;
```

Also ensure the `public/uploads` directory exists and is writable by the webserver; profile pictures are stored there automatically.


### Lateness penalty
Logging a later clock‑in applies a small penalty: 1 minute late counts as a 0.5‑hour deduction, 10 minutes late counts as roughly 1 hour. This encourages punctuality.
## Security

- CSRF tokens on forms
- Prepared statements via PDO
- Sanitization with `htmlspecialchars` for output
- Role-based access control

## Extending

This skeleton can be enhanced with AJAX, charts, PDF export, dark mode, and email notifications as needed.