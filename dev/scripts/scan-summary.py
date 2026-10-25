"""Summarises the output of scan.sh: PHP 8.5 lint failures and PHPCompatibility
findings grouped by sniff and by top-level directory."""

import collections
import json
import pathlib
import re
import sys

out = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else "data/scan")

def message_kind(message):
    """Strips the names out of a PHP message so that repeats group together."""
    message = re.sub(r"[A-Za-z_]\w*::\w+\(\)", "X::m()", message)
    return re.sub(r"\$\w+", "$x", message)


for repo in ("pipeinpipe", "totalizator"):
    print(f"\n######## {repo}")

    fatal = []
    warnings = []
    deprecated = collections.defaultdict(list)
    for line in (out / f"{repo}-lint.txt").read_text().splitlines():
        m = re.match(r"(?:PHP )?(Parse error|Fatal error|Deprecated|Warning):\s+(.*) in (\S+) on line (\d+)", line)
        if not m:
            continue
        level, message, path, lineno = m.groups()
        if level == "Deprecated":
            deprecated[message_kind(message)].append(f"{path}:{lineno}")
        elif level == "Warning":
            warnings.append(f"{path}:{lineno}  {level}: {message}")
        else:
            fatal.append(f"{path}:{lineno}  {level}: {message}")

    print(f"php -l: {len(fatal)} files fail to compile, {len(warnings)} compile warnings, "
          f"{sum(map(len, deprecated.values()))} compile-time deprecations")
    for line in sorted(fatal) + sorted(warnings):
        print("   ", line)
    for kind, where in sorted(deprecated.items(), key=lambda kv: -len(kv[1])):
        print(f"  {len(where):5d} Deprecated: {kind[:150]}")
        print(f"        e.g. {where[0]}")

    report = json.loads((out / f"{repo}-phpcompat.json").read_text())
    by_sniff = collections.Counter()
    by_dir = collections.Counter()
    severity = {}
    examples = {}
    for path, data in report["files"].items():
        top = path.split("/")[0] if "/" in path else "(root)"
        for msg in data["messages"]:
            by_sniff[msg["source"]] += 1
            by_dir[top] += 1
            severity[msg["source"]] = msg["type"]
            examples.setdefault(msg["source"], f"{path}:{msg['line']}  {msg['message']}")

    totals = report["totals"]
    print(f"PHPCompatibility: {totals['errors']} errors, {totals['warnings']} warnings "
          f"in {sum(1 for f in report['files'].values() if f['messages'])} files")
    print("  by directory: " + ", ".join(f"{d}={n}" for d, n in by_dir.most_common()))
    for sniff, n in by_sniff.most_common():
        print(f"  {n:5d} {severity[sniff]:7s} {sniff}")
        print(f"        e.g. {examples[sniff][:180]}")
