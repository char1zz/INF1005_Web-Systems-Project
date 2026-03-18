<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="sf-hero">
    <div style="max-width:600px">
        <div class="sf-hero-badge">Singapore's premier fitness studio</div>
        <h1>Push Your<br><em>Limits</em><br>Every Day</h1>
        <p class="sf-hero-sub">High-energy Spin &amp; HIIT classes designed to transform your body and energise your mind. Book your spot in minutes.</p>
        <div class="sf-hero-btns">
            <a class="sf-nav-btn sf-nav-btn-solid" href="/spinfit/classes.php" style="padding:12px 28px;font-size:13px">Book a class</a>
            <a class="sf-nav-btn" href="/spinfit/membership_plans.php" style="padding:12px 28px;font-size:13px;border-color:rgba(255,255,255,.3);color:#fff;background:none">View plans</a>
        </div>
        <div class="sf-hero-stats">
            <div><div class="sf-hero-stat-num">12+</div><div class="sf-hero-stat-label">Weekly classes</div></div>
            <div><div class="sf-hero-stat-num">6</div><div class="sf-hero-stat-label">Instructors</div></div>
            <div><div class="sf-hero-stat-num">500+</div><div class="sf-hero-stat-label">Members</div></div>
            <div><div class="sf-hero-stat-num">3</div><div class="sf-hero-stat-label">Studio rooms</div></div>
        </div>
    </div>
</div>

<!-- Our classes -->
<div class="sf-section-head" style="margin-top:8px">
    <div class="sf-section-title">Our classes</div>
    <a href="/spinfit/classes.php" style="font-size:13px;color:var(--brand)">View schedule →</a>
</div>

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:var(--border);margin:0 28px 40px;border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
    <?php
    $classes = [
        [
            'type'=>'spin',
            'name'=>'Rhythm Ride',
            'desc'=>'High-intensity cycling to the beat. Build endurance, torch calories.',
            'dur'=>'45 min',
            'level'=>'All levels',
            'room'=>'Studio A',
            'benefits'=>['Boosts cardiovascular endurance','Burns calories through rhythm-based intervals','Improves coordination and riding confidence'],
            'expect'=>['Beat-driven ride with climbs, sprints, and jumps','High-energy playlist and immersive studio atmosphere','Sweaty but beginner-friendly challenge'],
            'gear'=>['Spin shoes or supportive trainers','Water bottle','Sweat towel'],
            'shop'=>['Grip Water Bottle','Spin Shoe Clips','Performance Tee']
        ],
        [
            'type'=>'hiit',
            'name'=>'Power Circuit',
            'desc'=>'Fast-paced intervals that build stamina and total-body power.',
            'dur'=>'30 min',
            'level'=>'Intermediate',
            'room'=>'Studio B',
            'benefits'=>['Builds strength and cardio capacity together','Improves agility and explosive power','Efficient full-body conditioning in less time'],
            'expect'=>['Short bursts of work with quick recoveries','Bodyweight drills, cardio rounds, and circuit stations','High intensity with modifications when needed'],
            'gear'=>['Training mat','Water bottle','Towel'],
            'shop'=>['HIIT Gloves','Compression Shorts','Protein Shaker']
        ],
        [
            'type'=>'spin',
            'name'=>'Endurance Climb',
            'desc'=>'Long-form ride with simulated hill climbs to build aerobic capacity.',
            'dur'=>'60 min',
            'level'=>'Advanced',
            'room'=>'Studio A',
            'benefits'=>['Builds lasting stamina and aerobic endurance','Strengthens legs through resistance climbs','Improves pacing and mental grit'],
            'expect'=>['Longer ride blocks with sustained resistance','Progressive climbs, tempo pushes, and controlled recovery','A more demanding ride for experienced riders'],
            'gear'=>['Spin shoes','Electrolyte bottle','Sweat towel'],
            'shop'=>['Spin Shoe Clips','Grip Water Bottle','Performance Tee']
        ],
        [
            'type'=>'hiit',
            'name'=>'Core Blast',
            'desc'=>'Targeted core and cardio fusion — six-pack science meets intensity.',
            'dur'=>'30 min',
            'level'=>'All levels',
            'room'=>'Studio C',
            'benefits'=>['Strengthens core stability and posture','Supports balance, control, and injury prevention','Adds a strong cardio burn in a short class'],
            'expect'=>['Focused ab work mixed with quick cardio pushes','Mat-based circuits and standing core drills','Fast, fiery, and scalable for all levels'],
            'gear'=>['Training mat','Grip gloves','Water bottle'],
            'shop'=>['HIIT Gloves','Compression Shorts','Protein Shaker']
        ],
    ];
    foreach ($classes as $c): ?>
    <button type="button" class="sf-class-card sf-class-modal-trigger" data-class-modal='<?= htmlspecialchars(json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT), ENT_QUOTES, "UTF-8") ?>'>
        <span class="badge-<?= $c['type'] ?>" style="margin-bottom:12px;display:inline-block"><?= strtoupper($c['type']) ?></span>
        <h3 style="font-size:16px;font-weight:500;margin-bottom:8px"><?= $c['name'] ?></h3>
        <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;margin-bottom:14px"><?= $c['desc'] ?></p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
            <span style="font-size:11px;background:var(--surface);padding:3px 9px;border-radius:4px;color:var(--ink-mid)"><?= $c['dur'] ?></span>
            <span style="font-size:11px;background:var(--surface);padding:3px 9px;border-radius:4px;color:var(--ink-mid)"><?= $c['level'] ?></span>
            <span style="font-size:11px;background:var(--surface);padding:3px 9px;border-radius:4px;color:var(--ink-mid)"><?= $c['room'] ?></span>
        </div>
        <span class="sf-card-link">View class details →</span>
    </button>
    <?php endforeach; ?>

</div>

<div class="sf-class-modal" id="sf-class-modal" aria-hidden="true">
    <div class="sf-class-modal-backdrop" data-class-modal-close></div>
    <div class="sf-class-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="sf-class-modal-title">
        <button type="button" class="sf-class-modal-close" data-class-modal-close aria-label="Close">×</button>
        <div class="sf-class-modal-head">
            <span class="sf-class-modal-badge" id="sf-class-modal-badge"></span>
            <h3 id="sf-class-modal-title"></h3>
            <p id="sf-class-modal-desc"></p>
        </div>
        <div class="sf-class-modal-meta">
            <span id="sf-class-modal-dur"></span>
            <span id="sf-class-modal-level"></span>
            <span id="sf-class-modal-room"></span>
        </div>
        <div class="sf-class-modal-grid">
            <div>
                <div class="sf-class-modal-subtitle">Benefits</div>
                <ul id="sf-class-modal-benefits" class="sf-class-modal-list"></ul>
            </div>
            <div>
                <div class="sf-class-modal-subtitle">What to expect</div>
                <ul id="sf-class-modal-expect" class="sf-class-modal-list"></ul>
            </div>
            <div>
                <div class="sf-class-modal-subtitle">Recommended gear</div>
                <ul id="sf-class-modal-gear" class="sf-class-modal-list"></ul>
            </div>
            <div>
                <div class="sf-class-modal-subtitle">Suggested shop picks</div>
                <ul id="sf-class-modal-shop" class="sf-class-modal-list"></ul>
            </div>
        </div>
        <div class="sf-class-modal-actions">
            <a href="/spinfit/classes.php" class="sf-nav-btn sf-nav-btn-solid">Book this class</a>
            <a href="/spinfit/shop/shop.php" class="sf-nav-btn sf-nav-btn-outline">Shop essentials</a>
        </div>
    </div>
</div>

<!-- CTA banner -->
<div style="background:var(--brand);color:#fff;padding:48px 28px;text-align:center;margin:0 28px 40px;border-radius:var(--radius-lg)">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;opacity:.8;margin-bottom:10px">Join SpinFit</div>
    <h2 style="font-size:28px;font-weight:500;margin-bottom:10px">Ready to start your journey?</h2>
    <p style="font-size:14px;opacity:.8;margin-bottom:24px">Join hundreds of members already transforming their fitness.</p>
    <a href="/spinfit/register.php" style="display:inline-block;background:#fff;color:var(--brand);padding:13px 36px;border-radius:var(--radius-md);font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase">Get started free</a>
</div>

<!-- Shop teaser -->
<div class="sf-section-head">
    <div class="sf-section-title">Shop</div>
    <a href="/spinfit/shop/shop.php" style="font-size:13px;color:var(--brand)">Shop all →</a>
</div>
<?php
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:var(--border);margin:0 28px 40px;border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
    <?php foreach ($products as $p): ?>
    <a href="/spinfit/shop/product_detail.php?id=<?= $p['id'] ?>" style="display:block;background:#fff;text-decoration:none" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='#fff'">
        <div style="aspect-ratio:1;background:var(--surface);display:flex;align-items:center;justify-content:center">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect x="10" y="10" width="28" height="28" rx="4" stroke="#ccc" stroke-width="1.5"/></svg>
        </div>
        <div style="padding:14px">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-soft);margin-bottom:4px"><?= htmlspecialchars($p['category'] ?? '') ?></div>
            <div style="font-size:13px;font-weight:500;margin-bottom:6px"><?= htmlspecialchars($p['name']) ?></div>
            <div style="font-size:13px;font-weight:500">$<?= number_format($p['price'],2) ?></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
