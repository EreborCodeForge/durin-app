# ADR-0001 — Role of durin-app

## Status

Accepted (amended for v0.2.0 neutral root)

## Context

The Durin package chain (`durin-core`, `durin-presets`, `durin-architecture`, `durins-forge`) is published. Consumers need a canonical `composer create-project` entry point that is **not** secretly equal to the `minimal` preset, so worker/service/future shapes can initialize without deleting HTTP-specific files.

## Decision

1. `ereborcodeforge/durin-app` is a Composer **project** skeleton, not a library.
2. It depends directly on `ereborcodeforge/durins-forge` and `ereborcodeforge/mithrilphp`.
3. It does **not** depend directly on `durin-core`, `durin-presets`, `durin-architecture`, or `mazarbul`.
4. `App\` is application-owned; framework code stays under Forge / Mithril namespaces.
5. Application Forge imports are limited to the Forge `docs/public-api.md` whitelist.
6. From `0.2.x`, the published artifact is a **neutral** root (`preset: uninitialized` until `durin init`).
7. Preset / scaffold policy remains in `durin-presets`; Forge applies it via `durin init`.
8. No installer logic and no per-preset package variants (`durin-app-service`, …).
9. CLI remains `vendor/bin/durin` from Forge — no local `bin/durin`.
10. Installer may set `APP_NAME` only; Forge/preset owns `durin.yaml`, Kernel, routes, and layers.

## Consequences

- `composer create-project ereborcodeforge/durin-app:^0.2 my-app && vendor/bin/durin init` is the low-level onboarding path.
- Global `durin new` (installer) remains the canonical UX.
- Parity CI tracks neutral-root invariants, not `minimal` identity.
