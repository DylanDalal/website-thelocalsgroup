<?php
/**
 * Landing page — "The Locals Group" dark brand redesign.
 *
 * Sections (mockup):
 *  1. Meet The Locals Group — dark hero, intro copy, team group photo.
 *  2. Find Homes / Get Approved / Sell Homes — graphic CTAs + flanking agents.
 *  3. Stay In Touch / Learn The Area — phone mockup, featured agents, feature bar.
 *  4. Our Locals — agent grid with names in big condensed type.
 *  5. Join The Team — preferred vendor / lender / affiliate / partner cards.
 *
 * Visual styling lives in assets/css/home.css (scoped under body.home) and is
 * enqueued only on the front page; behaviour in assets/js/home.js.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$gf = function ($name, $default = '') {
    $v = function_exists('get_field') ? get_field($name) : '';
    return $v !== '' && $v !== null ? $v : $default;
};

// ---- Content + fallbacks -------------------------------------------------
$intro = $gf('home_intro', "The Locals Group is a modern real estate collective built around local expertise, relationship-driven service, and community connection. Our agents help buyers, sellers, and investors navigate each market with confidence — combining neighborhood knowledge, elevated marketing, and trusted guidance from start to finish.");

$cta_find    = $gf('cta_find_url',    home_url('/search'));
$cta_approve = $gf('cta_approve_url', home_url('/get-approved'));
$cta_sell    = $gf('cta_sell_url',    home_url('/sell'));

$touch_body = $gf('touch_body', 'Get local updates, market insight, and community connection from The Locals Group.');
$touch_link = $gf('touch_link', home_url('/contact'));

// Roster: agent CPT, else auto-discovered placeholder headshots.
$roster = locals_home_roster();
$pick = function ($i) use ($roster) { return $roster[$i % max(1, count($roster))] ?? null; };

// Backgrounds with theme-asset fallbacks.
$group_photo = locals_image_url($gf('home_group_photo', null), '', 'locals-hero');
$action_bg   = locals_image_url($gf('home_action_bg', null), 'state-card-florida.jpg', 'locals-hero');

// Video opener: ACF URL, else the bundled brand reel. Poster fallback for
// reduced-motion / slow connections / formats the browser can't play.
$hero_video    = $gf('hero_video_url', LOCALS_REALTY_URI . '/assets/images/default-hero-video.mp4');
$hero_poster   = locals_image_url($gf('hero_fallback', null), 'state-card-florida.jpg', 'locals-hero');
$hero_tagline  = $gf('hero_tagline', 'Local Knowledge. Lasting Connections. Exceptional Results.');
?>

<!-- ============================ 0. VIDEO OPENER ============================ -->
<section class="tlg tlg-opener" aria-label="The Locals Group">
    <div class="tlg-opener__media" aria-hidden="true">
        <video class="tlg-opener__video" autoplay muted loop playsinline preload="metadata"
               <?php echo $hero_poster ? 'poster="' . esc_url($hero_poster) . '"' : ''; ?>>
            <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
        </video>
        <div class="tlg-opener__scrim"></div>
    </div>
    <div class="tlg-opener__content">
        <p class="tlg-script tlg-opener__brand">The Locals <span>Group</span></p>
        <p class="tlg-opener__tagline"><?php echo esc_html($hero_tagline); ?></p>
    </div>
    <a class="tlg-opener__cue" href="#meet" aria-label="Scroll to meet the team">
        <span></span>
    </a>
</section>

<!-- ====== 1 + 2. MEET THE LOCALS GROUP  →  GET APPROVED (one blended section) ======
   A single tall section with one continuous backdrop (atmosphere + US map +
   grain) shared by both phases, so there is no hard seam between "Meet The
   Locals Group" and "Get Approved". The two phases stack and scroll normally
   with the page. -->
<?php
// Editorial hero: an even row of equal-size agent cutouts over a glowing US
// map of our markets (FL/NC/SC/TN).
$cluster = locals_home_cluster(5);
$pos     = ['p1', 'p2', 'p3', 'p4', 'p5'];
$img_dir = LOCALS_REALTY_URI . '/assets/images';
$markets = [
    'FL' => ['Florida',        'florida'],
    'NC' => ['North Carolina', 'north-carolina'],
    'SC' => ['South Carolina', 'south-carolina'],
    'TN' => ['Tennessee',      'tennessee'],
];
$buyer = $pick(5);
$seller = $pick(6);
?>
<section id="meet" class="tlg tlg-saga">
    <div class="tlg-saga__pin">

        <!-- Shared continuous backdrop for both phases. -->
        <div class="tlg-saga__bg" aria-hidden="true">
            <div class="tlg-saga__atmos"></div>
            <?php get_template_part('template-parts/home-usmap'); ?>
            <div class="tlg-saga__grain"></div>
        </div>

        <!-- Phase A — Meet The Locals Group -->
        <div class="tlg-saga__phase tlg-saga__phase--meet">
            <div class="tlg-hero__inner">
                <div class="tlg-hero__copy">
                    <h1 class="tlg-display tlg-hero__title">Meet The<br>Locals Group</h1>
                    <hr class="tlg-hero__divider">
                    <p class="tlg-hero__lead"><?php echo esc_html($intro); ?></p>
                    <div class="tlg-hero__cta">
                        <a class="tlg-btn tlg-btn--gold" href="<?php echo esc_url($cta_find); ?>">Find Your Home <span aria-hidden="true">&rarr;</span></a>
                        <a class="tlg-btn tlg-btn--ghost" href="<?php echo esc_url(home_url('/about')); ?>">Meet the Team</a>
                    </div>

                    <?php // Hovering a market name lights up its state on the map (see home.js). ?>
                    <nav class="tlg-hero__states" aria-label="Our markets">
                        <?php foreach ($markets as $code => $m) : ?>
                            <a class="tlg-hero__state" href="<?php echo esc_url(home_url('/' . $m[1])); ?>" data-state="<?php echo esc_attr($code); ?>"><?php echo esc_html($m[0]); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <div class="tlg-hero__cluster">
                    <img class="tlg-hero__cluster-brush" src="<?php echo esc_url("$img_dir/brush4.png"); ?>" alt="" aria-hidden="true" decoding="async">
                    <?php foreach ($cluster as $i => $p) : if (empty($pos[$i])) break; ?>
                        <figure class="<?php echo esc_attr($pos[$i]); ?>">
                            <?php if ($p['url'] && $p['url'] !== '#') : ?><a href="<?php echo esc_url($p['url']); ?>"><?php endif; ?>
                            <img src="<?php echo esc_url($p['img']); ?>" alt="<?php echo esc_attr($p['name']); ?>">
                            <?php if ($p['url'] && $p['url'] !== '#') : ?></a><?php endif; ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Phase B — Find / Get Approved / Sell -->
        <div class="tlg-saga__phase tlg-saga__phase--action">
            <div class="tlg-action__photo" aria-hidden="true" style="--photo:url('<?php echo esc_url("$img_dir/state-card-south-carolina.jpg"); ?>');"></div>
            <div class="tlg-action__brushes" aria-hidden="true">
                <?php for ($b = 1; $b <= 5; $b++) : ?>
                    <img class="tlg-action__brush tlg-action__brush--<?php echo $b; ?>" src="<?php echo esc_url("$img_dir/brush$b.png"); ?>" alt="" loading="lazy" decoding="async">
                <?php endfor; ?>
            </div>

            <p class="tlg-action__brand">
                <span class="tlg-script">The Locals</span>
                <span class="tlg-action__brand-sub">GROUP &middot; lpt realty</span>
            </p>

            <?php if ($buyer) : ?>
                <figure class="tlg-action__agent tlg-action__agent--left"><img src="<?php echo esc_url($buyer['img']); ?>" alt=""></figure>
            <?php endif; ?>
            <?php if ($seller) : ?>
                <figure class="tlg-action__agent tlg-action__agent--right"><img src="<?php echo esc_url($seller['img']); ?>" alt=""></figure>
            <?php endif; ?>

            <a class="tlg-cta tlg-cta--find" href="<?php echo esc_url($cta_find); ?>" aria-label="Find homes">
                <img src="<?php echo esc_url("$img_dir/find-homes.png"); ?>" alt="Find Homes" loading="lazy" decoding="async">
            </a>
            <a class="tlg-cta tlg-cta--sell" href="<?php echo esc_url($cta_sell); ?>" aria-label="Sell homes">
                <img src="<?php echo esc_url("$img_dir/sell-homes.png"); ?>" alt="Sell Homes" loading="lazy" decoding="async">
            </a>

            <div class="tlg-action__stage">
                <a class="tlg-cta tlg-cta--approve" href="<?php echo esc_url($cta_approve); ?>" aria-label="Get approved">
                    <img src="<?php echo esc_url("$img_dir/get_approved_upscaled.webp"); ?>" alt="Get Approved" decoding="async">
                </a>
            </div>
        </div>

    </div>
</section>

<!-- Feature banner above the phone scene -->
<ul class="tlg-features tlg-features--banner">
    <li><strong>Local Expertise</strong><span>You can trust</span></li>
    <li><strong>Luxury Service</strong><span>Personal touch</span></li>
    <li><strong>Community First</strong><span>Always local</span></li>
</ul>

<!-- ====== 2b. GET APPROVED — PAINTED SCENES (background1 flipbook → background2 + wave) ======
     A 300vh trio:
       · Section 1 — a 100vh "scene" on background1-1…4. As it scrolls into view the
         four frames flip 1→4 (a brush stroke paints itself across the skyline), under
         foreground layers (phone / brush) that share one bottom origin so they stack
         identically on every screen size. (bootPaintScene)
       · Sections 2 & 3 — a 200vh region over the tall background2.jpg. A sticky
         WebP wave sequence (alpha) starts pinning 150vh into the trio and is scrubbed
         frame-by-frame by scroll. (bootWaveScrub) -->
<section class="tlg tlg-paint tlg-paint--scene" data-paint-scene aria-label="Get approved">
    <div class="tlg-paint__frames" aria-hidden="true">
        <?php for ($i = 1; $i <= 4; $i++) : ?>
            <div class="tlg-paint__frame" style="background-image:url('<?php echo esc_url("$img_dir/background1-$i.jpg"); ?>');"></div>
        <?php endfor; ?>
    </div>
    <!-- Foreground layer 1 — two columns, 33vw / 67vw, no gap. -->
    <div class="tlg-paint__fg tlg-paint__fg--1" aria-hidden="true">
        <div class="tlg-paint__cell tlg-paint__cell--phone">
            <img class="tlg-paint__phone" data-phone src="<?php echo esc_url("$img_dir/phone.webp"); ?>" alt="" decoding="async">
        </div>
        <div class="tlg-paint__cell tlg-paint__cell--right"><!-- image TBD --></div>
    </div>

    <!-- Foreground layer 2 — three columns, 8.33vw / 46.6vw / remainder. -->
    <div class="tlg-paint__fg tlg-paint__fg--2" aria-hidden="true">
        <div class="tlg-paint__cell tlg-paint__cell--spacer"></div>
        <div class="tlg-paint__cell tlg-paint__cell--brush">
            <?php
            // Brush animation frames (WebP, alpha), stacked and wiped in by scroll once
            // the background1 flip completes — see bootPaintScene in home.js.
            $brush_frames = [];
            foreach (glob(LOCALS_REALTY_DIR . '/assets/images/brushes/*.webp') as $bf) {
                $brush_frames[] = "$img_dir/brushes/" . basename($bf);
            }
            sort($brush_frames);
            ?>
            <?php if ($brush_frames) : ?>
            <div class="tlg-paint__brush" data-brush-frames>
                <?php foreach ($brush_frames as $bsrc) : ?>
                    <img class="tlg-paint__brush-frame" src="<?php echo esc_url($bsrc); ?>" alt="" loading="lazy" decoding="async">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="tlg-paint__cell tlg-paint__cell--rest"></div>
    </div>

    <!-- Foreground layer 3 — Stay In Touch copy: 13vw spacer / 20vw text / rest. -->
    <div class="tlg-paint__fg tlg-paint__fg--text">
        <div class="tlg-paint__cell tlg-paint__cell--lead"></div>
        <div class="tlg-paint__copy">
            <div class="tlg-paint__copy-pad"></div>
            <p class="tlg-paint__brand">
                <span class="tlg-paint__brand-the">THE</span>
                <span class="tlg-script tlg-paint__brand-name">Locals</span>
                <span class="tlg-paint__brand-group">GROUP</span>
            </p>
            <h2 class="tlg-display tlg-paint__title2">Stay<br>In<br>Touch</h2>
            <p class="tlg-paint__learn">
                <svg class="tlg-paint__pin" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
                <span>Learn the<br>Area</span>
            </p>
            <p class="tlg-paint__copy-body">Get local updates,<br>
                market insight, and<br>
                community connection<br>
                from The Locals Group.</p>
            <div class="tlg-paint__copy-fill"></div>
        </div>
        <div class="tlg-paint__cell tlg-paint__cell--tail"></div>
    </div>
</section>

<?php
// Wave frame sequence (WebP with alpha) in assets/images/sequence. Duplicate frames
// were dropped but the numbering kept, so a gap means "hold the previous frame": we
// expand the numbers back into a flat per-slot list and let home.js scrub through it.
$wave_seq = [];
foreach (glob(LOCALS_REALTY_DIR . '/assets/images/sequence/*.webp') as $f) {
    if (preg_match('/(\d+)\.webp$/', basename($f), $m)) {
        $wave_seq[(int) $m[1]] = "$img_dir/sequence/" . basename($f);
    }
}
ksort($wave_seq);
$wave_frames = [];
if ($wave_seq) {
    $cur = reset($wave_seq);
    for ($n = min(array_keys($wave_seq)); $n <= max(array_keys($wave_seq)); $n++) {
        if (isset($wave_seq[$n])) { $cur = $wave_seq[$n]; }
        $wave_frames[] = $cur;   // held across the dropped-duplicate gaps
    }
}
// Unique frames (stacked, opacity-toggled) + a per-scroll-slot index into them.
$wave_unique = array_values(array_unique($wave_frames));
$wave_uidx   = array_flip($wave_unique);
$wave_slots  = array_map(fn($u) => $wave_uidx[$u], $wave_frames);
?>
<section class="tlg tlg-paint tlg-paint--fall" data-paint-fall
         style="--bg2:url('<?php echo esc_url("$img_dir/background2.jpg"); ?>');">
    <div class="tlg-paint__fall-bg" aria-hidden="true"></div>

    <?php if ($wave_unique) : ?>
    <!-- Wave foreground: all frames stacked (alpha), preloaded progressively as you scroll
         toward them; scrubbing toggles opacity (no src swap), so no decode stutter. -->
    <div class="tlg-paint__wave" data-wave-track aria-hidden="true"
         data-slots="<?php echo esc_attr(wp_json_encode($wave_slots)); ?>">
        <div class="tlg-paint__wave-sticky">
            <?php foreach ($wave_unique as $ui => $uurl) : ?>
                <img class="tlg-paint__wave-frame" data-src="<?php echo esc_url($uurl); ?>"<?php echo $ui === 0 ? ' src="' . esc_url($uurl) . '"' : ''; ?> alt="" decoding="async">
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 2 — realtor cards in a horizontal scrollbox -->
    <div class="tlg-paint__block tlg-paint__block--a" data-reveal>
        <h2 class="tlg-display tlg-paint__cards-title">Our Locals</h2>
        <div class="tlg-cards" data-cards tabindex="0" role="group" aria-label="Our local agents">
            <ul class="tlg-cards__track">
                <?php
                $cards = array_slice($roster, 0, 12);
                // Luxury-interior backdrops sourced from Unsplash (assets/images/locals-bg).
                $card_bgs = array_map(
                    fn($f) => "$img_dir/locals-bg/" . basename($f),
                    glob(LOCALS_REALTY_DIR . '/assets/images/locals-bg/*.jpg') ?: []
                );
                sort($card_bgs);
                foreach ($cards as $ci => $a) :
                    $first = explode(' ', $a['name'])[0];
                    $rest  = trim(substr($a['name'], strlen($first)));
                    $tag   = $a['url'] && $a['url'] !== '#' ? 'a' : 'div';
                    $bg    = $card_bgs ? $card_bgs[$ci % count($card_bgs)] : '';
                ?>
                    <li class="tlg-card">
                        <<?php echo $tag; ?> class="tlg-card__link"<?php echo $tag === 'a' ? ' href="' . esc_url($a['url']) . '"' : ''; ?>>
                            <span class="tlg-card__bg"<?php echo $bg ? ' style="background-image:url(\'' . esc_url($bg) . '\');"' : ''; ?>></span>
                            <span class="tlg-card__name tlg-display" aria-hidden="true">
                                <span class="tlg-card__first"><?php echo esc_html(strtoupper($first)); ?></span>
                                <?php if ($rest) : ?><span class="tlg-card__last"><?php echo esc_html(strtoupper($rest)); ?></span><?php endif; ?>
                            </span>
                            <span class="tlg-card__photo"><img src="<?php echo esc_url($a['img']); ?>" alt="<?php echo esc_attr($a['name']); ?>" loading="lazy" decoding="async"></span>
                        </<?php echo $tag; ?>>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Section 3 — content below Our Locals. -->
    <div class="tlg-paint__final">
        <div class="tlg-paint__block tlg-paint__block--b" data-reveal>
            <p class="tlg-script tlg-paint__kicker">Section three</p>
            <h2 class="tlg-display tlg-paint__title">Placeholder headline</h2>
            <p class="tlg-paint__body">Placeholder copy for the third section — replace this with the real message and call to action.</p>
            <a class="tlg-btn tlg-btn--gold" href="<?php echo esc_url($cta_approve); ?>">Get approved</a>
        </div>
    </div>
</section>

<!-- 50vh cream band, below background2.jpg in the document. -->
<section class="tlg tlg-cream" aria-hidden="true"></section>

<!-- ============================ 3. STAY IN TOUCH / LEARN THE AREA ============================ -->
<section class="tlg tlg-touch" data-reveal>
    <div class="tlg-touch__inner">
        <div class="tlg-phone" aria-hidden="true">
            <div class="tlg-phone__notch"></div>
            <div class="tlg-phone__screen">
                <?php
                $listing = function_exists('locals_lofty_tailored_listing') ? locals_lofty_tailored_listing() : [];
                $l_img   = $listing['photo'] ?? '';
                $l_price = isset($listing['price']) ? locals_format_price($listing['price']) : '$90,000';
                $l_addr  = $listing['address'] ?? '789 Atlantic Ave #621';
                $l_loc   = trim(implode(', ', array_filter([$listing['city'] ?? 'Daytona Beach', $listing['state'] ?? 'FL'])));
                $l_photo = $l_img ?: locals_image_url(null, 'florida2.webp');
                ?>
                <div class="tlg-phone__photo"<?php echo $l_photo ? ' style="background-image:url(\'' . esc_url($l_photo) . '\');"' : ''; ?>>
                    <span class="tlg-phone__tag">For Sale</span>
                </div>
                <div class="tlg-phone__body">
                    <p class="tlg-phone__price"><?php echo esc_html($l_price); ?></p>
                    <p class="tlg-phone__addr"><?php echo esc_html($l_addr); ?></p>
                    <p class="tlg-phone__loc"><?php echo esc_html($l_loc); ?></p>
                    <p class="tlg-phone__beds"><strong>1</strong> Bath</p>
                </div>
            </div>
        </div>

        <div class="tlg-touch__copy">
            <p class="tlg-script tlg-touch__brand">The Locals <span>Group</span></p>
            <h2 class="tlg-display tlg-touch__title">Stay<br>In<br>Touch</h2>
            <p class="tlg-touch__pin"><span aria-hidden="true">&#9679;</span> <span class="tlg-script tlg-touch__learn">Learn the area</span></p>
            <p class="tlg-touch__body"><?php echo esc_html($touch_body); ?></p>
            <a class="tlg-btn tlg-btn--gold" href="<?php echo esc_url($touch_link); ?>">Stay in touch</a>
        </div>

        <div class="tlg-touch__agents" aria-hidden="false">
            <?php
            $featured = [$pick(7), $pick(8), $pick(9)];
            foreach ($featured as $j => $f) : if (!$f) continue;
                $first = explode(' ', $f['name'])[0];
                $rest  = trim(substr($f['name'], strlen($first)));
            ?>
                <figure class="tlg-touch__agent" style="--n:<?php echo $j; ?>;">
                    <img src="<?php echo esc_url($f['img']); ?>" alt="<?php echo esc_attr($f['name']); ?>">
                    <figcaption>
                        <span class="tlg-script tlg-touch__agent-first"><?php echo esc_html($first); ?></span>
                        <?php if ($rest) : ?><span class="tlg-touch__agent-last"><?php echo esc_html(strtoupper($rest)); ?></span><?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>

    <ul class="tlg-features">
        <li><strong>Local Expertise</strong><span>You can trust</span></li>
        <li><strong>Luxury Service</strong><span>Personal touch</span></li>
        <li><strong>Community First</strong><span>Always local</span></li>
    </ul>
    <p class="tlg-script tlg-tagline">Local Knowledge. Lasting Connections. Exceptional Results.</p>
</section>

<!-- ============================ 4. OUR LOCALS ============================ -->
<section class="tlg tlg-locals" data-reveal>
    <h2 class="tlg-display tlg-locals__title">Our Locals</h2>
    <ul class="tlg-locals__grid" data-reveal data-reveal-stagger="0.07">
        <?php
        $grid = array_slice($roster, 0, 6);
        foreach ($grid as $a) :
            $first = explode(' ', $a['name'])[0];
            $rest  = trim(substr($a['name'], strlen($first)));
            $tag   = $a['url'] && $a['url'] !== '#' ? 'a' : 'div';
        ?>
            <li class="tlg-local">
                <<?php echo $tag; ?> class="tlg-local__link"<?php echo $tag === 'a' ? ' href="' . esc_url($a['url']) . '"' : ''; ?>>
                    <span class="tlg-local__photo"><img src="<?php echo esc_url($a['img']); ?>" alt="<?php echo esc_attr($a['name']); ?>"></span>
                    <span class="tlg-local__name tlg-display">
                        <span><?php echo esc_html(strtoupper($first)); ?></span>
                        <?php if ($rest) : ?><span><?php echo esc_html(strtoupper($rest)); ?></span><?php endif; ?>
                    </span>
                </<?php echo $tag; ?>>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ============================ 5. JOIN THE TEAM ============================ -->
<?php
$join_cards = [
    ['eyebrow' => 'Become a Preferred',     'title' => 'Vendor',              'img' => 'florida3.webp', 'url' => $gf('join_vendor_url',    home_url('/join'))],
    ['eyebrow' => 'Become a Preferred',     'title' => 'Lender',              'img' => 'florida4.webp', 'url' => $gf('join_lender_url',    home_url('/join'))],
    ['eyebrow' => 'Become Our Preferred',   'title' => 'Business Affiliate',  'img' => 'florida5.webp', 'url' => $gf('join_affiliate_url', home_url('/join'))],
    ['eyebrow' => 'Partner With',           'title' => 'The Locals Group',    'img' => 'florida6.webp', 'url' => $gf('join_partner_url',   home_url('/join'))],
];
?>
<section class="tlg tlg-join" data-reveal>
    <h2 class="tlg-display tlg-join__title">Join The Team</h2>
    <ul class="tlg-join__grid" data-reveal data-reveal-stagger="0.08">
        <?php foreach ($join_cards as $c) : $img = locals_image_url(null, $c['img'], 'locals-card'); ?>
            <li class="tlg-join__card">
                <a href="<?php echo esc_url($c['url']); ?>">
                    <span class="tlg-join__media"<?php echo $img ? ' style="background-image:url(\'' . esc_url($img) . '\');"' : ''; ?>></span>
                    <span class="tlg-join__label">
                        <span class="tlg-join__eyebrow"><?php echo esc_html($c['eyebrow']); ?></span>
                        <span class="tlg-join__name tlg-display"><?php echo esc_html(strtoupper($c['title'])); ?></span>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="tlg-script tlg-tagline">Stronger Partnerships. Greater Impact. Lasting Success.</p>
</section>

<?php get_footer();
