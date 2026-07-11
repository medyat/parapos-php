
------
This package provides php client for Parapos API.

> **Requires [PHP 8.2+](https://php.net/releases/)**

> Upgrading a major version? See [UPGRADE.md](UPGRADE.md).

## Using your own payment model

Point `config('parapos.model')` at your own Eloquent model (e.g. a uuid-keyed, tenant-aware one). It must `use MedyaT\Parapos\Concerns\InteractsWithParaposPayment` and `implement MedyaT\Parapos\Contracts\PaymentStatus`:

```php
// config/parapos.php
'model' => \App\Models\Payment::class,

// Set to false when your app owns the payments schema
// (e.g. uuid primary key) and ships its own migration.
'load_migrations' => false,
```

The bundled (bigint) migration can be published as a starting point:

```bash
php artisan vendor:publish --tag=parapos-migrations
```

🧹 Keep a modern codebase with **Pint**:
```bash
composer lint
```

✅ Run refactors using **Rector**
```bash
composer refacto
```

⚗️ Run static analysis using **PHPStan**:
```bash
composer test:types
```

✅ Run unit tests using **PEST**
```bash
composer test:unit
```

🚀 Run the entire test suite:
```bash
composer test
```
