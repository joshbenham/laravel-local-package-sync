<p align="center">
    <img src="https://raw.githubusercontent.com/joshbenham/laravel-local-package-sync/master/docs/example.png" height="300" alt="Skeleton Php">
    <p align="center">
        <a href="https://github.com/joshbenham/laravel-local-package-sync/actions"><img alt="GitHub Workflow Status (master)" src="https://github.com/joshbenham/laravel-local-package-sync/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/joshbenham/laravel-local-package-sync"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/joshbenham/laravel-local-package-sync"></a>
        <a href="https://packagist.org/packages/joshbenham/laravel-local-package-sync"><img alt="Latest Version" src="https://img.shields.io/packagist/v/joshbenham/laravel-local-package-sync"></a>
        <a href="https://packagist.org/packages/joshbenham/laravel-local-package-sync"><img alt="License" src="https://img.shields.io/packagist/l/joshbenham/laravel-local-package-sync"></a>
    </p>
</p>

------
This package provides a way of building packages locally on a Laravel application.

> **Requires [PHP 8.3+](https://php.net/releases/)**

```bash
composer require joshbenham/laravel-local-package-sync
```

Then tell this command which path your Packages are in

```bash
php artisan packages:link --path=packages --yes --update
```

**Local Package Sync** was created by **[Josh Benham](https://twitter.com/joshbenham)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
