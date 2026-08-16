# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [4.0.0] - 2026-08-16
See [UPGRADE.md](UPGRADE.md#upgrading-from-30-to-40) for step-by-step migration instructions.

### Added (per-instance configuration — no global writes)
- `Config` now carries `model`, `tenant`, `appUrl` and an overridable `response_url`. Each falls
  back to `config('parapos.*')` when null, so single-profile applications are unaffected. This lets
  one process drive two independent profiles without `config()->set`, whose writes leak between
  requests in a long-lived worker (Octane, queue workers).
- `FindOrNewPaymentAction` accepts the model class as an optional constructor argument;
  `PaymentService` and `CardService` pass `$this->config->model`. `new FindOrNewPaymentAction`
  still resolves the global config.
- `MedyaT\Parapos\Http\HandleResponseAction` — the 3D callback handler, extracted from the bundled
  route closure. Extend it and override `model()`, `responseMiddlewares()` or `view()` to register
  additional callback routes.
- `config('parapos.register_routes')` (default `true`) toggles the bundled route.
- Passing `'tenant' => null` explicitly means "this profile has no tenant segment" and now wins over
  a globally configured tenant — an admin profile can opt out of one. Omitting the key still defers
  to `config('parapos.tenant')`. The distinction only applies to `tenant`; for `response_url`,
  `model` and `appUrl`, null continues to read as "not specified".

### Changed (BREAKING)
- `Config::$response_url` narrowed from `public string` (default `'parapos/response/{hash}'`) to
  `public ?string` (default `null`, resolved through `config('parapos.response_url')` whose default
  is the `{tenant?}` variant). Code reading the property directly now sees `null` where it used to
  see a string; call `getResponseUrl()` instead.
- `Config` previously ignored a `response_url` passed to the constructor and always read global
  config. It is now honoured — a host that passed one and unknowingly relied on it being dropped
  will see its own value take effect.
- The container-bound `Parapos` singleton is built from `config('parapos')` at resolution time, so
  `response_url` and `model` are snapshotted. A `config()->set('parapos.response_url', …)` issued
  *after* the instance is resolved no longer takes effect; set it before resolving, or construct
  `Parapos` with the value. `tenant` is deliberately exempt — the provider drops that key so
  multi-tenant hosts that set it per request keep working.

### Fixed
- The bundled callback route hardcoded `MedyaT\Parapos\Models\Payment` instead of honouring
  `config('parapos.model')`, which 3.0 introduced. A host model in a different table was never
  found; a host model sharing the `payments` table was loaded through the package class, bypassing
  any global scope (tenant isolation) the host model defines.

## [3.0.0] - 2026-07-11
See [UPGRADE.md](UPGRADE.md#upgrading-from-2x-to-30) for step-by-step migration instructions.

### Changed (PK-agnostic / host-configurable payment model)
- The payment model is now resolved from `config('parapos.model')` (default
  `MedyaT\Parapos\Models\Payment`). Host apps can supply their own Eloquent
  model — including a uuid-keyed / tenant-aware one — as long as it
  `use`s `MedyaT\Parapos\Concerns\InteractsWithParaposPayment` and
  `implements MedyaT\Parapos\Contracts\PaymentStatus`.
- Payment status constants moved to `Contracts\PaymentStatus`; the `response_hash`
  boot hook + `generate_random_string()` moved to the `InteractsWithParaposPayment`
  trait. `Payment::PAYMENT_*` still resolves (the default model implements the interface).
- `config('parapos.load_migrations')` (default `true`) toggles the bundled bigint
  migration; set `false` when the host owns the schema. The migration is also
  publishable via the `parapos-migrations` tag.
- **BREAKING:** id-typed params and hints widened so uuid keys work —
  `VerifyResponseMiddlewareInterface::__invoke()`, `Http`/`HttpRequest`/`HttpResponse`
  and `PaymentService::$payment` now type-hint the base `Illuminate\...\Model`
  instead of the concrete `Payment`; `FindOrNewPaymentAction`/`PaymentService::addPayment`
  id args accept `int|string`. Custom `VerifyResponseMiddlewareInterface`
  implementations must change their `Payment $payment` param to `Model $payment`.

## Earlier

### Added
- Adds first version
