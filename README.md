# durin-app

Neutral Composer application root for the Durin ecosystem.

```bash
composer create-project ereborcodeforge/durin-app:^0.2 my-app
cd my-app
cp .env.example .env
vendor/bin/durin init
# or: vendor/bin/durin init --preset=service
vendor/bin/durin doctor
vendor/bin/durin dev
```

Canonical UX remains the global installer:

```bash
durin new my-app --preset=service
```

## What this package is

- A Composer `project` root owned by `App\`
- Neutral bootstrap before preset initialization
- Direct dependencies: `ereborcodeforge/durins-forge` and `ereborcodeforge/mithrilphp`
- Environment-driven `APP_NAME` via `config/app.php`

## What this package is not

- The `minimal` preset (or any preset)
- A framework implementation
- A second CLI or preset catalog

Presets are applied by `vendor/bin/durin init` using `durin-presets` through Forge.

## Structure (before init)

- `composer.json` / `.env.example`
- `config/app.php`
- `public/index.php` (requires init)
- `src/` (empty until preset)
- `var/cache`, `var/runtime`

## Documentation

- [Architecture](docs/architecture.md)
- [Distribution](docs/distribution.md)
- [ADR-0001 — Role of durin-app](docs/adr/ADR-0001-durin-app-role.md)

## License

MIT
