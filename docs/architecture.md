# Architecture — durin-app

## Application ownership

`durin-app` is the canonical Composer **project** skeleton. The consumer owns:

- application root
- `App\` namespace
- `App\Kernel`
- `config/`, `routes/`, `public/`
- `durin.yaml` and `.env`

The framework must not own `App\`.

## Forge boundary

Direct production dependency:

```text
durin-app → ereborcodeforge/durins-forge
```

Transitive stack (do not require directly in V1):

```text
durin-core
durin-presets
durin-architecture
mithrilphp
mazarbul
```

Application bootstrap may import only documented Forge public APIs for Kernel / path resolution:

- `EreborCodeForge\Durin\Forge\Support\ApplicationPath`
- `EreborCodeForge\Durin\Forge\Core\Http\HttpApplicationKernel`

`config` / `routes` stay aligned with the validated `minimal` preset bootstrap symbols.

## Kernel relationship

```text
App\Kernel
    ↓ composes
HttpApplicationKernel (Forge)
    ↓
Mithril HttpApplication contract
```

`App\Kernel` stays thin: application providers, middleware, and hooks only. No generic container, route compiler, Eregion protocol, or doctor logic.

## Runtime relationship

```text
Application (this repo)
    ↓
MithrilPHP Worker (public/index.php)
    ↓
Eregion
```

`public/index.php` sets `ApplicationPath::setRoot()` from the application root (never from vendor paths) and runs as an Eregion worker under CLI.

## Preset parity

Structural intent for V1 comes from `durin-presets` **minimal**:

| Concern | Owner |
|---------|--------|
| Preset / scaffold policy | `durin-presets` |
| Published application artifact | `durin-app` |

When minimal bootstrap changes, update presets first, then refresh this skeleton and re-run parity + consumer tests.
