#!/bin/sh
# DLS engine tests: ICC worked examples, JS vs PHP agreement, table check. Run: sh dev/tests/run.sh
set -e
dir=$(cd "$(dirname "$0")" && pwd)
tmp=$(mktemp -d)
node "$dir/cases.js" > "$tmp/cases.json"
node "$dir/run-js.js" "$tmp/cases.json" > "$tmp/js.json"
php "$dir/run-php.php" "$tmp/cases.json" > "$tmp/php.json"
node "$dir/compare.js" "$tmp/cases.json" "$tmp/js.json" "$tmp/php.json"
rm -rf "$tmp"
