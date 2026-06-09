# Contributing

Thank you for wanting to improve **afghanistan-province-district-village**.

## For users (just install the package)

Most people only need Composer. They do **not** need GitHub access:

```bash
composer require barialay/afghanistan-province-district-village
```

Composer downloads the latest stable release from Packagist.

## For contributors (suggest changes)

Only you (**Barialay**) can push directly to the main repository.

Other people contribute with a **Pull Request (PR)**. They do **not** push to your repo unless you give them permission.

### Simple workflow

```
Contributor                    You (Barialay)
     |                              |
     |  1. Fork repo on GitHub      |
     |  2. Edit on their fork       |
     |  3. Open Pull Request  ----> |  4. Review changes
     |                              |  5. Merge or ask for fixes
     |                              |  6. Packagist updates (auto)
```

### Step-by-step for contributors

1. Fork [github.com/Barialay/afghanistan-province-district-village](https://github.com/Barialay/afghanistan-province-district-village)
2. Clone their fork locally
3. Create a branch: `git checkout -b fix-district-names`
4. Make changes and run tests: `composer test`
5. Commit and push to **their fork**
6. On GitHub, click **Compare & pull request**
7. Wait for your review

### What you do as owner

1. Read the Pull Request on GitHub
2. If it looks good → click **Merge pull request**
3. If not → write a comment asking for changes
4. You never need to give strangers direct push access

## Branches

| Branch | Purpose |
|--------|---------|
| `main` | Official stable code (your repo uses `main`, not `master`) |
| `fix-something` | Short-lived branch for one change |

## Before submitting a PR

- Run `composer test`
- Keep changes focused (one feature or fix per PR)
- Update README if behavior changes

## Questions?

Open an [issue](https://github.com/Barialay/afghanistan-province-district-village/issues) on GitHub.
