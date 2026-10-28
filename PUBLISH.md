# Publish to WordPress.org

The marketplace copy is Subversion, not GitHub. Pushing `main` does not update the plugin directory.

Open a terminal on this laptop and run:

```bash
cd ~/smartTerminal/ttp-wordpress-plugin
./deploy_to_svn.sh
```

That copies the plugin into the local checkout, tags the version from `talktopc.php`, and commits both to `https://plugins.svn.wordpress.org/talktopc`. Subversion is not installed on the laptop. The script runs it inside a temporary Docker container, the same way 1.9.114 was published.

The first time, the terminal asks:

- **Username:** `yinon11`
- **Password:** the WordPress.org SVN password. With two-factor authentication this is the SVN password on the WordPress.org profile security page, not the normal login password.
- **Store password unencrypted (yes/no)?** Type `yes`.

A good run ends with `Committed revision` and a number. The new version shows on WordPress.org a few minutes later.

An optional log note:

```bash
./deploy_to_svn.sh "keep the widget loading when WP Rocket delays JavaScript"
```

## Before you run it

The version is already set for the release you are shipping. Three places have to match:

- `talktopc.php` header, `Version:`
- `talktopc.php`, `TALKTOPC_VERSION`
- `readme.txt`, `Stable tag:`, plus a changelog block

`./production_deploy.sh` bumps those numbers and writes a zip to `~/Downloads`. It does not publish. `./upload_all.sh` and `./dev_sync.sh` update the Loopitis test site, not WordPress.org.

## After it is live

WordPress.org does not update sites by itself. Each site updates the plugin in wp-admin. On a site that uses WP Rocket, clear that cache too, or the old script tag stays frozen.
