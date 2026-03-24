# MailPanel

A lightweight PHP web application for managing [iRedMail](https://www.iredmail.org/) users and domains via LDAP. Provides a simple admin interface for domain listing, user management, and password operations.

Built with vanilla PHP 8.1+ -- no framework, no ORM, no template engine dependency. Uses the native `php-ldap` extension for all directory operations and `vlucas/phpdotenv` for environment configuration.

```
kilimcininkoroglu/mailpanel
```

### Supported Backend

| iRedMail Backend | Supported |
| ---------------- | --------- |
| OpenLDAP         | Yes       |
| MySQL/MariaDB    | No        |
| PostgreSQL       | No        |

This application is designed exclusively for iRedMail installations using the **OpenLDAP** backend.

## Requirements

- PHP 8.1 or higher
- PHP LDAP extension (`ext-ldap`)
- [Composer](https://getcomposer.org/) (included as `composer.phar`)
- An iRedMail server with OpenLDAP backend

## Installation

```bash
git clone <repository-url>
cd mailpanel
php composer.phar install
cp .env.example .env
```

Edit `.env` with your LDAP server details, then start the application (see [Running](#running)).

## Configuration

All settings use the `IREDADMIN_LIGHT_` prefix and are loaded from `.env` or `.env.prod` via [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

### Required Variables

| Variable        | Description                               | Example                    |
| --------------- | ----------------------------------------- | -------------------------- |
| `SECRET_KEY`    | Application secret key for sessions       | `my-random-secret-key`     |
| `LDAP_URI`      | LDAP server URI (`ldap://` or `ldaps://`) | `ldaps://ldap.example.com` |
| `LDAP_ROOT_DN`  | LDAP root DN                              | `dc=example,dc=com`        |
| `LDAP_USER`     | Admin email or CN for LDAP bind           | `postmaster@example.com`   |
| `LDAP_PASSWORD` | Admin password for LDAP bind              | `secret`                   |

### Optional Variables

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

Passwords must comply with the following rules (each configurable via environment variables):

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

Authentication is performed via LDAP bind:

1. User submits email and password on the login page
2. The application binds to the LDAP server using the provided credentials
3. After successful bind, it verifies the user has `domainGlobalAdmin=yes` attribute
4. If verified, the email is stored in `$_SESSION['email']`
5. On failure, an "Invalid credentials!" error is displayed

Only global administrators can access the application. Regular mail users cannot log in.

Session cookies are configured with `httponly=true` and `samesite=Lax`.

LDAP connection errors (e.g., expired session) automatically redirect to `/logout`.

## Features

- Session-based authentication via LDAP bind with admin verification
- Domain listing with user counts and active status
- User listing sorted by identifier with quota and admin status
- User profile editing: full name, first/last name, employee number, position, phone numbers, quota, admin flag, account status
- Password change with configurable policy enforcement
- TLS/STARTTLS support for secure LDAP connections
- 10+ password hashing schemes (SSHA512, BCRYPT, SHA512, etc.)

### Limitations

- **User creation** is not fully implemented -- the form validates input but does not write to LDAP
- **CSV import** button exists in the UI but has no backend implementation
- **Domain management** (create/edit/delete domains) is not supported
- Only **OpenLDAP** backend is supported (no MySQL/PostgreSQL)

## LDAP Directory Structure

The application expects the standard iRedMail LDAP tree:

```
<ROOT_DN>                                          (e.g., dc=example,dc=com)
  o=domains
    domainName=example.com                         (objectClass: mailDomain)
      [domainName, accountStatus, domainCurrentUserNumber]
      ou=Users
        mail=user@example.com                      (objectClass: mailUser)
          [uid, mail, accountStatus, mailQuota, cn, givenName, sn,
           employeeNumber, title, telephoneNumber, mobile,
           domainGlobalAdmin, userPassword]
```

### User Attributes

| Attribute             | Type   | Description              | Editable |
| --------------------- | ------ | ------------------------ | -------- |
| `uid`                 | string | User identifier          | No       |
| `mail`                | string | Email address            | No       |
| `accountStatus`       | string | `active` or `disabled`   | Yes      |
| `mailQuota`           | int    | Quota in bytes           | Yes (MB) |
| `cn`                  | string | Full name                | Yes      |
| `givenName`           | string | First name               | Yes      |
| `sn`                  | string | Last name                | Yes      |
| `employeeNumber`      | string | Employee ID              | Yes      |
| `title`               | string | Job position             | Yes      |
| `telephoneNumber`     | string | Work phone               | Yes      |
| `mobile`              | string | Mobile phone             | Yes      |
| `domainGlobalAdmin`   | string | `yes` if global admin    | Yes      |
| `userPassword`        | string | Hashed password          | Yes      |

## Project Structure

```
composer.json               Dependencies and PSR-4 autoloading
.env.example                Environment variable template
public/
  index.php                 Front controller and route registration
  .htaccess                 Apache URL rewrite rules
  static/                   Chota CSS framework, custom styles, logo
src/
  bootstrap.php             Autoloading, dotenv, session initialization
  Router.php                Regex-based URL router with named parameters
  Middleware.php             Session-based authentication guard
  TemplateEngine.php         Layout inheritance via output buffering
  TemplateFilters.php        localize() and asMegabytes() helpers
  Models/
    Settings.php            Singleton, reads IREDADMIN_LIGHT_* env vars
    LdapConnection.php      LDAP singleton with TLS/STARTTLS support
    User.php                Mail user data model (11 fields)
    UserPassword.php        Password validation (7 rules)
  Controllers/
    AuthController.php      Login (LDAP bind + admin check) and logout
    DomainController.php    Domain listing from LDAP
    UserController.php      User list, view/edit, create, password change
    BaseController.php      404 error page handler
  Utils/
    LdapUtils.php           DN construction, LDAP modify helpers
    PasswordUtils.php       Password hashing (10+ schemes)
templates/
  base.php                  HTML layout with navigation bar
  loginPage.php             Login form
  domainList.php            Domain table
  userList.php              User table with breadcrumbs
  userView.php              User edit form (general + password tabs)
  userCreate.php            User creation form
  page404.php               404 error page
```

## License

This project is released into the public domain under the Unlicense.
