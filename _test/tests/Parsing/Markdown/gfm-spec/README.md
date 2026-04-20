# GFM Spec Test Fixture

`spec.txt` is the authoritative test document for GitHub Flavored Markdown
(GFM). It combines CommonMark's test examples with GFM-specific extensions
(tables, strikethrough, task lists, autolink extension, disallowed raw HTML)
in a single fenced-example file.

## Upstream

- Project: https://github.com/github/cmark-gfm
- Source path: `test/spec.txt`
- Pinned commit: `587a12bb54d95ac37241377e6ddc93ea0e45439b` (2023-07-21)
- Spec version: 0.29 (date: 2019-04-06, as recorded in the file header)
- License: CC-BY-SA 4.0 (see `LICENSE` in this directory)

## Re-syncing

```
curl -fsSL -o spec.txt https://raw.githubusercontent.com/github/cmark-gfm/<COMMIT>/test/spec.txt
```

Update this README's pinned-commit line and re-run the suite. Example numbers
may shift between versions — adjust `skip.php` entries accordingly.

## License note

This fixture is included under CC-BY-SA 4.0 — a share-alike license separate
from DokuWiki's GPLv2. The two coexist cleanly because this file lives in
its own directory with its own `LICENSE`, is test data (not compiled into
shipped binaries), and is preserved verbatim with attribution. Any
modifications to the fixture itself would have to remain CC-BY-SA 4.0 —
but modifications aren't expected; the file is treated as upstream-owned.

## Usage

`SpecReader` (`../../SpecReader.php`) parses this file's fenced-example blocks
into individual test records. `GfmSpecTest` uses the reader as a data
provider. Deliberately out-of-scope examples are listed in `skip.php` with a
short reason each.
