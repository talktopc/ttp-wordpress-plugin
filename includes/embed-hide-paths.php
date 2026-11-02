<?php
/**
 * Paths where the partner embed is not printed.
 *
 * The list is edited in WordPress. Matching happens while the page HTML is
 * built, so an excluded URL never receives the loader. A stored entry hides
 * that path and anything under it. The home page cannot be hidden.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * One visitor-facing path, or '' when the line must not hide anything.
 *
 * Accepts a path (/checkout, checkout) or a full URL. Query and fragment are
 * dropped. '/' and a blank line return ''.
 *
 * @param mixed $line
 * @return string
 */
function talktopc_normalize_hide_path($line) {
    $line = trim((string) $line);
    if ($line === '') {
        return '';
    }

    $path = $line;
    $has_url_parts = strpos($line, '://') !== false
        || strpos($line, '?') !== false
        || strpos($line, '#') !== false;
    if ($has_url_parts) {
        $parsed = parse_url($line, PHP_URL_PATH);
        if (!is_string($parsed) || $parsed === '') {
            return '';
        }
        $path = $parsed;
    }

    $path = rawurldecode($path);
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return '';
    }

    $path = rtrim($path, '/');
    if ($path === '' || $path === '/') {
        return '';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    if (strlen($path) > 512) {
        return '';
    }
    if (preg_match('/[\x00-\x1F\x7F]/', $path)) {
        return '';
    }
    if (preg_match('#(^|/)\.\.(/|$)#', $path)) {
        return '';
    }
    return $path;
}

/**
 * @param mixed $raw Textarea contents, one path per line.
 * @return array{paths: string[], dropped: int}
 */
function talktopc_normalize_hide_paths($raw) {
    $lines = preg_split('/\R/u', (string) $raw);
    if (!is_array($lines)) {
        $lines = [];
    }

    $paths = [];
    $dropped = 0;
    $max = 100;
    foreach ($lines as $line) {
        if (trim((string) $line) === '') {
            continue;
        }
        if (count($paths) >= $max) {
            $dropped++;
            continue;
        }
        $path = talktopc_normalize_hide_path($line);
        if ($path === '') {
            $dropped++;
            continue;
        }
        if (in_array($path, $paths, true)) {
            continue;
        }
        $paths[] = $path;
    }

    return ['paths' => $paths, 'dropped' => $dropped];
}

/**
 * Canonical option value.
 *
 * @param mixed $value
 * @return string
 */
function talktopc_sanitize_embed_hide_paths($value) {
    $value = wp_strip_all_tags((string) $value);
    $parsed = talktopc_normalize_hide_paths($value);
    return implode("\n", $parsed['paths']);
}

/**
 * Path of a request URI, decoded, with no trailing slash. The home page is '/'.
 *
 * @param mixed $uri
 * @return string
 */
function talktopc_path_from_request_uri($uri) {
    $path = parse_url((string) $uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }
    $path = rawurldecode($path);
    $stripped = preg_replace('/[\x00-\x1F\x7F]/', '', $path);
    $path = is_string($stripped) ? $stripped : '';
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return '/';
    }
    $path = rtrim($path, '/');
    if ($path === '' || $path === '/') {
        return '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return $path;
}

/**
 * @param string $request_path Result of talktopc_path_from_request_uri().
 * @param string[] $rules Canonical paths from talktopc_normalize_hide_paths().
 * @return bool
 */
function talktopc_path_is_hidden($request_path, $rules) {
    if (!is_string($request_path) || $request_path === '' || $request_path === '/') {
        return false;
    }
    if (!is_array($rules)) {
        return false;
    }
    foreach ($rules as $rule) {
        if (!is_string($rule) || $rule === '' || $rule === '/') {
            continue;
        }
        if ($request_path === $rule) {
            return true;
        }
        if (strpos($request_path, $rule . '/') === 0) {
            return true;
        }
    }
    return false;
}

/**
 * @return string[]
 */
function talktopc_embed_hide_paths() {
    $parsed = talktopc_normalize_hide_paths((string) get_option('talktopc_embed_hide_paths', ''));
    return $parsed['paths'];
}

/**
 * WP Rocket stores the head HTML, so a saved list stays invisible until that
 * cache is gone. Returns whether a purge ran.
 *
 * @return bool
 */
function talktopc_clear_rocket_cache() {
    if (!function_exists('rocket_clean_domain')) {
        return false;
    }
    rocket_clean_domain();
    return true;
}
