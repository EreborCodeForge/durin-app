# Architecture — durin-app

## Application ownership

`durin-app` is the canonical Composer **project** skeleton. Before `durin init`, the consumer owns only a **neutral** root:

- application root
- `App\` namespace (empty `src/`)
- `config/app.php`
- `durin.yaml` with `preset: uninitialized` and `runtime.state: unresolved`
- `.env` / `.env.example` without HTTP-specific keys

After init, Forge/presets add the chosen shape (`App\Kernel` + `routes/` + `public/` for HTTP, or `App\JobKernel` for worker). The framework must not own `App\`.

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
| `mithrilphp` | runtime API used by application bootstrap **after** init |

Do **not** require `durin-core`, `durin-presets`, `durin-architecture`, or `mazarbul` directly unless application source imports them.

The neutral root does **not** pin `extra.mithril` (kernel / Eregion). Init merges those from the preset plan + `RuntimePlan`.

## Forge boundary

Application PHP under `src/`, `public/`, `config/`, and `routes/` (once created) may import only documented Forge public APIs:

- `EreborCodeForge\Durin\Forge\Support\ApplicationPath`
- `EreborCodeForge\Durin\Forge\Core\Http\HttpApplicationKernel`
- `EreborCodeForge\Durin\Forge\Core\DiscoveryServiceProvider`

Everything else under `EreborCodeForge\Durin\Forge\` is internal.

## Init flow

```text
runtime.state=unresolved
        ↓
durin init
        ↓
Preset + RuntimeResolver → RuntimePlan
        ↓
Scaffold + Composer merge + Env merge + residual cleanup
        ↓
Manifest finalize (execution + optional supervisor)
```

HTTP (`mithril-http` + supervisor `eregion`): Kernel, routes, public.  
Worker (`mithril-job`, no supervisor): JobKernel, no routes/public/Eregion.

## Preset parity

Structural intent comes from `durin-presets`. When bootstrap templates change, update presets first, then refresh this skeleton and re-run init + distribution smokes for `minimal`, `service`, and `worker`.
