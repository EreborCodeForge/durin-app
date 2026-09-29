# durin-app

Neutral Composer application root for the Durin ecosystem.

```bash
composer create-project ereborcodeforge/durin-app:^0.2 my-app
cd my-app
cp .env.example .env
vendor/bin/durin init
# or: vendor/bin/durin init --preset=service
# or: vendor/bin/durin init --preset=worker
vendor/bin/durin doctor
vendor/bin/durin run
```

Canonical UX remains the global installer:

```bash
durin new my-app --preset=service
```

## What this package is

- A Composer `project` root owned by `App\`
- Neutral bootstrap before preset initialization (`runtime.state: unresolved`)
- Direct dependencies: `ereborcodeforge/durins-forge` and `ereborcodeforge/mithrilphp`
- Environment-driven `APP_NAME` via `config/app.php`
- No Kernel, JobKernel, routes, or public entry point until `durin init`

## What this package is not

- The `minimal` preset (or any preset)
- A framework implementation
- A second CLI or preset catalog
- A concrete runtime choice (HTTP/Eregion/job)

Presets are applied by `vendor/bin/durin init` using `durin-presets` through Forge. Forge merges Composer/`.env.example` and finalizes runtime from a `RuntimePlan`.

## Structure (before init)

- `composer.json` / `.env.example` (neutral only)
- `config/app.php`
- `src/` (empty until preset)
- `storage/`, `var/cache`, `var/runtime`
- `tests/`

HTTP presets create `src/Kernel.php`, `routes/`, and `public/`. The worker preset creates `src/JobKernel.php` without HTTP ceremony.

## Documentation

- [Architecture](docs/architecture.md)
- [Distribution](docs/distribution.md)
- [ADR-0001 — Role of durin-app](docs/adr/ADR-0001-durin-app-role.md)

## License

MIT
