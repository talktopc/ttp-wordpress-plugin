#!/bin/bash

# Script to deploy plugin to WordPress.org SVN repository
# Usage: ./deploy_to_svn.sh [--trunk-only]
#   --trunk-only: Only deploy to trunk, skip tag creation

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SVN_ROOT="/home/yinon11/talktopc-wp-plugin-dev/talk-to-pc"
SVN_TRUNK_DIR="$SVN_ROOT/trunk"
SVN_TAGS_DIR="$SVN_ROOT/tags"

# Check if --trunk-only flag is set
TRUNK_ONLY=false
if [ "$1" == "--trunk-only" ] || [ "$1" == "-t" ]; then
    TRUNK_ONLY=true
fi

# Extract version from plugin file
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

echo "=========================================="
echo "Deploying TalkToPC Plugin to WordPress.org"
echo "Version: $VERSION"
echo "=========================================="
echo ""

# Check if SVN directories exist
if [ ! -d "$SVN_TRUNK_DIR" ]; then
    echo "Error: SVN trunk directory not found at $SVN_TRUNK_DIR"
    exit 1
fi

if [ ! -d "$SVN_TAGS_DIR" ] && [ "$TRUNK_ONLY" = false ]; then
    echo "Error: SVN tags directory not found at $SVN_TAGS_DIR"
    exit 1
fi

# Step 1: Deploy to trunk
echo "📦 Step 1: Copying files to trunk..."
cd "$PLUGIN_DIR"

# Copy all files except excluded ones (matching .distignore)
rsync -av --delete \
    --exclude='.git' \
    --exclude='*.zip' \
    --exclude='production_deploy.sh' \
    --exclude='production_deploy_readme.sh' \
    --exclude='deploy_to_svn.sh' \
    --exclude='upload_all.sh' \
    --exclude='clear_cache.sh' \
    --exclude='.gitignore' \
    --exclude='.gitattributes' \
    --exclude='.distignore' \
    --exclude='.wordpress-org' \
    --exclude='*.md' \
    --exclude='.vscode' \
    --exclude='.DS_Store' \
    --exclude='node_modules' \
    --exclude='*.sh' \
    --exclude='.attach_pid*' \
    --exclude='*.log' \
    --exclude='*.swp' \
    --exclude='*.swo' \
    --exclude='*~' \
    . "$SVN_TRUNK_DIR/"

echo "✅ Files copied to trunk"
echo ""

# Step 2: Create/update tag (if not trunk-only)
if [ "$TRUNK_ONLY" = false ]; then
    TAG_DIR="$SVN_TAGS_DIR/$VERSION"
    
    echo "🏷️  Step 2: Creating/updating tag $VERSION..."
    
    # Remove existing tag if it exists
    if [ -d "$TAG_DIR" ]; then
        echo "   Removing existing tag directory..."
        svn remove "$TAG_DIR" --force 2>/dev/null || rm -rf "$TAG_DIR"
    fi
    
    # Copy trunk to tag (without -m flag, commit separately)
    echo "   Copying trunk to tags/$VERSION..."
    svn copy "$SVN_TRUNK_DIR" "$TAG_DIR" 2>&1
    
    echo "✅ Tag $VERSION created"
    echo ""
fi

# Step 3: Commit changes
echo "💾 Step 3: Committing to SVN..."
cd "$SVN_TRUNK_DIR"

# Check if there are changes to commit
if svn status | grep -q "^[AMD]"; then
    echo "   Committing trunk changes..."
    svn add --force . 2>/dev/null || true
    svn ci -m "Update to $VERSION: keep the widget loading when WP Rocket delays JavaScript"
    echo "✅ Trunk committed"
else
    echo "   No changes in trunk to commit"
fi

if [ "$TRUNK_ONLY" = false ]; then
    cd "$SVN_TAGS_DIR"
    if svn status | grep -q "^[AMD]"; then
        echo "   Committing tag changes..."
        svn add --force . 2>/dev/null || true
        svn ci -m "Tag version $VERSION"
        echo "✅ Tag committed"
    else
        echo "   No changes in tags to commit"
    fi
fi

echo ""
echo "=========================================="
echo "✅ Deployment complete!"
echo "=========================================="
echo ""
echo "Version: $VERSION"
if [ "$TRUNK_ONLY" = false ]; then
    echo "Tag: tags/$VERSION"
fi
echo "Trunk: Updated and committed"
echo ""
echo "Your plugin is now live on WordPress.org!"
