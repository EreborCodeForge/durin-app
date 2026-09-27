# durin-app

Official Composer application skeleton for the Durin ecosystem.

```bash
composer create-project ereborcodeforge/durin-app my-app
cd my-app
cp .env.example .env
vendor/bin/durin doctor
vendor/bin/durin dev
```

## Runtime preparation

When you need a local HTTP server and compiled caches:

```bash
vendor/bin/forge server:install
vendor/bin/forge eregion:craft
vendor/bin/durin optimize
vendor/bin/durin serve
```

## What this package is

- A Composer `project` root owned by `App\`
- Direct dependencies: `ereborcodeforge/durins-forge` (framework + DX) and `ereborcodeforge/mithrilphp` (runtime API)
- Minimal HTTP shape aligned with the Durin `minimal` preset
- Ready for application code under `src/`

## What this package is not

- A framework implementation
- A second CLI or preset engine
- Service / worker / modular package variants

CLI, doctor, generators, and runtime orchestration come from `vendor/bin/durin` (Forge).

## Structure

- `src/Http`
- `src/Application`
- `routes`
- `config`
- `public`
- `tests`

## Documentation

- [Architecture](docs/architecture.md)
- [Distribution](docs/distribution.md)
- [ADR-0001 — Role of durin-app](docs/adr/ADR-0001-durin-app-role.md)

## License

MIT
