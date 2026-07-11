# Upgrade Guide

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
