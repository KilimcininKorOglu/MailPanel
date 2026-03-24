# MailPanel

A lightweight PHP web application for managing [iRedMail](https://www.iredmail.org/) users and domains. Supports both OpenLDAP and MySQL/MariaDB backends. Provides a simple admin interface for domain listing, user management, and password operations.

Built with vanilla PHP 8.1+ -- no framework, no ORM, no template engine dependency. Uses `vlucas/phpdotenv` for environment configuration.

```
kilimcininkoroglu/mailpanel v0.2.0
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

| Variable        | Description                               | Example                    |
| --------------- | ----------------------------------------- | -------------------------- |
| `LDAP_URI`      | LDAP server URI (`ldap://` or `ldaps://`) | `ldaps://ldap.example.com` |
| `LDAP_ROOT_DN`  | LDAP root DN                              | `dc=example,dc=com`        |
| `LDAP_USER`     | Admin email or CN for LDAP bind           | `postmaster@example.com`   |
| `LDAP_PASSWORD` | Admin password for LDAP bind              | `secret`                   |

### MySQL Settings (required when `BACKEND=mysql`)

| Variable         | Default | Description                |
| ---------------- | ------- | -------------------------- |
| `MYSQL_HOST`     | -       | MySQL server hostname      |
| `MYSQL_PORT`     | `3306`  | MySQL server port          |
| `MYSQL_DATABASE` | -       | Database name (e.g. `vmail`) |
| `MYSQL_USER`     | -       | Database user              |
| `MYSQL_PASSWORD` | -       | Database password          |

### Optional Settings

| Variable                              | Default    | Description                              |
| ------------------------------------- | ---------- | ---------------------------------------- |
| `NAME`                                | `local`    | Application instance name                |
| `TEMPLATES_AUTO_RELOAD`               | `true`     | Auto-reload templates on change          |
| `PASSWORD_MIN_LENGTH`                 | `8`        | Minimum password length                  |
| `PASSWORD_INCLUDES_SPECIAL_CHARS`     | `true`     | Require special characters in passwords  |
| `PASSWORD_INCLUDES_NUMBERS`           | `true`     | Require digits in passwords              |
| `PASSWORD_INCLUDES_LOWERCASE`         | `true`     | Require lowercase letters in passwords   |
| `PASSWORD_INCLUDES_UPPERCASE`         | `true`     | Require uppercase letters in passwords   |
| `PASSWORD_HASHES_USE_PREFIXED_SCHEME` | `true`     | Use `{SCHEME}` prefix in password hashes |
| `PASSWORD_DEFAULT_SCHEME`             | `SSHA512`  | Default password hashing scheme          |

### Password Schemes

Schemes with full hash generation support:

`SSHA512`, `SHA512`, `SSHA`, `BCRYPT`, `MD5`, `PLAIN-MD5`, `PLAIN`

Schemes that require the external `doveadm` command (falls back to SSHA if unavailable):

`CRAM-MD5`, `NTLM`

Schemes recognized for reading existing hashes but not available for generation:

`SHA`, `CRYPT`, `SHA512-CRYPT`

### Password Validation Rules

- Only printable ASCII characters (codes 32-126)
- Minimum length (default: 8 characters)
- At least one digit (if `PASSWORD_INCLUDES_NUMBERS` is true)
- At least one uppercase letter (if `PASSWORD_INCLUDES_UPPERCASE` is true)
- At least one lowercase letter (if `PASSWORD_INCLUDES_LOWERCASE` is true)
- At least one special character from: `$ @ # % ! ^ & * ( ) - _ + = { } [ ]`

## Running

### Development Server

```bash
php -S localhost:8080 -t public/
```

Open `http://localhost:8080` in your browser. You will be redirected to the login page.

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

| Method   | Path                                   | Auth | Description              |
| -------- | -------------------------------------- | ---- | ------------------------ |
| GET      | `/`                                    | No   | Redirects to `/domains`  |
| GET/POST | `/login`                               | No   | Login page               |
| GET      | `/logout`                              | No   | Clears session, redirect |
| GET      | `/domains`                             | Yes  | List all mail domains    |
| GET      | `/{domain}/users`                      | Yes  | List users in domain     |
| GET/POST | `/{domain}/users/create`               | Yes  | Create user form         |
| GET/POST | `/{domain}/users/{userUid}/{editMode}` | Yes  | View/edit user           |

The `{editMode}` parameter accepts `general` (profile fields) or `password` (password change form).

Protected routes redirect unauthenticated users to `/login?next={original_url}`.

## Authentication

Authentication depends on the selected backend:

**LDAP backend**: Binds to the LDAP server with user credentials and verifies the `domainGlobalAdmin=yes` attribute.

**MySQL backend**: Verifies credentials against the `admin` table and checks the `domain_admins` table for global admin status (`domain='ALL'`).

In both cases:
1. User submits email and password on the login page
2. Backend verifies credentials and admin status
3. If verified, the email is stored in `$_SESSION['email']`
4. On failure, an "Invalid credentials!" error is displayed

Only global administrators can access the application. Regular mail users cannot log in.

Session cookies are configured with `httponly=true` and `samesite=Lax`.

## Features

- Dual backend support: OpenLDAP and MySQL/MariaDB
- Session-based authentication with admin verification
- Domain listing with user counts and active status
- User listing sorted by identifier with quota and admin status
- User profile editing: full name, first/last name, employee number, position, phone numbers, quota, admin flag, account status
- Password change with configurable policy enforcement
- TLS/STARTTLS support for secure LDAP connections
- 10+ password hashing schemes (SSHA512, BCRYPT, SHA512, etc.)

### Limitations

- **User creation (LDAP)**: Form validates input but does not write to LDAP. MySQL backend fully supports user creation.
- **CSV import**: Button exists in the UI but has no backend implementation
- **Domain management**: Create/edit/delete domains is not supported
- **PostgreSQL**: Not supported

## Architecture

The application uses a **Repository pattern** to abstract data access. Controllers interact with repository interfaces, and the `RepositoryFactory` returns the correct implementation based on `MAILPANEL_BACKEND`.

```
Controller → RepositoryInterface → LdapRepository (when BACKEND=ldap)
                                 → MysqlRepository (when BACKEND=mysql)
```

### Field Mapping (LDAP vs MySQL)

| User Model Field  | LDAP Attribute       | MySQL Column     |
| ----------------- | -------------------- | ---------------- |
| `uid`             | `uid`                | `username` (before @) |
| `accountStatus`   | `accountStatus`      | `active` (1/0)   |
| `mailQuota`       | `mailQuota` (bytes)  | `quota` (MB)     |
| `cn`              | `cn`                 | `name`           |
| `givenName`       | `givenName`          | `first_name`     |
| `sn`              | `sn`                 | `last_name`      |
| `employeeNumber`  | `employeeNumber`     | `employeeid`     |
| `title`           | `title`              | `rank`           |
| `mobile`          | `mobile`             | `mobile`         |
| `telephoneNumber` | `telephoneNumber`    | `phone`          |
| `domainGlobalAdmin` | `domainGlobalAdmin` | `isglobaladmin` (1/0) |

## Project Structure

```
composer.json                        Dependencies and PSR-4 autoloading
.env.example                         Environment variable template
public/
  index.php                          Front controller and route registration
  .htaccess                          Apache URL rewrite rules
  static/                            Chota CSS framework, custom styles, logo
src/
  bootstrap.php                      Autoloading, dotenv, session, extension check
  Router.php                         Regex-based URL router with named parameters
  Middleware.php                      Session-based authentication guard
  TemplateEngine.php                  Layout inheritance via output buffering
  TemplateFilters.php                 localize() and asMegabytes() helpers
  Models/
    Settings.php                     Singleton config with backend selection
    LdapConnection.php               LDAP connection singleton (TLS/STARTTLS)
    User.php                         Mail user data model (11 fields, quota in MB)
    UserPassword.php                 Password validation (7 rules)
  Repositories/
    AuthRepositoryInterface.php      Authentication contract
    DomainRepositoryInterface.php    Domain listing contract
    UserRepositoryInterface.php      User CRUD contract
    RepositoryFactory.php            Returns backend-specific implementations
    Ldap/
      LdapAuthRepository.php         LDAP bind + admin check
      LdapDomainRepository.php       LDAP domain search
      LdapUserRepository.php         LDAP user operations
    Mysql/
      MysqlConnection.php            PDO singleton
      MysqlAuthRepository.php        Admin table auth + password verify
      MysqlDomainRepository.php      Domain query with user COUNT
      MysqlUserRepository.php        Mailbox table CRUD
  Controllers/
    AuthController.php               Login and logout (backend-agnostic)
    DomainController.php             Domain listing (backend-agnostic)
    UserController.php               User CRUD (backend-agnostic)
    BaseController.php               404 error page handler
  Utils/
    LdapUtils.php                    DN construction, LDAP modify helpers
    PasswordUtils.php                Password hashing (10+ schemes)
templates/                           Native PHP templates
```

## License

This project is released into the public domain under the Unlicense.
