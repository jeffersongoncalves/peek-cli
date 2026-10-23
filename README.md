# Peek CLI

Read-only inspection of remote servers over SSH (ls, cat, tail, grep, ps, df, free, uptime, tree, systemctl status) with named profiles and table/json output, built for pairing with an LLM.

## Installation

```bash
composer global require jeffersongoncalves/peek-cli
```

Or grab the standalone PHAR from the [latest release](https://github.com/jeffersongoncalves/peek-cli/releases/latest).

## Usage

```bash
peek example world
```

Every command is non-interactive: all input arrives as arguments and flags,
so a script or an AI agent can drive it without a TTY.

Update to the latest release (PHAR installs only — Composer installs update
via `composer global update`):

```bash
peek self-update
peek self-update --check
```

## Releasing

Run the **Release** workflow from the Actions tab. It stamps `version.txt`,
regenerates `CHANGELOG.md` with git-cliff, builds the PHAR, commits it to
`main`, then creates the tag and release pinned to that exact commit — so a
published tag never moves.

```bash
gh workflow run release.yml --ref main
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please see [SECURITY](.github/SECURITY.md).

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
