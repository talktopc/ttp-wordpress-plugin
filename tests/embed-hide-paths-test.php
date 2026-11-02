<?php
/**
 * Path matching for the partner-embed hide list. No WordPress.
 *
 * php tests/embed-hide-paths-test.php
 */

define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/includes/embed-hide-paths.php';

$failed = 0;

function check($label, $actual, $expected) {
    global $failed;
    if ($actual !== $expected) {
        $failed++;
        fwrite(STDERR, "FAIL $label\n  actual:   " . var_export($actual, true) . "\n  expected: " . var_export($expected, true) . "\n");
    }
}

$parsed = talktopc_normalize_hide_paths("/checkout\n/cart/\n\n/\nhttps://rimon-tours.co.il/thanks?order=1\ncheckout\n/checkout\n");
check('keeps prefixes and drops home', $parsed['paths'], ['/checkout', '/cart', '/thanks']);
check('counts a rejected line', $parsed['dropped'], 1);

check('full url drops query', talktopc_normalize_hide_path('https://rimon-tours.co.il/checkout?x=1#y'), '/checkout');
check('home is not a rule', talktopc_normalize_hide_path('/'), '');
check('dotdot is not a rule', talktopc_normalize_hide_path('/checkout/../admin'), '');
check('hebrew path', talktopc_normalize_hide_path('/טיולים/יפן/'), '/טיולים/יפן');

$rules = ['/checkout', '/טיולים/יפן'];
check('exact', talktopc_path_is_hidden('/checkout', $rules), true);
check('child', talktopc_path_is_hidden('/checkout/order-received', $rules), true);
check('prefix is not a child', talktopc_path_is_hidden('/checkout-extra', $rules), false);
check('home stays visible', talktopc_path_is_hidden('/', $rules), false);
check('other page stays visible', talktopc_path_is_hidden('/tours/japan', $rules), false);
check('hebrew exact', talktopc_path_is_hidden('/טיולים/יפן', $rules), true);
check('hebrew child', talktopc_path_is_hidden('/טיולים/יפן/2026', $rules), true);

check('request query ignored', talktopc_path_from_request_uri('/checkout/?order=9'), '/checkout');
check('encoded hebrew request', talktopc_path_from_request_uri('/%D7%98%D7%99%D7%95%D7%9C%D7%99%D7%9D/%D7%99%D7%A4%D7%9F/'), '/טיולים/יפן');
check('encoded request matches saved path', talktopc_path_is_hidden(
    talktopc_path_from_request_uri('/%D7%98%D7%99%D7%95%D7%9C%D7%99%D7%9D/%D7%99%D7%A4%D7%9F/?utm=1'),
    $rules
), true);
check('empty uri is home', talktopc_path_from_request_uri(''), '/');

if ($failed > 0) {
    fwrite(STDERR, "$failed failed\n");
    exit(1);
}
echo "ok\n";
