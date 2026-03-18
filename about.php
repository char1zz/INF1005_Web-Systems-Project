<?php
require_once 'config/db.php';require_once 'includes/auth.php';
$pageTitle='About Us';include 'includes/header.php';
$instructors=[['name'=>'Charis','role'=>'Lead Spin Instructor','bio'=>'7 years of cycling coaching. Known for her infectious energy and music-driven sessions.'],['name'=>'Donavan','role'=>'HIIT Coach','bio'=>'Former national athlete. Brings military-grade intensity to every Core Blast and circuit session.'],['name'=>'Hana','role'=>'Endurance Specialist','bio'=>'Long-form ride expert who helps members build real aerobic capacity over time.'],['name'=>'Chanel','role'=>'Spin Instructor','bio'=>'A groovy, vibey spin instructor who turns every ride into a full-on party. Think good beats, smooth flows, and high-energy sessions that leave you sweaty, hyped, and ready for the next class.']];
?>
<div class="sf-section-head"><div class="sf-section-title">About SpinFit</div></div>
<div class="sf-about-grid" style="padding-top:0">
    <div>
        <h2 style="font-size:20px;font-weight:500;margin-bottom:14px">Our story</h2>
        <p style="font-size:14px;color:var(--ink-mid);line-height:1.75;margin-bottom:12px">SpinFit was founded in 2020 by a group of fitness enthusiasts who believed that high-energy group workouts could be both effective and addictive. What started as a single studio room has grown into Singapore's most energetic spin and HIIT community.</p>
        <p style="font-size:14px;color:var(--ink-mid);line-height:1.75;margin-bottom:20px">Our instructors are certified professionals who combine science-backed training with music-driven motivation — so every class feels less like a workout and more like a party you can't skip.</p>
        <a href="register.php" class="sf-nav-btn sf-nav-btn-solid" style="display:inline-block;text-decoration:none">Join us today</a>
    </div>
    <div style="background:var(--surface);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;min-height:240px;color:var(--ink-soft);font-size:13px">Studio photo</div>
</div>

<div style="padding:0 28px 40px">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border);border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:40px">
        <?php foreach([['Energy','Every class is designed to push you harder than you thought possible — fuelled by great music and instructors.'],['Community','SpinFit is more than a gym — it\'s a tribe. Our members support each other inside the studio and beyond.'],['Results','We combine Spin and HIIT — two of the most effective training methods — to maximise results.']] as[$title,$desc]): ?>
        <div style="background:#fff;padding:24px">
            <div style="font-size:15px;font-weight:500;margin-bottom:8px"><?= $title ?></div>
            <div style="font-size:13px;color:var(--ink-soft);line-height:1.65"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="font-size:16px;font-weight:500;margin-bottom:18px">Meet our instructors</div>
    <div class="sf-instructor-grid" style="padding:0;margin-bottom:40px">
        <?php foreach($instructors as $i): ?>
        <div class="sf-instructor-card">
            <div class="sf-instructor-avatar"><?= strtoupper(substr($i['name'],0,1)) ?></div>
            <div style="font-size:14px;font-weight:500;margin-bottom:2px"><?= htmlspecialchars($i['name']) ?></div>
            <div style="font-size:11px;color:var(--brand);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px"><?= htmlspecialchars($i['role']) ?></div>
            <div style="font-size:12px;color:var(--ink-soft);line-height:1.55"><?= htmlspecialchars($i['bio']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:28px">
        <div>
            <div style="font-size:15px;font-weight:500;margin-bottom:12px">Find us</div>
            <div style="font-size:13px;color:var(--ink-mid);line-height:1.75">
                <div style="font-weight:500;margin-bottom:4px">SpinFit Studio</div>
                <div>123 Orchard Road, #04-01</div><div>Singapore 238858</div>
                <div style="margin-top:8px">hello@spinfit.com.sg</div><div>+65 6123 4567</div>
            </div>
        </div>
        <div>
            <div style="font-size:15px;font-weight:500;margin-bottom:12px">Opening hours</div>
            <div style="font-size:13px;color:var(--ink-mid);line-height:1.9">
                <div>Mon – Fri &nbsp; 6:00 AM – 10:00 PM</div>
                <div>Sat – Sun &nbsp; 7:00 AM – 8:00 PM</div>
                <div style="margin-top:8px;color:var(--ink-soft)">Public holidays may vary.</div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
