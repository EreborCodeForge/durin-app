# Distribution — durin-app

## Packagist

| | |
|--|--|
| Package | `ereborcodeforge/durin-app` |
| Type | `project` |
| GitHub | https://github.com/EreborCodeForge/durin-app |
| PHP | `^8.5` |
| Direct deps | `ereborcodeforge/durins-forge:^0.1`, `ereborcodeforge/mithrilphp:^2.2` |

No custom Composer `repositories` in the published `composer.json`.

## create-project flow

```bash
composer create-project ereborcodeforge/durin-app my-app
cd my-app
cp .env.example .env
vendor/bin/durin doctor
```

Expected after install:

- `vendor/bin/durin` present (from Forge)
- `App\Kernel` autoloadable
- `durin.yaml` with `architecture.modules: false`
- direct `mithrilphp` require in root `composer.json`
- doctor exits 0 (Eregion warnings without local server install are acceptable)

`create-project` must **not** auto-download Eregion, start services, run migrations, or mutate the host machine.

## Release process

1. CI green on PHP 8.5
2. Local `composer test` + `vendor/bin/durin doctor`
3. Boundary + package contract green
4. Tag annotated `vX.Y.Z` and push (**never retag** published tags)
5. GitHub Release
6. Sync Packagist
7. Run Packagist `create-project` distribution smoke

Version identity is the **git tag**, not a `version` field in `composer.json`.

Patch hardening after `v0.1.0` ships as `v0.1.1` (modules parity, Mithril direct require, Forge public API boundary, distribution smoke).

## Consumer smoke

From an empty directory (after Packagist sync of **v0.1.1+**):

```bash
composer create-project ereborcodeforge/durin-app:^0.1 smoke-app
cd smoke-app
test -x vendor/bin/durin
vendor/bin/durin doctor
vendor/bin/durin optimize
```

Assert the installed tree has `modules: false`, `"ereborcodeforge/mithrilphp": "^2.2"` in root `composer.json`, no custom `repositories`, and optimize writes under `var/cache/`.

CI workflow: `.github/workflows/distribution-smoke.yml` (requires Packagist `>= 0.1.1`).
