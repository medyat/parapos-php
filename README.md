
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

## Running two profiles in one application

Global config assumes one profile per process. When an application collects on more than one — say
a tenant context and an admin context sharing the same workers — pass the profile to `Parapos`
instead, and nothing has to be written to global config (writes that would otherwise leak from one
request into the next under Octane or a queue worker):

```php
$parapos = new Parapos([
    'apiKey' => '...',
    'secretKey' => '...',
    'model' => \App\Models\Landlord\LandlordCardPayment::class,
    'response_url' => 'landlord/parapos/response/{hash}',
    'appUrl' => 'https://admin.example.com',
]);
```

`model`, `response_url`, `tenant` and `appUrl` each fall back to `config('parapos.*')` (and
`config('app.url')`) when omitted, so an application with a single profile needs no changes.

The 3D callback needs a matching route. Extend `HandleResponseAction`, override what differs, and
register it wherever you like:

```php
class LandlordParaposResponseController extends \MedyaT\Parapos\Http\HandleResponseAction
{
    protected function model(): string
    {
        return \App\Models\Landlord\LandlordCardPayment::class;
    }
}

Route::post('landlord/parapos/response/{hash}', LandlordParaposResponseController::class);
```

Set `'register_routes' => false` if you would rather register every callback route yourself.

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
