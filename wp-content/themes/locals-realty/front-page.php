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
?>

<!-- ============================ 1. MEET THE LOCALS GROUP ============================ -->
<section class="tlg tlg-hero" data-reveal>
    <div class="tlg-hero__inner">
        <div class="tlg-hero__copy">
            <h1 class="tlg-display tlg-hero__title">Meet<br>The Locals<br>Group</h1>
            <p class="tlg-hero__lead"><?php echo esc_html($intro); ?></p>
            <div class="tlg-hero__cta">
                <a class="tlg-btn tlg-btn--gold" href="<?php echo esc_url($cta_find); ?>">Find your home</a>
                <a class="tlg-btn tlg-btn--ghost" href="<?php echo esc_url(home_url('/about')); ?>">Meet the team</a>
            </div>
        </div>
        <div class="tlg-hero__photo">
            <?php if ($group_photo) : ?>
                <img class="tlg-hero__group" src="<?php echo esc_url($group_photo); ?>" alt="The Locals Group team">
            <?php else : ?>
                <div class="tlg-hero__cluster" aria-hidden="true">
                    <?php for ($i = 0; $i < 5; $i++) : $p = $pick($i); if (!$p) continue; ?>
                        <figure class="tlg-hero__chip" style="--n:<?php echo $i; ?>;">
                            <img src="<?php echo esc_url($p['img']); ?>" alt="">
                        </figure>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================ 2. FIND / GET APPROVED / SELL ============================ -->
<section class="tlg tlg-action" data-reveal>
    <div class="tlg-action__bg" style="background-image:url('<?php echo esc_url($action_bg); ?>');" aria-hidden="true"></div>
    <div class="tlg-action__scrim" aria-hidden="true"></div>

    <p class="tlg-action__brand">
        <span class="tlg-script">The Locals</span>
        <span class="tlg-action__brand-sub">GROUP &middot; lpt realty</span>
    </p>

    <?php $buyer = $pick(5); $seller = $pick(6); ?>
    <?php if ($buyer) : ?>
        <figure class="tlg-action__agent tlg-action__agent--left"><img src="<?php echo esc_url($buyer['img']); ?>" alt=""></figure>
    <?php endif; ?>
    <?php if ($seller) : ?>
        <figure class="tlg-action__agent tlg-action__agent--right"><img src="<?php echo esc_url($seller['img']); ?>" alt=""></figure>
    <?php endif; ?>

    <div class="tlg-action__row">
        <a class="tlg-cta tlg-cta--find" href="<?php echo esc_url($cta_find); ?>">
            <span class="tlg-cta__small">Find</span>
            <span class="tlg-cta__big">Homes</span>
        </a>
        <a class="tlg-cta tlg-cta--approve" href="<?php echo esc_url($cta_approve); ?>">
            <span class="tlg-cta__small">Get</span>
            <span class="tlg-cta__big">Approved</span>
        </a>
        <a class="tlg-cta tlg-cta--sell" href="<?php echo esc_url($cta_sell); ?>">
            <span class="tlg-cta__small">Sell</span>
            <span class="tlg-cta__big">Homes</span>
        </a>
    </div>
</section>

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
