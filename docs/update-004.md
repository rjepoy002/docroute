# Update 004: XAMPP subdirectory entry point

Open `http://localhost/docroute-v2`. Apache adds the directory slash, then Laravel
redirects guests to the existing named `login` route and authenticated users to
the existing named `dashboard` route. Dashboard middleware still enforces account,
verification, and role restrictions. Logout invalidates the existing session and
redirects through the root to login.

## Local environment

Set these values in the existing `.env` (do not replace database settings):

```dotenv
APP_URL=http://localhost/docroute-v2
SESSION_PATH=/docroute-v2
SESSION_DOMAIN=null
APP_DEBUG=false
```

Use HTTP-compatible cookies locally (`SESSION_SECURE_COOKIE` must not be true on
plain HTTP). Leave `ASSET_URL` unset unless an asset host is deliberately used.
Run `php artisan config:clear` after changes. The URL setting supports generated
CLI/email links; HTTP requests detect the subdirectory from Apache. No Laravel
route prefixes or forced URL roots are needed. Tests override APP_URL to localhost
because their simulated requests use root-relative paths.

Run `npm ci` if dependencies are missing, then `npm run build`. Vite's existing
Laravel integration generates URLs beneath the request base path. Stop the Vite
dev server and remove a stale `public/hot` file if production assets are desired.
Do not hardcode `/build` or `/public` into templates. The guest logo uses `url('/')`.

## Recommended Apache configuration: expose only public

XAMPP currently exposes `C:/xampp/htdocs` with directory overrides enabled. The
project `.htaccess` disables listings, denies private directories and dotfiles,
serves only selected static asset extensions from `public`, and routes all other
requests through the root `index.php`. It requires Apache 2.4, mod_rewrite,
`AllowOverride All`, and XAMPP's working PHP handler. It is a compatibility layer;
the root PHP wrapper alone cannot protect Laravel source or configuration.

For a stronger boundary, add the following to the active localhost server
configuration (for example, an included Apache configuration file). No global
Apache configuration is changed by this update. Put this Alias before any broader
Alias matching the same URL. These filesystem paths are installation-specific.

```apache
Alias /docroute-v2 "C:/xampp/htdocs/docroute-v2/public"

<Directory "C:/xampp/htdocs/docroute-v2">
    AllowOverride None
    Require all denied
</Directory>

<Directory "C:/xampp/htdocs/docroute-v2/public">
    Options -Indexes -MultiViews
    AllowOverride All
    Require all granted
    DirectoryIndex index.php
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
</Directory>
```

Validate with `C:\xampp\apache\bin\httpd.exe -t`, restart Apache using the XAMPP
control panel, and repeat the HTTP checks below. The more specific public
directory grants access only to public files; the parent override is disabled so
the compatibility rewrite rules cannot interfere with the Alias. The standard
`public/.htaccess` handles dashboard refreshes. The root wrapper remains available
for compatibility but the Alias uses the standard public entry point directly.
Do not publish alternate aliases or hosts exposing the project directory.
If Apache overrides or PHP handling are disabled, configure the public-only Alias
before serving the application.

## Acceptance checks

Automated: `php artisan test` uses isolated in-memory SQLite, not the application
MySQL database. It covers authentication, invalid passwords, logout, root redirects,
dashboard authorization, and existing modules. `npm run build` verifies assets.

HTTP checks on the running Apache instance:

- Root follows redirects to `/docroute-v2/login` (200).
- `/docroute-v2/dashboard` redirects guests to that login URL on repeated requests.
- Login form action, password-reset link, logo link, and Vite asset URLs retain
  `/docroute-v2`; local CSS, JavaScript, and favicon return 200.
- Session cookies use `/docroute-v2`; login/logout POSTs without a CSRF token
  return 419.
- `.env`, `.git/config`, `vendor/autoload.php`, `storage/logs/laravel.log`,
  `routes/web.php`, and `composer.json` are denied by the
  compatibility rules. Under the Alias these private paths must remain inaccessible.

The compatibility setup may still execute the standard `/public/index.php` entry
point because Apache applies `public/.htaccess` in that directory. It does not
serve PHP source. Use the recommended Alias to remove this alternate entry URL.

Complete a browser login with a known existing account, refresh the dashboard and
nested pages, then log out. No account passwords are changed or recovered for
verification. Check visual layout and browser console/network requests, including
external fonts, and repeat after enabling the recommended Alias.

## Results on the existing XAMPP instance

- `php artisan test`: **89 passed, 556 assertions**, using in-memory SQLite.
- `npm run build`: passed; CSS and JavaScript bundles generated successfully.
- `httpd.exe -t`: syntax OK; global configuration was not modified.
- Live root redirect chain: 301 directory slash, 302 login, final login 200.
- Guest dashboard requests, including repeated requests: 302 to subdirectory login.
- CSS, JavaScript, favicon: 200; generated login action and links include the base path.
- Login and logout POST without CSRF: 419.
- Private dotfiles, vendor, storage logs, routes, config, and composer metadata: 403.
  README source request: 404. Standard public entry point executes (302), without
  exposing source.
- Existing authentication, logout, dashboard permissions, and module tests pass.
  Login with an actual existing MySQL account and visual browser checks remain
  manual: no existing account credentials were supplied or changed.

Files changed: root `index.php`, root `.htaccess`, `routes/web.php`,
`resources/views/layouts/guest.blade.php`, `.env.example`, `phpunit.xml`,
`tests/Feature/ExampleTest.php`, `tests/Feature/Auth/AuthenticationTest.php`, and this
document. Local ignored `.env` changes: application URL, cookie path, and debug
disabled. Database settings are unchanged. Built assets remain ignored as before.
