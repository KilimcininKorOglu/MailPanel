# MailPanel

A lightweight PHP web application for managing [iRedMail](https://www.iredmail.org/) users, domains, and admin accounts. Supports both OpenLDAP and MySQL/MariaDB backends with optional Amavisd, Fail2ban, and iRedAPD integrations. Provides a full-featured admin interface with role-based access control.

Built with vanilla PHP 8.1+ -- no framework, no ORM, no template engine dependency. Uses `vlucas/phpdotenv` for environment configuration.

```
kilimcininkoroglu/mailpanel v0.3.0
```

### Supported Backends

| iRedMail Backend | Supported |
| ---------------- | --------- |
| OpenLDAP         | Yes       |
| MySQL/MariaDB    | Yes       |
| PostgreSQL       | No        |

Select the backend via `MAILPANEL_BACKEND` environment variable (`ldap` or `mysql`).

## Requirements

- PHP 8.1 or higher
- PHP LDAP extension (`ext-ldap`) -- for LDAP backend
- PHP PDO MySQL extension (`ext-pdo_mysql`) -- for MySQL backend
- [Composer](https://getcomposer.org/) (included as `composer.phar`)
- An iRedMail server with OpenLDAP or MySQL/MariaDB backend

## Installation

```bash
git clone <repository-url>
cd mailpanel
php composer.phar install
cp .env.example .env
```

Edit `.env` with your backend choice and connection details, then start the application (see [Running](#running)).

## Configuration

All settings use the `MAILPANEL_` prefix and are loaded from `.env` or `.env.prod` via [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

### Backend Selection

```env
MAILPANEL_BACKEND=ldap    # or "mysql"
```

### General Settings

| Variable        | Required | Description                 |
| --------------- | -------- | --------------------------- |
| `SECRET_KEY`    | Yes      | Application secret key      |

### LDAP Settings (required when `BACKEND=ldap`)

| Variable          | Default | Description                               | Example                    |
| ----------------- | ------- | ----------------------------------------- | -------------------------- |
| `LDAP_URI`        | -       | LDAP server URI (`ldap://` or `ldaps://`) | `ldaps://ldap.example.com` |
| `LDAP_ROOT_DN`    | -       | LDAP root DN                              | `dc=example,dc=com`        |
| `LDAP_USER`       | -       | Admin email or CN for LDAP bind           | `postmaster@example.com`   |
| `LDAP_PASSWORD`   | -       | Admin password for LDAP bind              | `secret`                   |
| `LDAP_TLS_VERIFY` | `false` | Verify TLS certificate on LDAP connection | `true`                     |

### MySQL Settings (required when `BACKEND=mysql`)

| Variable         | Default      | Description                          |
| ---------------- | ------------ | ------------------------------------ |
| `MYSQL_HOST`     | -            | MySQL server hostname                |
| `MYSQL_PORT`     | `3306`       | MySQL server port                    |
| `MYSQL_DATABASE` | -            | Database name (e.g. `vmail`)         |
| `MYSQL_USER`     | -            | Database user                        |
| `MYSQL_PASSWORD` | -            | Database password                    |
| `VMAIL_PATH`     | `/var/vmail` | Mail storage base path               |
| `STORAGE_NODE`   | `vmail1`     | Storage node name for new mailboxes  |

### Optional Settings

| Variable                              | Default      | Description                                |
| ------------------------------------- | ------------ | ------------------------------------------ |
| `PASSWORD_MIN_LENGTH`                 | `8`          | Minimum password length                    |
| `PASSWORD_INCLUDES_SPECIAL_CHARS`     | `true`       | Require special characters in passwords    |
| `PASSWORD_INCLUDES_NUMBERS`           | `true`       | Require digits in passwords                |
| `PASSWORD_INCLUDES_LOWERCASE`         | `true`       | Require lowercase letters in passwords     |
| `PASSWORD_INCLUDES_UPPERCASE`         | `true`       | Require uppercase letters in passwords     |
| `PASSWORD_HASHES_USE_PREFIXED_SCHEME` | `true`       | Use `{SCHEME}` prefix in password hashes   |
| `PASSWORD_DEFAULT_SCHEME`             | `SSHA512`    | Default password hashing scheme            |
| `REQUIRE_OLD_PASSWORD_ON_CHANGE`      | `false`      | Require current password for password change |
| `PAGINATION_PER_PAGE`                 | `50`         | Items per page on list views               |
| `SESSION_TIMEOUT`                     | `1800`       | Session timeout in seconds                 |
| `ALLOWED_IP_RANGES`                   | -            | Comma-separated CIDR ranges for access     |

### Branding

| Variable             | Default                    | Description                |
| -------------------- | -------------------------- | -------------------------- |
| `BRAND_NAME`         | `MailPanel`                | Panel name in UI and title |
| `BRAND_LOGO_URL`     | `/static/logo-iredmail.png`| Logo URL in navigation     |
| `BRAND_FOOTER_TEXT`  | -                          | Custom footer text         |
| `BRAND_PRIMARY_COLOR`| -                          | CSS primary color override |

### Activity Logging (optional)

Connects to the `iredadmin` database for admin activity logging.

| Variable                      | Default      | Description                   |
| ----------------------------- | ------------ | ----------------------------- |
| `ACTIVITY_LOGGING_ENABLED`    | `true`       | Enable/disable activity log   |
| `IREDADMIN_DB_HOST`           | -            | iRedAdmin database host       |
| `IREDADMIN_DB_PORT`           | `3306`       | iRedAdmin database port       |
| `IREDADMIN_DB_NAME`           | `iredadmin`  | iRedAdmin database name       |
| `IREDADMIN_DB_USER`           | -            | iRedAdmin database user       |
| `IREDADMIN_DB_PASSWORD`       | -            | iRedAdmin database password   |

### Amavisd Integration (optional)

| Variable                             | Default    | Description                     |
| ------------------------------------ | ---------- | ------------------------------- |
| `AMAVISD_ENABLED`                    | `false`    | Enable quarantine + mail log    |
| `AMAVISD_DB_HOST`                    | -          | Amavisd database host           |
| `AMAVISD_DB_PORT`                    | `3306`     | Amavisd database port           |
| `AMAVISD_DB_NAME`                    | `amavisd`  | Amavisd database name           |
| `AMAVISD_DB_USER`                    | -          | Amavisd database user           |
| `AMAVISD_DB_PASSWORD`                | -          | Amavisd database password       |
| `AMAVISD_REMOVE_QUARANTINED_IN_DAYS` | `7`        | Quarantine retention days       |
| `AMAVISD_REMOVE_MAILLOG_IN_DAYS`     | `7`        | Mail log retention days         |

### Fail2ban Integration (optional)

| Variable           | Default                         | Description                     |
| ------------------ | ------------------------------- | ------------------------------- |
| `FAIL2BAN_ENABLED` | `false`                         | Enable ban/unban management     |
| `FAIL2BAN_SOCKET`  | -                               | Custom fail2ban-client socket   |
| `FAIL2BAN_JAILS`   | `dovecot,postfix,postfix-sasl`  | Comma-separated jail names      |

### iRedAPD Integration (optional)

| Variable           | Default    | Description                     |
| ------------------ | ---------- | ------------------------------- |
| `IREDAPD_ENABLED`  | `false`    | Enable throttle + greylisting   |
| `IREDAPD_DB_HOST`  | -          | iRedAPD database host           |
| `IREDAPD_DB_PORT`  | `3306`     | iRedAPD database port           |
| `IREDAPD_DB_NAME`  | `iredapd`  | iRedAPD database name           |
| `IREDAPD_DB_USER`  | -          | iRedAPD database user           |
| `IREDAPD_DB_PASSWORD` | -       | iRedAPD database password       |

### Password Schemes

Schemes with full hash generation support:

`SSHA512`, `SHA512`, `SSHA`, `BCRYPT`, `MD5`, `PLAIN-MD5`, `PLAIN`

Schemes that require the external `doveadm` command (falls back to SSHA if unavailable):

`CRAM-MD5`, `NTLM`

Schemes recognized for reading existing hashes but not available for generation:

`SHA`, `CRYPT`, `SHA512-CRYPT`

## Running

### Development Server

```bash
php -S localhost:8080 -t public/
```

Open `http://localhost:8080` in your browser. You will be redirected to the dashboard.

### Apache

Point the document root to the `public/` directory. The included `.htaccess` handles URL rewriting.

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/mailpanel/public
    <Directory /path/to/mailpanel/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    root /path/to/mailpanel/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Routes

All routes are registered in `public/index.php`.

| Method   | Path                                   | Auth         | Description                      |
| -------- | -------------------------------------- | ------------ | -------------------------------- |
| GET      | `/`                                    | No           | Redirects to `/dashboard`        |
| GET      | `/dashboard`                           | Yes          | Statistics dashboard             |
| GET/POST | `/login`                               | No           | Login page                       |
| GET      | `/logout`                              | No           | Clears session, redirect         |
| GET      | `/domains`                             | Yes          | List all mail domains            |
| GET/POST | `/domains/create`                      | Global admin | Create domain form               |
| GET/POST | `/domains/{domain}/edit`               | Yes          | Edit domain general settings     |
| GET/POST | `/domains/{domain}/settings`           | Yes          | Edit domain advanced settings    |
| POST     | `/domains/{domain}/delete`             | Global admin | Delete domain                    |
| GET      | `/admins`                              | Global admin | List admin accounts              |
| GET/POST | `/admins/create`                       | Global admin | Create admin account             |
| GET/POST | `/admins/{email}/{editMode}`           | Global admin | Edit admin (general/password/domains) |
| POST     | `/admins/{email}/delete`               | Global admin | Delete admin                     |
| GET      | `/logs`                                | Global admin | Activity log viewer              |
| GET      | `/deleted-mailboxes`                   | Global admin | Deferred mailbox deletions       |
| GET      | `/{domain}/users`                      | Domain admin | List users in domain             |
| GET/POST | `/{domain}/users/create`               | Domain admin | Create user form                 |
| POST     | `/{domain}/users/bulk`                 | Domain admin | Bulk enable/disable/delete       |
| POST     | `/{domain}/users/{uid}/delete`         | Domain admin | Delete user                      |
| GET/POST | `/{domain}/users/{uid}/{editMode}`     | Domain admin | View/edit user (general/password/services/forwarding) |
| GET      | `/amavisd/quarantine`                  | Global admin | Quarantined messages             |
| POST     | `/amavisd/quarantine/{id}/release`     | Global admin | Release quarantined message      |
| POST     | `/amavisd/quarantine/{id}/delete`      | Global admin | Delete quarantined message       |
| GET      | `/amavisd/maillog`                     | Global admin | Amavisd mail log                 |
| POST     | `/amavisd/cleanup`                     | Global admin | Cleanup old records              |
| GET      | `/fail2ban`                            | Global admin | Fail2ban jail status             |
| POST     | `/fail2ban/ban`                        | Global admin | Ban an IP                        |
| POST     | `/fail2ban/unban`                      | Global admin | Unban an IP                      |
| GET/POST | `/iredapd/throttle/{account}`          | Global admin | Throttle settings                |
| GET/POST | `/iredapd/greylist/{account}`          | Global admin | Greylisting settings             |

Protected routes redirect unauthenticated users to `/login?next={original_url}`.

## Authentication

Authentication depends on the selected backend:

**LDAP backend**: Binds to the LDAP server with user credentials and verifies the `domainGlobalAdmin=yes` attribute.

**MySQL backend**: Verifies credentials against the `admin` table (standalone admins) or `mailbox` table (mailbox-based admins). Checks `domain_admins` for role assignment.

### Role-Based Access Control

| Role         | Access                                                |
| ------------ | ----------------------------------------------------- |
| Global admin | Full access to all domains, users, admins, and system |
| Domain admin | Access only to assigned domains and their users       |

Global admins are identified by `domain='ALL'` in `domain_admins` (MySQL) or `domainGlobalAdmin=yes` (LDAP). Domain admins see only their managed domains in the domain list and can only manage users within those domains.

Session cookies are configured with `httponly=true`, `samesite=Lax`, and `secure=true` (when served over HTTPS). Session IDs are regenerated after successful login. Sessions expire after `SESSION_TIMEOUT` seconds of inactivity.

## Features

### Domain Management
- Domain CRUD (create, edit, delete) for both LDAP and MySQL backends
- Domain settings: default user quota, password length rules, disclaimer text
- Enable/disable domains
- Paginated domain list

### User Management
- User CRUD with full profile editing (name, quota, phone, employee ID, etc.)
- User creation on both LDAP and MySQL backends
- Mail service toggles: SMTP, POP3, IMAP, ManageSieve, SOGo (+ TLS variants)
- Email forwarding with keep-copy option
- Used quota display (from Dovecot `used_quota` table)
- Bulk operations: enable, disable, delete multiple users
- Alphabetic filtering and sortable columns
- Paginated user list
- Old password verification on password change (configurable)

### Admin Management
- Standalone and mailbox-based admin accounts
- Admin CRUD with password management
- Domain assignment: assign/revoke domains per admin
- Global admin promotion/revocation

### Dashboard
- Domain, user, and admin counts (total/active/disabled)
- Quota allocation and usage statistics
- Message count

### Activity Logging
- Admin operation logging to `iredadmin.log` table
- Log viewer with domain and event type filters
- Configurable logging toggle

### Deferred Mailbox Deletion
- Deleted user mailboxes recorded in `deleted_mailboxes` table
- Management UI: view, cancel, reschedule pending deletions

### External Integrations
- **Amavisd**: Quarantine viewer with release/delete, mail log, configurable cleanup
- **Fail2ban**: Jail status, ban/unban IPs via `fail2ban-client`
- **iRedAPD**: Per-account throttle settings, greylisting toggle, sender whitelist

### Branding
- Configurable panel name, logo, footer text, and primary color
- CSS custom property override via `BRAND_PRIMARY_COLOR`

### Security
- CSRF token validation on all POST forms
- Session ID regeneration after login (session fixation prevention)
- Open redirect prevention on login redirects
- Secure cookie flag (conditional on HTTPS)
- Configurable LDAP TLS certificate verification
- Route parameter enforcement over form body values
- Password piped via stdin to external commands (not CLI arguments)
- Session timeout with configurable duration
- IP restriction via CIDR ranges
- Role-based access control (global admin vs domain admin)

### CLI Tools

```bash
php cli/bulkPasswordUpdate.php --file=passwords.csv       # Bulk password update
php cli/bulkQuotaUpdate.php --file=quotas.csv              # Bulk quota update
php cli/deleteExpiredMailboxes.php [--dry-run]              # Cron: cleanup mailboxes
php cli/exportUsers.php --domain=example.com               # Export users to CSV
php cli/promoteToGlobalAdmin.php --email=admin@example.com # Promote to global admin
php cli/cleanupAmavisdDb.php [--quarantine-days=7]         # Cron: Amavisd cleanup
```

### Limitations

- **PostgreSQL**: Not supported
- **Alias/mailing list management**: Not supported (Pro feature in iRedAdmin)
- **i18n**: English only

## Architecture

The application uses a **Repository pattern** to abstract data access. Controllers interact with repository interfaces, and the `RepositoryFactory` returns the correct implementation based on `MAILPANEL_BACKEND`.

```
Controller → RepositoryInterface → LdapRepository  (when BACKEND=ldap)
                                 → MysqlRepository  (when BACKEND=mysql)
```

External integrations (Amavisd, iRedAPD) connect to their own databases via dedicated PDO singletons (`AmavisdConnection`, `IredapdConnection`). Fail2ban communicates via the `fail2ban-client` CLI.

### Database Connections

| Connection            | Database    | Purpose                          |
| --------------------- | ----------- | -------------------------------- |
| `MysqlConnection`     | `vmail`     | Mail domains, users, admins      |
| `IredadminConnection` | `iredadmin` | Activity logging                 |
| `AmavisdConnection`   | `amavisd`   | Quarantine and mail log          |
| `IredapdConnection`   | `iredapd`   | Throttle and greylisting         |

### Field Mapping (LDAP vs MySQL)

| User Model Field    | LDAP Attribute       | MySQL Column              |
| ------------------- | -------------------- | ------------------------- |
| `uid`               | `uid`                | `username` (before `@`)   |
| `accountStatus`     | `accountStatus`      | `active` (1/0)            |
| `mailQuota`         | `mailQuota` (bytes)  | `quota` (MB)              |
| `cn`                | `cn`                 | `name`                    |
| `givenName`         | `givenName`          | `first_name`              |
| `sn`                | `sn`                 | `last_name`               |
| `employeeNumber`    | `employeeNumber`     | `employeeid`              |
| `title`             | `title`              | `rank`                    |
| `mobile`            | `mobile`             | `mobile`                  |
| `telephoneNumber`   | `telephoneNumber`    | `phone`                   |
| `domainGlobalAdmin` | `domainGlobalAdmin`  | `isglobaladmin` (1/0)     |

## Project Structure

```
composer.json                          Dependencies and PSR-4 autoloading
phpunit.xml.dist                       PHPUnit configuration
.env.example                           Environment variable template
cli/
  bootstrap.php                        CLI autoload (no session)
  bulkPasswordUpdate.php               Bulk password update from CSV
  bulkQuotaUpdate.php                  Bulk quota update from CSV
  deleteExpiredMailboxes.php           Cron: mailbox directory cleanup
  exportUsers.php                      Export users to CSV
  promoteToGlobalAdmin.php             Promote user to global admin
  cleanupAmavisdDb.php                 Cron: Amavisd database cleanup
public/
  index.php                            Front controller and route registration
  .htaccess                            Apache URL rewrite rules
  static/                              Chota CSS framework, custom styles, logo
src/
  bootstrap.php                        Autoloading, dotenv, session, extension check
  Router.php                           Regex-based URL router with named parameters
  Middleware.php                       Auth guard, RBAC, session timeout, IP restriction
  CsrfProtection.php                  CSRF token generation and validation
  TemplateEngine.php                   Layout inheritance, branding, feature flags
  TemplateFilters.php                  localize() and asMegabytes() helpers
  Exceptions/
    BackendConnectionException.php     Shared base for backend connection errors
  Models/
    Settings.php                       Singleton config with all env vars
    LdapConnection.php                 LDAP connection singleton (TLS/STARTTLS)
    User.php                           Mail user data model (20 fields, quota in MB)
    UserPassword.php                   Password validation (7 rules)
    Domain.php                         Domain data model
    Admin.php                          Admin account data model
    PaginatedResult.php                Pagination wrapper
    DeletedMailbox.php                 Deferred deletion record
    DomainSettings.php                 Per-domain settings (key:value format)
  Repositories/
    AuthRepositoryInterface.php        Auth + RBAC contract
    DomainRepositoryInterface.php      Domain CRUD contract
    UserRepositoryInterface.php        User CRUD contract
    AdminRepositoryInterface.php       Admin CRUD contract
    ForwardingRepositoryInterface.php  Email forwarding contract
    QuotaRepositoryInterface.php       Used quota contract
    DashboardRepositoryInterface.php   Dashboard stats contract
    DeletedMailboxRepositoryInterface.php  Deferred deletion contract
    AmavisdRepositoryInterface.php     Amavisd quarantine/log contract
    IredapdRepositoryInterface.php     iRedAPD throttle/greylist contract
    RepositoryFactory.php              Returns backend-specific implementations
    Ldap/                              LDAP implementations
    Mysql/                             MySQL implementations + connection singletons
  Services/
    ActivityLogger.php                 Admin activity logging facade
    Fail2banService.php                Fail2ban CLI wrapper
  Controllers/
    AuthController.php                 Login, logout, RBAC session setup
    DashboardController.php            Dashboard statistics
    DomainController.php               Domain CRUD
    AdminController.php                Admin CRUD + domain assignment
    UserController.php                 User CRUD, services, forwarding, bulk ops
    LogController.php                  Activity log viewer
    DeletedMailboxController.php       Deferred deletion management
    AmavisdController.php              Quarantine + mail log
    Fail2banController.php             Ban/unban management
    IredapdController.php              Throttle + greylisting
    BaseController.php                 404 error page handler
  Utils/
    LdapUtils.php                      DN construction, LDAP modify helpers
    PasswordUtils.php                  Password hashing (10+ schemes)
    PasswordVerifier.php               Password verification utility
templates/                             Native PHP templates
tests/
  bootstrap.php                        Test environment setup
  Utils/
    PasswordUtilsTest.php              Password hashing scheme tests
  Models/
    UserPasswordTest.php               Password validation rule tests
    DomainTest.php                     Domain model tests
    AdminTest.php                      Admin model tests
    PaginatedResultTest.php            Pagination tests
```

## Testing

```bash
php composer.phar install
vendor/bin/phpunit
```

Tests cover password hashing schemes (PasswordUtils), password validation rules (UserPassword), and model factory methods (Domain, Admin, PaginatedResult). PHPUnit 13 is used as the test framework.

## License

This project is released into the public domain under the Unlicense.
