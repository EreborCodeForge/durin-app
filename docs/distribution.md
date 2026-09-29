# Distribution — durin-app

## Packagist

| | |
|--|--|
| Package | `ereborcodeforge/durin-app` |
| Type | `project` |
| GitHub | https://github.com/EreborCodeForge/durin-app |
| PHP | `^8.5` |
| Direct deps | `ereborcodeforge/durins-forge:^0.2.3`, `ereborcodeforge/mithrilphp:^2.2` |

No custom Composer `repositories` in the published `composer.json`.  
No `extra.mithril` runtime pin before init.

## create-project flow

```bash
composer create-project ereborcodeforge/durin-app my-app
cd my-app
cp .env.example .env
vendor/bin/durin doctor
vendor/bin/durin init --preset=minimal   # or service | worker
vendor/bin/durin doctor
```

Expected after install (before init):

- `vendor/bin/durin` present (from Forge)
- `durin.yaml` with `preset: uninitialized` and `runtime.state: unresolved`
- no `src/Kernel.php`, no `routes/`, no `public/`
- no `extra.mithril` in root `composer.json`
- doctor warns that the app is not initialized (acceptable)

`create-project` must **not** auto-download Eregion, start services, run migrations, or mutate the host machine.

## Release process

1. CI green on PHP 8.5
2. Local `composer test` + `vendor/bin/durin doctor` on the neutral root
3. Boundary + package contract green
4. Tag annotated `vX.Y.Z` and push (**never retag** published tags)
5. GitHub Release
6. Sync Packagist
7. Run Packagist `create-project` distribution smoke for **minimal**, **service**, and **worker**

Version identity is the **git tag**, not a `version` field in `composer.json`.

## Consumer smoke

From an empty directory (after Packagist sync of a release that includes Forge merge/finalize):

```bash
composer create-project ereborcodeforge/durin-app:^0.2 smoke-app
cd smoke-app
test -x vendor/bin/durin
grep -q 'state: unresolved' durin.yaml
vendor/bin/durin init --preset=minimal --skip-runtime-install
test -f src/Kernel.php
grep -q 'execution: mithril-http' durin.yaml
vendor/bin/durin doctor
```

Repeat with `--preset=service` and `--preset=worker` (worker asserts JobKernel and no `routes/`/`public/`).

CI workflow: `.github/workflows/distribution-smoke.yml`.
