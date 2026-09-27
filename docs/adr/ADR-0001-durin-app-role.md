# ADR-0001 — Role of durin-app

## Status

Accepted (amended for v0.1.1 dependency boundary)

## Context

The Durin package chain (`durin-core`, `durin-presets`, `durin-architecture`, `durins-forge`) is published. Consumers need a canonical `composer create-project` entry point that boots a Durin application without copying framework source or inventing a second scaffold engine.

Application bootstrap imports Mithril runtime types directly (`HttpApplication`, `Worker`, `Response`, …). Treating Mithril as only a transitive Forge dependency hid a real compile-time dependency.

## Decision

1. `ereborcodeforge/durin-app` is a Composer **project** skeleton, not a library.
2. It depends directly on `ereborcodeforge/durins-forge` and `ereborcodeforge/mithrilphp`.
3. It does **not** depend directly on `durin-core`, `durin-presets`, `durin-architecture`, or `mazarbul` in V1.
4. `App\` is application-owned; framework code stays under Forge / Mithril namespaces.
5. Application Forge imports are limited to the Forge `docs/public-api.md` whitelist (scanned across `src/`, `public/`, `config/`, `routes/`).
6. V1 represents the **minimal HTTP** application shape (`architecture.modules: false`).
7. Preset / scaffold policy remains in `durin-presets`; this repo is the published artifact.
8. No installer logic and no `durin-app-service` / `durin-app-worker` package variants.
9. CLI remains `vendor/bin/durin` from Forge — no local `bin/durin`.

## Consequences

- `composer create-project ereborcodeforge/durin-app my-app` is the public onboarding path.
- Drift against `minimal` must be detected in CI (semantic parity).
- Forge public API promotions happen in `durins-forge` before the app relies on new classes.
- Richer naming / preset selection belongs in a future `durin-installer`, not here.
