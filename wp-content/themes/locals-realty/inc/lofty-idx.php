<?php
/**
 * Lofty integration.
 *
 * `thelocals-group.com` will resolve to this WordPress site (on WPEngine).
 * Lofty supplies two integration shapes:
 *
 *   1. Widget iframes — Lofty hosts each widget at /api-site/widget/{ID} on
 *      their domain (currently `thelocals-group.com` while Lofty owns DNS;
 *      will need a subdomain re-point once the apex moves to WPEngine — see
 *      bottom of this file for the migration plan). The iframe sends
 *      postMessage height updates so we can auto-resize without scrollbars.
 *
 *   2. REST API — server-side fetch for cases where we want to render
 *      listings ourselves with our own brand styling. Used by
 *      `locals_render_listings()` for the Highlighted Properties grid.
 *
 * Configure these in wp-config.php:
 *
 *   define('LOFTY_WIDGET_BASE', 'https://thelocals-group.com');
 *   // LOFTY_API_BASE defaults to https://api.lofty.com — only override if Lofty
 *   // tells you to use a different host.
 *   define('LOFTY_API_KEY',     'paste-from-lofty-dashboard');
 *   define('LOFTY_AGENT_ID',    'your-lofty-agent-or-team-id');
 */

if (!defined('ABSPATH')) {
    exit;
}

/* -------------------------------------------------------------------------
 *  Config helpers
 * ------------------------------------------------------------------------- */

function locals_lofty_widget_base() {
    return defined('LOFTY_WIDGET_BASE')
        ? rtrim(LOFTY_WIDGET_BASE, '/')
        : 'https://thelocals-group.com';
}
function locals_lofty_api_base() {
    return defined('LOFTY_API_BASE')
        ? rtrim(LOFTY_API_BASE, '/')
        : 'https://api.lofty.com';
}
function locals_lofty_api_key()       { return defined('LOFTY_API_KEY')       ? LOFTY_API_KEY       : ''; }
function locals_lofty_agent_id()      { return defined('LOFTY_AGENT_ID')      ? LOFTY_AGENT_ID      : ''; }
function locals_lofty_client_id()     { return defined('LOFTY_CLIENT_ID')     ? LOFTY_CLIENT_ID     : ''; }
function locals_lofty_client_secret() { return defined('LOFTY_CLIENT_SECRET') ? LOFTY_CLIENT_SECRET : ''; }
function locals_lofty_token_path()    { return defined('LOFTY_TOKEN_PATH')    ? LOFTY_TOKEN_PATH    : '/oauth/token'; }

/**
 * OAuth 2.0 client_credentials flow. Cached in a transient until ~60s before expiry.
 * Returns the access token string, or '' on failure.
 */
function locals_lofty_access_token() {
    $cid = locals_lofty_client_id();
    $sec = locals_lofty_client_secret();
    if (!$cid || !$sec) {
        return '';
    }

    $cache = get_transient('locals_lofty_access_token');
    if (is_array($cache) && !empty($cache['token']) && $cache['expires_at'] > time() + 60) {
        return $cache['token'];
    }

    $url = locals_lofty_api_base() . locals_lofty_token_path();
    $res = wp_remote_post($url, [
        'timeout' => 8,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($cid . ':' . $sec),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ],
        'body'    => http_build_query(['grant_type' => 'client_credentials']),
    ]);

    if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
        if (current_user_can('manage_options')) {
            set_transient('locals_lofty_token_error', [
                'status' => is_wp_error($res) ? 0 : (int) wp_remote_retrieve_response_code($res),
                'body'   => is_wp_error($res) ? $res->get_error_message() : (string) wp_remote_retrieve_body($res),
                'url'    => $url,
            ], HOUR_IN_SECONDS);
        }
        return '';
    }

    $body  = json_decode((string) wp_remote_retrieve_body($res), true);
    $token = $body['access_token'] ?? '';
    $exp   = (int) ($body['expires_in'] ?? 3600);
    if ($token) {
        set_transient('locals_lofty_access_token', [
            'token'      => $token,
            'expires_at' => time() + $exp,
        ], $exp);
    }
    return $token;
}

/**
 * Resolve the right Authorization header for the listings call.
 * Prefers OAuth client_credentials when configured; otherwise falls back
 * to the raw API key as a Bearer token.
 */
function locals_lofty_auth_header() {
    if (locals_lofty_client_id() && locals_lofty_client_secret()) {
        $tok = locals_lofty_access_token();
        return $tok ? 'Bearer ' . $tok : '';
    }
    $key = locals_lofty_api_key();
    return $key ? 'Bearer ' . $key : '';
}

/* -------------------------------------------------------------------------
 *  Widget iframe — matches Lofty's actual embed snippet shape.
 *
 *  Lofty's snippet is:
 *    <iframe src="{base}/api-site/widget/{ID}" id="chimeWidget" ...>
 *    <script>… listens for postMessage 'updateBodyRect' to resize …</script>
 *
 *  Our wrapper supports multiple widgets per page (Lofty's default snippet
 *  uses a hard-coded element id "chimeWidget" which collides if you embed
 *  more than one). We assign unique ids and rely on a single global
 *  postMessage listener registered in main.js.
 * ------------------------------------------------------------------------- */

function locals_lofty_widget($widget_id, $attrs = []) {
    $widget_id = (int) $widget_id;
    if (!$widget_id) {
        return '';
    }

    static $instance = 0;
    $instance++;

    $url = locals_lofty_widget_base() . '/api-site/widget/' . $widget_id;
    $defaults = [
        'id'          => 'lofty-widget-' . $instance,
        'class'       => 'lofty-widget',
        'frameborder' => '0',
        'style'       => 'width:100%;height:600px;border:0;display:block',
        'loading'     => 'lazy',
        'title'       => __('Lofty widget', 'locals-realty'),
    ];
    $attrs = array_merge($defaults, $attrs);

    $html = '<iframe src="' . esc_url($url) . '"';
    foreach ($attrs as $k => $v) {
        if ($v === null || $v === '') {
            continue;
        }
        $html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
    }
    $html .= '></iframe>';
    return $html;
}

add_shortcode('lofty_widget', function ($atts) {
    $atts = shortcode_atts([
        'id'     => 0,
        'height' => 600,
    ], $atts, 'lofty_widget');

    return locals_lofty_widget((int) $atts['id'], [
        'style' => 'width:100%;height:' . (int) $atts['height'] . 'px;border:0;display:block',
    ]);
});

/* -------------------------------------------------------------------------
 *  API-driven listings (custom rendering, our brand styling)
 *  Use when we want to display Lofty data without showing Lofty's UI.
 * ------------------------------------------------------------------------- */

/**
 * Make a raw call to the Lofty listings endpoint.
 * Returns the full result tuple so callers and the debug view can inspect.
 *
 * @return array{
 *   ok:bool, status:int, url:string, error:string, body_raw:string, body:mixed
 * }
 */
function locals_lofty_request_listings($filters = []) {
    $base = locals_lofty_api_base();
    $key  = locals_lofty_api_key();

    if (!$base) {
        return ['ok' => false, 'status' => 0, 'url' => '', 'error' => 'LOFTY_API_BASE is not set in wp-config.php', 'body_raw' => '', 'body' => null];
    }

    // Lofty supports two auth flavors. They use different Authorization prefixes:
    //   - OAuth 2.0 access token:  "Authorization: Bearer <access_token>"
    //   - Static API key:          "Authorization: token <api_key>"
    if (locals_lofty_client_id() && locals_lofty_client_secret()) {
        $token = locals_lofty_access_token();
        if (!$token) {
            return ['ok' => false, 'status' => 0, 'url' => '', 'error' => 'OAuth token request failed; see "Token endpoint error" above', 'body_raw' => '', 'body' => null];
        }
        $auth_header = 'Bearer ' . $token;
    } elseif ($key) {
        $auth_header = 'token ' . $key;
    } else {
        return ['ok' => false, 'status' => 0, 'url' => '', 'error' => 'No Lofty credentials configured', 'body_raw' => '', 'body' => null];
    }

    $url  = $base . (defined('LOFTY_LISTINGS_PATH') ? LOFTY_LISTINGS_PATH : '/v2.0/listings/search');
    $body = locals_lofty_build_listings_request($filters);

    $res = wp_remote_post($url, [
        'timeout' => 8,
        'headers' => [
            'Authorization' => $auth_header,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
        'body'    => wp_json_encode($body),
    ]);

    if (is_wp_error($res)) {
        return ['ok' => false, 'status' => 0, 'url' => $url, 'error' => $res->get_error_message(), 'body_raw' => '', 'body' => null];
    }
    $status = (int) wp_remote_retrieve_response_code($res);
    $body_raw = (string) wp_remote_retrieve_body($res);
    $body     = json_decode($body_raw, true);

    return [
        'ok'       => $status >= 200 && $status < 300,
        'status'   => $status,
        'url'      => $url,
        'error'    => $status >= 400 ? 'HTTP ' . $status : '',
        'body_raw' => $body_raw,
        'body'     => $body,
    ];
}

/**
 * Translate our flat filter shape into Lofty's POST body for /v2.0/listings/search.
 *
 * Accepted input keys:
 *   limit, page, scope ('all'|'my'|'office'),
 *   city, state, town (treated like city), zip,
 *   price_min, price_max, beds_min, beds_max, baths_min, baths_max,
 *   property_type (string or array), purchase_type (string),
 *   sold (bool), sort (string).
 */
function locals_lofty_build_listings_request($filters = []) {
    $req = [
        'searchScope' => $filters['scope'] ?? 'all',
        'soldFlag'    => !empty($filters['sold']),
        'pageNum'     => (int) ($filters['page']  ?? 1),
        'pageSize'    => max(1, min(100, (int) ($filters['limit'] ?? 6))),
    ];

    $cond = [];

    $city  = $filters['city']  ?? $filters['town']  ?? '';
    $state = $filters['state'] ?? '';
    $zip   = $filters['zip']   ?? '';
    $loc   = array_filter([
        'city'    => $city  ? [(string) $city]  : null,
        'state'   => $state ? [(string) $state] : null,
        'zipcode' => $zip   ? [(string) $zip]   : null,
    ]);
    if ($loc) {
        $cond['location'] = $loc;
    }

    $price_min = $filters['price_min'] ?? '';
    $price_max = $filters['price_max'] ?? '';
    if ($price_min !== '' || $price_max !== '') {
        $cond['price'] = $price_min . ',' . $price_max;
    }

    if (isset($filters['beds_min']) || isset($filters['beds_max'])) {
        $cond['beds'] = ($filters['beds_min'] ?? '') . ',' . ($filters['beds_max'] ?? '');
    }
    if (isset($filters['baths_min']) || isset($filters['baths_max'])) {
        $cond['baths'] = ($filters['baths_min'] ?? '') . ',' . ($filters['baths_max'] ?? '');
    }

    if (!empty($filters['property_type'])) {
        $cond['propertyType'] = (array) $filters['property_type'];
    }
    if (!empty($filters['purchase_type'])) {
        $cond['purchaseType'] = [(string) $filters['purchase_type']];
    }

    if ($cond) {
        $req['filterConditions'] = $cond;
    }

    if (!empty($filters['sort'])) {
        $req['sortFields'] = (array) $filters['sort'];
    }

    return $req;
}

function locals_lofty_listings($filters = []) {
    $cache_key = 'locals_lofty_listings_' . md5(wp_json_encode($filters));
    $cached    = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $result = locals_lofty_request_listings($filters);
    if (!$result['ok']) {
        set_transient($cache_key, [], 2 * MINUTE_IN_SECONDS);
        if (current_user_can('manage_options')) {
            set_transient('locals_lofty_last_error', $result, HOUR_IN_SECONDS);
        }
        return [];
    }

    $body = $result['body'];
    // Lofty v2 wraps results: { listing: [...], metadata: {...} }
    $raw  = $body['listing'] ?? $body['data'] ?? $body['listings'] ?? $body['results'] ?? $body;
    if (!is_array($raw)) {
        $raw = [];
    }
    $list = array_map('locals_lofty_normalize_listing', $raw);

    set_transient($cache_key, $list, 15 * MINUTE_IN_SECONDS);
    return $list;
}

function locals_lofty_normalize_listing($raw) {
    if (!is_array($raw)) {
        return [];
    }
    return [
        'id'        => (string) ($raw['id']                    ?? ''),
        'mls_id'    => $raw['mlsListingId']                    ?? '',
        'address'   => $raw['streetAddress']                   ?? ($raw['address'] ?? ''),
        'address_full' => $raw['fullAddress']                  ?? ($raw['address'] ?? ''),
        'city'      => $raw['city']                            ?? '',
        'state'     => $raw['state']                           ?? '',
        'zip'       => $raw['zipCode']                         ?? '',
        'price'     => isset($raw['price'])     ? (float) $raw['price']     : null,
        'beds'      => isset($raw['bedrooms'])  ? (float) $raw['bedrooms']  : null,
        'baths'     => isset($raw['bathrooms']) ? (float) $raw['bathrooms'] : null,
        'sqft'      => isset($raw['sqft'])      ? (float) $raw['sqft']      : null,
        'lot_sqft'  => isset($raw['lotSize'])   ? (float) $raw['lotSize']   : null,
        'built'     => $raw['builtYear']                       ?? null,
        'status'    => $raw['listingStatus']                   ?? '',
        'type'      => $raw['propertyType']                    ?? '',
        'photo'     => $raw['previewPicture']                  ?? '',
        'agent'     => $raw['agentName']                       ?? '',
        'agent_org' => $raw['agentOrganizationName']           ?? '',
        // Prefer the externally-hosted Lofty detail page; fall back to relative path.
        'permalink' => $raw['siteDetailLink']                  ?? ($raw['detailLink'] ?? ''),
        'raw'       => $raw,
    ];
}

function locals_format_price($n) {
    if ($n === null || $n === '') return '';
    return '$' . number_format((float) $n);
}

function locals_render_listings($filters = [], $empty_message = '') {
    $listings = locals_lofty_listings($filters);
    if (!$listings) {
        if ($empty_message) {
            echo '<p class="listings__empty">' . esc_html($empty_message) . '</p>';
        }
        return;
    }
    ?>
    <ul class="listings-grid">
        <?php foreach ($listings as $l) :
            $title = trim($l['address']);
            $loc   = trim(implode(', ', array_filter([$l['city'], $l['state']])));
        ?>
            <li class="listing-card">
                <a href="<?php echo esc_url($l['permalink'] ?: '#'); ?>">
                    <?php if ($l['photo']) : ?>
                        <img class="listing-card__photo" src="<?php echo esc_url($l['photo']); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                    <?php endif; ?>
                    <div class="listing-card__body">
                        <p class="listing-card__address"><?php echo esc_html($title); ?></p>
                        <p class="listing-card__price"><?php echo esc_html(locals_format_price($l['price'])); ?></p>
                        <?php if ($loc) : ?><p class="listing-card__loc"><?php echo esc_html($loc); ?></p><?php endif; ?>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/* -------------------------------------------------------------------------
 *  REST endpoint for AJAX-filtered listings.
 *  GET /wp-json/locals/v1/listings?city=Jupiter&state=FL&scope=office&limit=6
 *  Returns: { html: "<ul class='listings-grid'>…</ul>", filters: {…} }
 * ------------------------------------------------------------------------- */

add_action('rest_api_init', function () {
    register_rest_route('locals/v1', '/listings', [
        'methods'             => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback'            => function (WP_REST_Request $req) {
            $allowed = ['city', 'town', 'state', 'zip', 'scope', 'limit', 'page', 'sort',
                        'price_min', 'price_max', 'beds_min', 'beds_max', 'baths_min', 'baths_max',
                        'property_type', 'purchase_type'];
            $filters = [];
            foreach ($allowed as $k) {
                $v = $req->get_param($k);
                if ($v !== null && $v !== '') {
                    $filters[$k] = is_array($v) ? array_map('sanitize_text_field', $v) : sanitize_text_field((string) $v);
                }
            }
            if (empty($filters['scope']))  { $filters['scope'] = 'office'; }
            if (empty($filters['limit']))  { $filters['limit'] = 6; }

            // Admin-only debug: ?debug=1 returns the raw Lofty request/response
            // so we can inspect which filter fields the API actually honors.
            if ($req->get_param('debug') && current_user_can('manage_options')) {
                $request_body = locals_lofty_build_listings_request($filters);
                $result       = locals_lofty_request_listings($filters);
                $body         = $result['body'] ?? null;
                $rows         = is_array($body) ? ($body['data'] ?? $body['result'] ?? $body) : [];
                $cities       = [];
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        if (is_array($row)) {
                            $cities[] = $row['city'] ?? ($row['cityName'] ?? '?');
                        }
                    }
                }
                return [
                    'filters'        => $filters,
                    'lofty_request'  => $request_body,
                    'lofty_status'   => $result['status'],
                    'lofty_url'      => $result['url'],
                    'lofty_error'    => $result['error'],
                    'returned_count' => is_array($rows) ? count($rows) : 0,
                    'returned_cities' => array_count_values(array_filter($cities)),
                    'body_keys'      => is_array($body) ? array_keys($body) : null,
                    'body_raw_sample' => substr((string) $result['body_raw'], 0, 1500),
                ];
            }

            ob_start();
            locals_render_listings($filters, __('No listings match this filter right now.', 'locals-realty'));
            return [
                'html'    => ob_get_clean(),
                'filters' => $filters,
            ];
        },
    ]);
});

/**
 * Pick a single listing tailored to the visitor.
 *
 * Personalization v1: reads cookies set client-side when the visitor expresses
 * interest in a region (clicks a state card, town pill, or visits a state page).
 *   - locals_pref_state  → 2-letter state code
 *   - locals_pref_city   → city name
 * Falls back to a random office listing.
 *
 * Returns one normalized listing or [] if nothing matches.
 */
function locals_lofty_tailored_listing() {
    $state = isset($_COOKIE['locals_pref_state']) ? sanitize_text_field(wp_unslash($_COOKIE['locals_pref_state'])) : '';
    $city  = isset($_COOKIE['locals_pref_city'])  ? sanitize_text_field(wp_unslash($_COOKIE['locals_pref_city']))  : '';

    $tries = [];
    if ($city)  { $tries[] = ['city' => $city,  'state' => $state, 'scope' => 'office', 'limit' => 20, 'sort' => 'MLS_LIST_DATE_L_DESC']; }
    if ($state) { $tries[] = ['state' => $state, 'scope' => 'office', 'limit' => 20, 'sort' => 'MLS_LIST_DATE_L_DESC']; }
    $tries[] = ['scope' => 'office', 'limit' => 20, 'sort' => 'MLS_LIST_DATE_L_DESC'];

    foreach ($tries as $f) {
        $listings = locals_lofty_listings($f);
        if ($listings) {
            return $listings[array_rand($listings)];
        }
    }
    return [];
}

add_shortcode('lofty_listings', function ($atts) {
    $atts = shortcode_atts([
        'town' => '', 'city' => '', 'state' => '', 'zip' => '',
        'featured' => 1, 'limit' => 6,
    ], $atts, 'lofty_listings');
    ob_start();
    locals_render_listings($atts, __('Featured listings will appear here once Lofty API access is configured.', 'locals-realty'));
    return ob_get_clean();
});

/**
 * Admin-only debug view of the Lofty API call.
 *
 *   [lofty_debug]
 *
 * Renders to anyone with manage_options. Shows: configured base URL, last error,
 * and a pretty-printed sample of one listing so we can fix field names without
 * waiting on Lofty's docs.
 */
add_action('wp_body_open', function () {
    if (empty($_GET['locals_lofty_debug']) || !current_user_can('manage_options')) {
        return;
    }
    echo do_shortcode('[lofty_debug limit="' . (int) $_GET['locals_lofty_debug'] . '"]');
});

add_shortcode('lofty_debug', function ($atts) {
    if (!current_user_can('manage_options')) {
        return '';
    }
    $atts = shortcode_atts(['limit' => 1], $atts, 'lofty_debug');

    $filters = ['limit' => (int) $atts['limit']];
    delete_transient('locals_lofty_listings_' . md5(wp_json_encode($filters)));

    $request_body = locals_lofty_build_listings_request($filters);
    $result       = locals_lofty_request_listings($filters);
    $sample = null;
    if ($result['ok'] && is_array($result['body'])) {
        $rows = $result['body']['data'] ?? $result['body']['listings'] ?? $result['body']['results'] ?? $result['body'];
        if (is_array($rows) && isset($rows[0])) {
            $sample = $rows[0];
        }
    }

    $token_err = get_transient('locals_lofty_token_error');
    ob_start(); ?>
    <div style="background:#111;color:#0f0;padding:1rem;border-radius:6px;font:12px/1.5 ui-monospace,monospace;overflow:auto;max-height:80vh">
        <strong style="color:#0ff">Lofty API debug (admin only)</strong>
        <div>BASE:          <?php echo esc_html(locals_lofty_api_base() ?: '(not set)'); ?></div>
        <div>AUTH_SCHEME:   <?php echo esc_html(defined('LOFTY_AUTH_SCHEME') ? LOFTY_AUTH_SCHEME : 'bearer (default)'); ?></div>
        <div>API_KEY:       <?php echo locals_lofty_api_key()       ? '<span style="color:#0f0">configured</span>' : '<span style="color:#888">missing</span>'; ?></div>
        <div>CLIENT_ID:     <?php echo locals_lofty_client_id()     ? '<span style="color:#0f0">configured</span>' : '<span style="color:#888">missing</span>'; ?></div>
        <div>CLIENT_SECRET: <?php echo locals_lofty_client_secret() ? '<span style="color:#0f0">configured</span>' : '<span style="color:#888">missing</span>'; ?></div>
        <?php if (locals_lofty_client_id() && locals_lofty_client_secret()) : ?>
            <div>TOKEN:         <?php echo locals_lofty_access_token() ? '<span style="color:#0f0">obtained</span>' : '<span style="color:#f55">FAILED</span>'; ?></div>
        <?php endif; ?>
        <?php if ($token_err) : ?>
            <details open style="margin:0.5rem 0;color:#f55">
                <summary>Token endpoint error</summary>
                <div>URL:    <?php echo esc_html($token_err['url'] ?? ''); ?></div>
                <div>Status: <?php echo esc_html((string) ($token_err['status'] ?? '')); ?></div>
                <pre style="white-space:pre-wrap;color:#fbb;margin:0.25rem 0 0"><?php echo esc_html(substr((string) ($token_err['body'] ?? ''), 0, 4096)); ?></pre>
            </details>
        <?php endif; ?>
        <div style="margin-top:0.5rem">URL:  <?php echo esc_html($result['url']); ?></div>
        <div>HTTP: <?php echo (int) $result['status']; ?></div>
        <details style="margin-top:0.25rem">
            <summary style="color:#0ff;cursor:pointer">Request body sent</summary>
            <pre style="white-space:pre-wrap;color:#fff;margin:0.25rem 0 0"><?php echo esc_html(wp_json_encode($request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </details>
        <?php if ($result['error']) : ?>
            <div style="color:#f55">ERROR: <?php echo esc_html($result['error']); ?></div>
        <?php endif; ?>
        <details open style="margin-top:0.75rem">
            <summary style="color:#0ff;cursor:pointer">First listing (raw)</summary>
            <pre style="white-space:pre-wrap;color:#fff;margin:0.5rem 0 0"><?php echo esc_html(wp_json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </details>
        <details style="margin-top:0.5rem">
            <summary style="color:#0ff;cursor:pointer">Full body (first 8 KB)</summary>
            <pre style="white-space:pre-wrap;color:#aaa;margin:0.5rem 0 0"><?php echo esc_html(substr($result['body_raw'], 0, 8192)); ?></pre>
        </details>
    </div>
    <?php
    return ob_get_clean();
});
