# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
See [UPGRADE.md](UPGRADE.md) for step-by-step migration instructions.

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

### Added
- Adds first version
