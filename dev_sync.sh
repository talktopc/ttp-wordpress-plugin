#!/bin/bash
# Keep the Loopitis WordPress plugin bind mount in sync with this repo.
# The container reads /home/ubuntu/loopitis/plugins/talktopc directly, so a
# save shows up on https://loopitis.com without docker cp.
# Usage: ./dev_sync.sh          # sync once, then watch
#        ./dev_sync.sh --once   # sync once and exit

set -euo pipefail

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REMOTE="loopitis:/home/ubuntu/loopitis/plugins/talktopc/"

sync_once() {
  # The WordPress entrypoint chowns the mount point to www-data, which blocks rsync.
  ssh -o BatchMode=yes loopitis 'sudo -n chown -R ubuntu:ubuntu /home/ubuntu/loopitis/plugins/talktopc && sudo -n find /home/ubuntu/loopitis/plugins/talktopc -type d -exec chmod 755 {} +'
  rsync -rltz --omit-dir-times --delete --no-perms --no-owner --no-group \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='.vscode/' \
    --exclude='node_modules/' \
    --exclude='.DS_Store' \
    --exclude='*.zip' \
    --exclude='*.log' \
    --exclude='.gitignore' \
    --exclude='.gitattributes' \
    --exclude='*.md' \
    --exclude='*.sh' \
    --exclude='.wordpress-org/' \
    --exclude='.distignore' \
    --exclude='*.swp' \
    --exclude='*~' \
    -e "ssh -o BatchMode=yes" \
    "$PLUGIN_DIR/" "$REMOTE"
  ssh -o BatchMode=yes loopitis 'sudo -n find /home/ubuntu/loopitis/plugins/talktopc -type f -exec chmod 644 {} +'
}

sync_once

if [ "${1:-}" = "--once" ]; then
  echo "Synced to ${REMOTE}"
  exit 0
fi

echo "Watching ${PLUGIN_DIR} — saves sync to the WordPress container. Ctrl+C to stop."
export PLUGIN_DIR
python3 - "$PLUGIN_DIR" <<'PY'
import os, subprocess, sys, time

root = sys.argv[1]
skip_dirs = {".git", ".vscode", "node_modules", ".wordpress-org"}
skip_suffixes = (".zip", ".log", ".md", ".sh", ".swp", "~")
skip_names = {".DS_Store", ".gitignore", ".gitattributes", ".distignore"}

def snapshot():
    state = []
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in skip_dirs]
        for name in filenames:
            if name in skip_names or name.endswith(skip_suffixes):
                continue
            path = os.path.join(dirpath, name)
            try:
                st = os.stat(path)
            except OSError:
                continue
            state.append((path, st.st_mtime_ns, st.st_size))
    return tuple(state)

previous = snapshot()
while True:
    time.sleep(0.4)
    current = snapshot()
    if current != previous:
        previous = current
        subprocess.run(["bash", os.path.join(root, "dev_sync.sh"), "--once"], check=False)
PY
