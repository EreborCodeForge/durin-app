# Architecture — durin-app

## Application ownership

`durin-app` is the canonical Composer **project** skeleton. The consumer owns:

- application root
- `App\` namespace
- `App\Kernel`
- `config/`, `routes/`, `public/`
- `durin.yaml` and `.env`

The framework must not own `App\`.

## Dependency graph

```text
durin-app
├── durins-forge
│   ├── durin-core
│   ├── durin-presets
│   ├── durin-architecture
│   └── mazarbul
│
└── mithrilphp
```

| Package | Role |
|---------|------|
| `durins-forge` | framework composition + DX (`vendor/bin/durin`) |
| `mithrilphp` | runtime API used directly by application bootstrap |

Do **not** require `durin-core`, `durin-presets`, `durin-architecture`, or `mazarbul` directly unless application source imports them.

## Forge boundary

Application PHP under `src/`, `public/`, `config/`, and `routes/` may import only documented Forge public APIs:

- `EreborCodeForge\Durin\Forge\Support\ApplicationPath`
- `EreborCodeForge\Durin\Forge\Core\Http\HttpApplicationKernel`
- `EreborCodeForge\Durin\Forge\Core\DiscoveryServiceProvider`

Everything else under `EreborCodeForge\Durin\Forge\` is internal. Bootstrap dependencies must be explainable as Forge public API or Mithril public runtime API.

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
