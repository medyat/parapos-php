# Upgrade Guide

## Upgrading from 3.0 to 4.0

Nothing to do for most apps. Answer these four questions.

### 1. Did you set `config('parapos.model')` in 3.0?

**Reconcile your payments before deploying.** The 3D callback hardcoded `MedyaT\Parapos\Models\Payment`, so it either never found your payment (left `PENDING` forever) or wrote the result to the package's `payments` table instead of yours. 4.0 resolves the configured model, but it cannot repair rows 3.0 already misfiled.

### 2. Do you read `$parapos->config->response_url` anywhere?

```diff
-$url = $parapos->config->response_url;
+$url = $parapos->config->getResponseUrl($hash);
```

The property is now `?string` and defaults to `null`.

### 3. Do you pass `response_url` into the constructor?

```php
new Parapos(['response_url' => '...']);
```

It was silently ignored in 3.0 and is now used, so this changes the callback URL you send to the bank. Remove the argument to keep reading global config, or confirm the value is the URL you actually serve.

### 4. Do you call `config()->set('parapos.model'|'parapos.response_url')` *after* `app(Parapos::class)`?

Move the `set()` before the first resolve, or construct `Parapos` with the values instead. The container-bound instance now reads those two keys once. `parapos.tenant` is unaffected — it is still read on every call.

## New in 4.0: two profiles in one application

Pass a profile to `Parapos` instead of writing to global config:

```php
$parapos = new Parapos([
    'apiKey' => '...',
    'secretKey' => '...',
    'model' => \App\Models\LandlordPayment::class,
    'response_url' => 'landlord/parapos/response/{hash}',
    'tenant' => null, // explicit null = no tenant segment, even if one is set globally
]);
```

Each profile needs its own callback route:

```php
class LandlordResponseController extends \MedyaT\Parapos\Http\HandleResponseAction
{
    protected function model(): string
    {
        return \App\Models\LandlordPayment::class;
    }
}

Route::post('landlord/parapos/response/{hash}', LandlordResponseController::class);
```

Set `config('parapos.register_routes')` to `false` to register every callback route yourself.

## Upgrading from 2.x to 3.0

Version 3.0 makes the payment model host-configurable (e.g. a uuid-keyed, tenant-aware model). Most apps need no changes — but check the two points below.

### Custom `VerifyResponseMiddlewareInterface` implementations must be updated

**Likelihood of impact: high** (if you registered your own response middleware)

The `$payment` parameter is now type-hinted with the base Eloquent `Model` instead of the concrete `Payment`. PHP requires the exact signature, so existing implementations will fatal until updated:

```diff
-use MedyaT\Parapos\Models\Payment;
+use Illuminate\Database\Eloquent\Model;

 class MyResponseMiddleware implements VerifyResponseMiddlewareInterface
 {
-    public function __invoke(Request $request, Payment $payment)
+    public function __invoke(Request $request, Model $payment)
     {
         // ...
     }
 }
```

### Status constants moved to a contract

**Likelihood of impact: none** (backwards compatible)

`Payment::PAYMENT_*` still resolves, but the canonical home is now `MedyaT\Parapos\Contracts\PaymentStatus::PAYMENT_*`.

### Widened type hints

**Likelihood of impact: low**

`Http`, `HttpRequest`, `HttpResponse`, and `PaymentService::$payment` now type-hint `Illuminate\Database\Eloquent\Model` instead of `Payment`, and id parameters (`PaymentService::addPayment()`, `FindOrNewPaymentAction`) accept `int|string`. This only affects you if your static analysis relied on the narrower types.

## New in 3.0: use your own payment model

Point `config('parapos.model')` at your own Eloquent model. It must `use MedyaT\Parapos\Concerns\InteractsWithParaposPayment` and `implement MedyaT\Parapos\Contracts\PaymentStatus`:

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
