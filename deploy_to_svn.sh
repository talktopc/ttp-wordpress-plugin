#!/bin/bash

# Publish the plugin to WordPress.org.
# Run this in a terminal on the laptop. See PUBLISH.md.
#
#   ./deploy_to_svn.sh
#   ./deploy_to_svn.sh "short note for the SVN log"
#   ./deploy_to_svn.sh --trunk-only

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SVN_ROOT="/home/yinon11/talktopc-wp-plugin-dev/talk-to-pc"
SVN_TRUNK_DIR="$SVN_ROOT/trunk"
SVN_TAGS_DIR="$SVN_ROOT/tags"

TRUNK_ONLY=false
NOTE=""
for arg in "$@"; do
    case "$arg" in
        --trunk-only|-t) TRUNK_ONLY=true ;;
        *) NOTE="$arg" ;;
    esac
done

PLUGIN_FILE="$PLUGIN_DIR/talktopc.php"
if [ ! -f "$PLUGIN_FILE" ]; then
    echo "Error: Plugin file not found at $PLUGIN_FILE"
    exit 1
fi

VERSION=$(grep -E "^ \* Version:" "$PLUGIN_FILE" | sed 's/.*Version: //' | tr -d ' ')
if [ -z "$VERSION" ]; then
    echo "Error: Could not find version in plugin file"
    exit 1
fi

if [ -n "$NOTE" ]; then
    COMMIT_MSG="Update to $VERSION: $NOTE"
else
    COMMIT_MSG="Update to $VERSION"
fi

echo "=========================================="
echo "Deploying TalkToPC Plugin to WordPress.org"
echo "Version: $VERSION"
echo "=========================================="
echo ""

if [ ! -d "$SVN_TRUNK_DIR" ] || [ ! -d "$SVN_TAGS_DIR" ]; then
    echo "Error: SVN checkout not found at $SVN_ROOT"
    exit 1
fi

if [ ! -t 0 ] || [ ! -t 1 ]; then
    echo "This step needs a terminal, because WordPress.org asks for a password."
    echo "Open a terminal and run:"
    echo "  cd $PLUGIN_DIR && ./deploy_to_svn.sh"
    exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "Error: docker is required. Subversion runs inside a temporary container."
    exit 1
fi

echo "Copying files to trunk..."
cd "$PLUGIN_DIR"
rsync -a --delete \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='*.zip' \
    --exclude='*.sh' \
    --exclude='*.md' \
    --exclude='.gitignore' \
    --exclude='.gitattributes' \
    --exclude='.distignore' \
    --exclude='.wordpress-org/' \
    --exclude='.vscode/' \
    --exclude='.DS_Store' \
    --exclude='node_modules/' \
    --exclude='.attach_pid*' \
    --exclude='*.log' \
    --exclude='*.swp' \
    --exclude='*.swo' \
    --exclude='*~' \
    . "$SVN_TRUNK_DIR/"
rm -rf "$SVN_TRUNK_DIR/.github"

echo "Committing to WordPress.org..."
echo "If asked: username yinon11, then the WordPress.org SVN password."
echo "At 'Store password unencrypted (yes/no)?' type yes."
echo ""

docker run --rm -it \
    -e VERSION="$VERSION" \
    -e COMMIT_MSG="$COMMIT_MSG" \
    -e TRUNK_ONLY="$TRUNK_ONLY" \
    -v "$SVN_ROOT:$SVN_ROOT" \
    -v "$HOME/.subversion:/root/.subversion" \
    -w "$SVN_ROOT" \
    alpine:3.20 \
    sh -c '
        set -e
        apk add --no-cache subversion >/dev/null
        svn cleanup
        if [ "$TRUNK_ONLY" != true ]; then
            if [ -d "tags/$VERSION" ]; then
                svn revert -R "tags/$VERSION" || true
            fi
            if [ -d "tags/$VERSION" ]; then
                svn delete --force "tags/$VERSION" || true
            fi
            if [ -d "tags/$VERSION" ]; then
                rm -rf "tags/$VERSION"
            fi
            svn copy trunk "tags/$VERSION"
        fi
        svn add --force trunk >/dev/null
        svn commit -m "$COMMIT_MSG"
    '

echo ""
echo "Committed $VERSION."
echo "WordPress.org picks up the new Stable tag within a few minutes."
echo "A site that already has the plugin still needs an update in wp-admin."
