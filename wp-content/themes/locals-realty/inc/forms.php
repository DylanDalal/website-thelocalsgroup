<?php
/**
 * Public form handlers.
 *
 * Recruitment "Join" form: validates nonce + honeypot, stores submission as
 * a private `recruit_lead` CPT (admin-visible only), emails site admin,
 * redirects back to /join with ?joined=1 or ?joined=0.
 */

if (!defined('ABSPATH')) {
    exit;
}

function locals_handle_join_submission() {
    $referer = wp_get_referer() ?: home_url('/join');

    $nonce_ok = isset($_POST['locals_join_nonce'])
        && wp_verify_nonce(wp_unslash($_POST['locals_join_nonce']), 'locals_join');

    if (!$nonce_ok) {
        wp_safe_redirect(add_query_arg('joined', '0', $referer));
        exit;
    }

    // Honeypot: bots fill every visible field, so a non-empty hidden field is a tell.
    if (!empty($_POST['company'])) {
        wp_safe_redirect(add_query_arg('joined', '1', $referer)); // pretend success
        exit;
    }

    $fields = [
        'name'       => sanitize_text_field(wp_unslash($_POST['name']       ?? '')),
        'email'      => sanitize_email(wp_unslash($_POST['email']           ?? '')),
        'phone'      => sanitize_text_field(wp_unslash($_POST['phone']      ?? '')),
        'region'     => sanitize_text_field(wp_unslash($_POST['region']     ?? '')),
        'experience' => sanitize_text_field(wp_unslash($_POST['experience'] ?? '')),
        'message'    => sanitize_textarea_field(wp_unslash($_POST['message'] ?? '')),
    ];

    if (!$fields['name'] || !$fields['email'] || !is_email($fields['email'])) {
        wp_safe_redirect(add_query_arg('joined', '0', $referer));
        exit;
    }

    $title  = sprintf('%s — %s', $fields['name'], $fields['region'] ?: $fields['email']);
    $body   = sprintf(
        "Name: %s\nEmail: %s\nPhone: %s\nRegion: %s\nYears licensed: %s\n\n%s",
        $fields['name'], $fields['email'], $fields['phone'], $fields['region'], $fields['experience'], $fields['message']
    );

    $post_id = wp_insert_post([
        'post_type'    => 'recruit_lead',
        'post_status'  => 'private',
        'post_title'   => $title,
        'post_content' => $body,
        'meta_input'   => [
            '_lead_name'       => $fields['name'],
            '_lead_email'      => $fields['email'],
            '_lead_phone'      => $fields['phone'],
            '_lead_region'     => $fields['region'],
            '_lead_experience' => $fields['experience'],
            '_lead_source'     => 'join-form',
        ],
    ], true);

    if (is_wp_error($post_id) || !$post_id) {
        wp_safe_redirect(add_query_arg('joined', '0', $referer));
        exit;
    }

    $admin_email = get_option('admin_email');
    if ($admin_email) {
        $subject = sprintf('[%s] New recruitment lead: %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $fields['name']);
        wp_mail($admin_email, $subject, $body, [
            'Reply-To: ' . $fields['email'],
        ]);
    }

    wp_safe_redirect(add_query_arg('joined', '1', $referer));
    exit;
}

add_action('admin_post_locals_join',        'locals_handle_join_submission');
add_action('admin_post_nopriv_locals_join', 'locals_handle_join_submission');
