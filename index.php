<?php include 'db.php'; include 'includes/header.php'; ?>

<!-- ── HERO ── -->
<section style="text-align:center;padding:90px 6% 70px;position:relative;overflow:hidden;">
    <div style="position:absolute;width:600px;height:400px;background:radial-gradient(ellipse,rgba(124,58,237,.22) 0%,transparent 70%);top:-100px;left:-100px;pointer-events:none;"></div>
    <div style="position:absolute;width:500px;height:400px;background:radial-gradient(ellipse,rgba(236,72,153,.18) 0%,transparent 70%);bottom:-80px;right:-80px;pointer-events:none;"></div>

    <div style="position:relative;z-index:1;">
        <div class="badge badge-violet" style="margin-bottom:20px;font-size:.85rem;">⚡ Skill-for-skill exchange platform</div>

        <h1 style="font-size:clamp(2.8rem,7vw,5rem);margin-bottom:20px;line-height:1.05;">
            What do you want<br>
            <span class="text-grad">to learn today?</span>
        </h1>

        <p style="color:var(--text2);font-size:1.15rem;max-width:560px;margin:0 auto 44px;">
            Trade what you know for what you want. No money needed — just skills, passion, and community.
        </p>

        <form action="discovery.php" method="GET" style="max-width:600px;margin:0 auto 28px;">
            <div class="search-bar">
                <input type="text" name="search" placeholder="I want to learn… e.g. Python, Guitar, Spanish">
                <button type="submit">Match Me!</button>
            </div>
        </form>

        <div class="tag-pills">
            <span class="text-muted text-sm" style="align-self:center;">Popular:</span>
            <a href="discovery.php?search=Tech"    class="tag-pill-item">💻 Tech</a>
            <a href="discovery.php?search=Arts"    class="tag-pill-item">🎨 Arts</a>
            <a href="discovery.php?search=Cooking" class="tag-pill-item">🍳 Cooking</a>
            <a href="discovery.php?search=Music"   class="tag-pill-item">🎵 Music</a>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section style="padding:60px 6%;background:var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <h2 style="text-align:center;font-size:2rem;margin-bottom:40px;">How SkillSwap works</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;max-width:900px;margin:0 auto;">
        <?php
        $steps = [
            ['🧑‍💼','Create your profile','List what you can teach and what you want to learn.','badge-violet','profile.php'],
            ['🔍','Find your match','Search by skill and discover the perfect swap partner.','badge-cyan','discovery.php'],
            ['💬','Start a conversation','Message, share files, or hop on a video call.','badge-pink','chat.php'],
            ['⚡','Swap & grow','Exchange sessions and level up together.','badge-amber','matches.php'],
        ];
        foreach ($steps as $i => [$icon,$title,$desc,$badge,$link]):
        ?>
        <a href="<?= $link ?>" style="text-decoration:none;" title="<?= $title ?>">
            <div class="card card-glow" style="text-align:center;cursor:pointer;transition:transform 0.2s,border-color 0.2s;" 
                 onmouseover="this.style.transform='translateY(-4px)';this.style.borderColor='#6366f1';" 
                 onmouseout="this.style.transform='translateY(0)';this.style.borderColor='';">
                <div style="font-size:2.4rem;margin-bottom:12px;"><?= $icon ?></div>
                <span class="badge <?= $badge ?>" style="margin-bottom:12px;">Step <?= $i+1 ?></span>
                <h3 style="font-size:1.05rem;margin-bottom:8px;"><?= $title ?></h3>
                <p class="text-muted text-sm"><?= $desc ?></p>
                <p style="margin-top:12px;font-size:0.78rem;color:#6366f1;font-weight:700;">Go →</p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── CTA ── -->
<section style="text-align:center;padding:80px 6%;">
    <h2 style="font-size:clamp(2rem,5vw,3rem);margin-bottom:16px;">
        Ready to <span class="text-grad">start swapping?</span>
    </h2>
    <p class="text-muted" style="margin-bottom:36px;font-size:1.05rem;">
        Join thousands of people trading skills every day. It's free — always.
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
        <a href="register.php" class="btn btn-primary btn-lg">Join Free →</a>
        <a href="discovery.php" class="btn btn-secondary btn-lg">Browse Skills</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>