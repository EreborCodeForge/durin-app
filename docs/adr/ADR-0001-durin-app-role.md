# ADR-0001 — Role of durin-app

## Status

Accepted

## Context

The Durin package chain (`durin-core`, `durin-presets`, `durin-architecture`, `durins-forge`) is published. Consumers need a canonical `composer create-project` entry point that boots a Durin application without copying framework source or inventing a second scaffold engine.

## Decision

1. `ereborcodeforge/durin-app` is a Composer **project** skeleton, not a library.
2. It depends directly on `ereborcodeforge/durins-forge` only (among Durin packages).
3. `App\` is application-owned; framework code stays under Forge / Mithril namespaces.
4. V1 represents the **minimal HTTP** application shape.
5. Preset / scaffold policy remains in `durin-presets`; this repo is the published artifact.
6. No installer logic and no `durin-app-service` / `durin-app-worker` package variants.
7. CLI remains `vendor/bin/durin` from Forge — no local `bin/durin`.

## Consequences

- `composer create-project ereborcodeforge/durin-app my-app` is the public onboarding path.
- Drift against `minimal` must be detected in CI (semantic parity).
- Richer naming / preset selection belongs in a future `durin-installer`, not here.
