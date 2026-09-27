# Distribution — durin-app

## Packagist

| | |
|--|--|
| Package | `ereborcodeforge/durin-app` |
| Type | `project` |
| GitHub | https://github.com/EreborCodeForge/durin-app |
| PHP | `^8.5` |
| Dependency | `ereborcodeforge/durins-forge:^0.1` |

No custom Composer `repositories` in the published `composer.json`.

## create-project flow

```bash
composer create-project ereborcodeforge/durin-app my-app
cd my-app
cp .env.example .env
vendor/bin/durin doctor
```

Expected after install:

- `vendor/bin/durin` present (transitive from Forge)
- `App\Kernel` autoloadable
- doctor exits 0 (Eregion warnings without local server install are acceptable)

`create-project` must **not** auto-download Eregion, start services, run migrations, or mutate the host machine.

## Release process

1. CI green on PHP 8.5
2. Local `composer test` + `vendor/bin/durin doctor`
3. Preset parity checks green
4. Tag annotated `vX.Y.Z` and push
5. GitHub Release
6. Sync Packagist (webhook or manual update)

Version identity is the **git tag**, not a `version` field in `composer.json`.

## Consumer smoke

From an empty directory (after Packagist sync):

```bash
composer create-project ereborcodeforge/durin-app smoke-app
cd smoke-app
test -x vendor/bin/durin
vendor/bin/durin doctor
```

Until Packagist lists the package, validate via VCS/path only as a temporary gate — not as the public DoD.
