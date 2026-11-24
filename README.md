# Employee Service Request System

A role-based internal service desk built with CodeIgniter 4 and MySQL. Employees submit requests to IT, HR, or Facilities; agents work requests assigned to their service category; administrators manage users, SLAs, assignments, and operational reporting.

## Features

- Employee request submission, request history, comments, and activity timeline.
- Category-based assignment to an agent and role-based request visibility.
- Four-step status workflow: `open` → `in_progress` → `resolved` → `closed`.
- Category SLA deadlines, with high-priority requests receiving half the standard SLA.
- Overdue indicators for requests that are past their due date and not resolved or closed.
- Administrative dashboard with request counts, category breakdown, and recent activity.
- User and category management, including agent-to-category assignment.

## Requirements

- PHP 8.2 or newer with `intl`, `mbstring`, `mysqli`, and `mysqlnd`. The `sqlite3` extension is also required for the database tests.
- MySQL or MariaDB.
- Composer.
- Apache with `mod_rewrite` enabled for the recommended XAMPP setup.

## Local setup (XAMPP)

1. Create a MySQL database, for example `service_requests`.
2. Copy `.env.example` to `.env` and configure the application URL and database credentials:

   ```ini
   app.baseURL = 'http://localhost/service-requests/public/'
   database.default.hostname = localhost
   database.default.database = service_requests
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   ```

   Use credentials appropriate for your local environment. For production, point Apache's document root to the project's `public` directory and configure a secure HTTPS base URL.

3. Install dependencies if needed:

   ```sh
   composer install
   ```

4. Run the migrations and seed the local demonstration accounts:

   ```sh
   php spark migrate
   php spark db:seed MainSeeder
   ```

5. Open the configured base URL in a browser.

## Demonstration accounts

The seeder creates one administrator, an IT/HR/Facilities agent, and employee accounts. The shared local-demo password is `password123`.

| Role | Email |
| --- | --- |
| Administrator | `admin@company.com` |
| IT agent | `it@company.com` |
| HR agent | `hr@company.com` |
| Facilities agent | `facility@company.com` |
| Employee | `ali@company.com` |

These credentials are for local demonstration only. Change or remove them before deploying the application.

## SLA behavior

The deadline is calculated when a request is submitted from its category's configured SLA. Low- and medium-priority requests use the full SLA; high-priority requests use half. A request is overdue only while its status is not `resolved` or `closed` and its deadline has passed.

An agent must be assigned to the same category as a request. If a category has no agent, request submission asks the employee to contact an administrator rather than creating an unassigned request.

## Tests

Run the PHPUnit suite with SQLite enabled:

```sh
composer test
```
