<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── Handle custom skill add / remove (pure PHP, no JS) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_custom_skill'], $_POST['custom_type'])) {
        $type = ($_POST['custom_type'] === 'learn') ? 'learn' : 'teach';
        $val  = trim($_POST['add_custom_skill'] ?? '');
        if ($val !== '' && mb_strlen($val) <= 80) {
            if (!isset($_SESSION['custom_skills'][$type])) {
                $_SESSION['custom_skills'][$type] = [];
            }
            $already = array_map('strtolower', $_SESSION['custom_skills'][$type]);
            if (!in_array(strtolower($val), $already)) {
                $_SESSION['custom_skills'][$type][] = $val;
            }
        }
        header("Location: profile.php");
        exit();
    }

    if (isset($_POST['remove_custom_skill'], $_POST['custom_type'])) {
        $type = ($_POST['custom_type'] === 'learn') ? 'learn' : 'teach';
        $idx  = (int)$_POST['remove_custom_skill'];
        if (isset($_SESSION['custom_skills'][$type][$idx])) {
            array_splice($_SESSION['custom_skills'][$type], $idx, 1);
            $_SESSION['custom_skills'][$type] = array_values($_SESSION['custom_skills'][$type]);
        }
        header("Location: profile.php");
        exit();
    }
}

$custom_teach = isset($_SESSION['custom_skills']['teach']) ? array_values($_SESSION['custom_skills']['teach']) : [];
$custom_learn = isset($_SESSION['custom_skills']['learn']) ? array_values($_SESSION['custom_skills']['learn']) : [];

// ── Fetch user details ────────────────────────────────────────
$stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name, bio, user_location AS location FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user    = $stmt->get_result()->fetch_assoc();
$initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));

// ── Fetch all system skills ───────────────────────────────────
$skills_master = $conn->query("SELECT skill_id, skill_name FROM skills ORDER BY skill_name ASC");
$skills_list   = [];
if ($skills_master) {
    while ($row = $skills_master->fetch_assoc()) {
        $skills_list[] = $row;
    }
}

// ── Auto-categorize ───────────────────────────────────────────
function getSkillCategory(string $skill_name): string {
    $name = strtolower(trim($skill_name));
    $exact = [
        'java'=>'Technical','r'=>'Technical','c'=>'Technical','go'=>'Technical',
        'ai'=>'Technical','ui/ux design'=>'Technical','ui/ux'=>'Technical','networking'=>'Technical',
        'english writing'=>'Creative','graphic design'=>'Creative','guitar'=>'Creative',
        'interior design'=>'Creative','fashion design'=>'Creative','music production'=>'Creative',
        'essay writing'=>'Analytical','academic writing'=>'Analytical','essay'=>'Analytical',
        'english'=>'Interpersonal','public speaking'=>'Interpersonal',
    ];
    if (isset($exact[$name])) return $exact[$name];
    $technical    = ['machine learning','data science','web development','game development','data analysis','mobile app','it support','video editing','after effects','3d printing','javascript','typescript','blockchain','cybersecurity','programming','software','database','devops','linux','arduino','robotics','electronics','autocad','engineering','computer','android','unity','photoshop','premiere','figma','react','swift','kotlin','python','coding','mysql','html','css','sql','php','node','rust','cloud','tech','ios','ux','ui','c++','c#','excel'];
    $creative     = ['graphic design','creative writing','food styling','music production','cake decorating','digital art','illustration','photography','videography','animation','sketching','calligraphy','henna','tattoo','crafts','knitting','sewing','embroidery','pottery','sculpting','jewelry','woodworking','fashion','styling','interior','songwriting','composing','storytelling','choreography','filmmaking','drawing','painting','design','art','music','guitar','piano','violin','drums','singing','poetry','writing','dance','acting','theatre','cooking','baking','cake','pastry','origami','scrapbooking'];
    $analytical   = ['mathematics','statistics','project management','market research','critical thinking','problem solving','academic writing','essay writing','business analysis','legal research','data analysis','math','physics','chemistry','biology','research','science','finance','accounting','economics','investing','budgeting','analysis','logic','philosophy','law','legal','audit','consulting','strategy','business','marketing','seo','chess','debate','essay','academic','study','tutoring'];
    $interpersonal= ['sign language','public speaking','conflict resolution','event planning','communication','presentation','leadership','mentoring','coaching','counseling','mindfulness','negotiation','volunteering','language','spanish','french','german','arabic','chinese','japanese','korean','portuguese','italian','hindi','therapy','meditation','yoga','teaching','parenting','social','relationship','community','travel','culture','translation'];
    foreach ($technical     as $kw) { if (str_contains($name, $kw)) return 'Technical'; }
    foreach ($creative      as $kw) { if (str_contains($name, $kw)) return 'Creative'; }
    foreach ($analytical    as $kw) { if (str_contains($name, $kw)) return 'Analytical'; }
    foreach ($interpersonal as $kw) { if (str_contains($name, $kw)) return 'Interpersonal'; }
    return 'Other';
}

$category_order  = ['Technical','Creative','Analytical','Interpersonal','Other'];
$category_icons  = ['Technical'=>'⚙️','Creative'=>'🎨','Analytical'=>'📊','Interpersonal'=>'🌐','Other'=>'✨'];
$category_colors = ['Technical'=>'#6366f1','Creative'=>'#ec4899','Analytical'=>'#f59e0b','Interpersonal'=>'#06b6d4','Other'=>'#8b5cf6'];

$grouped_skills = array_fill_keys($category_order, []);
foreach ($skills_list as $skill) {
    $grouped_skills[getSkillCategory($skill['skill_name'])][] = $skill;
}
$grouped_skills = array_filter($grouped_skills);

// ── Fetch user's current skills ───────────────────────────────
$current_teach = [];
$current_learn = [];
$res_stmt = $conn->prepare("SELECT skill_id, type_name FROM user_skills WHERE user_id = ?");
$res_stmt->bind_param("i", $user_id);
$res_stmt->execute();
$res = $res_stmt->get_result();
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $t = strtolower(trim($row['type_name'] ?? ''));
        if ($t === 'teach') $current_teach[] = (int)$row['skill_id'];
        elseif ($t === 'learn') $current_learn[] = (int)$row['skill_id'];
    }
}

$grads = ['linear-gradient(135deg,#7c3aed,#ec4899)','linear-gradient(135deg,#06b6d4,#7c3aed)','linear-gradient(135deg,#f59e0b,#ec4899)','linear-gradient(135deg,#10b981,#06b6d4)'];
$grad  = $grads[$user_id % count($grads)];
?>

<style>
.prof-wrap  { max-width: 900px; margin: 40px auto; padding: 0 20px 60px; }
.prof-card  { background: #16161d; border: 1px solid #2a2a35; border-radius: 24px; padding: 40px; margin-bottom: 24px; }
.prof-header { display: flex; align-items: center; gap: 24px; margin-bottom: 36px; border-bottom: 1px solid #2a2a35; padding-bottom: 28px; }
.prof-av     { width: 90px; height: 90px; border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; font-weight: 800; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.2); }
.prof-title  { font-size: 1.75rem; font-weight: 800; color: #e0e0f0; margin-bottom: 4px; }
.prof-subtitle { color: #505060; font-size: .9rem; }

.form-grid  { display: grid; gap: 24px; margin-bottom: 32px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: .8rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: .05em; }
.form-input, .form-textarea { background: #0f0f13; border: 1px solid #2a2a35; border-radius: 12px; padding: 14px 16px; color: #e0e0f0; font-size: .95rem; font-family: inherit; transition: all .2s; width: 100%; box-sizing: border-box; }
.form-input:focus, .form-textarea:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
.form-textarea { height: 120px; resize: vertical; }

.skills-columns-wrap { display: grid; grid-template-columns: repeat(auto-fit,minmax(300px,1fr)); gap: 24px; margin-bottom: 36px; }
.skills-matrix-title { font-size: 1.1rem; font-weight: 700; color: #e0e0f0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.skills-block { background: #0f0f13; border: 1px solid #2a2a35; border-radius: 16px; padding: 20px; }

/* pure-CSS accordion */
.cat-toggle          { display: none; }
.skill-category      { margin-bottom: 6px; border-radius: 10px; overflow: hidden; border: 1px solid #2a2a35; }
.skill-category-header { display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; cursor: pointer; background: #16161d; transition: background .15s; }
.skill-category-header:hover { background: #1c1c26; }
.skill-category-label   { display: flex; align-items: center; gap: 8px; font-size: .82rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
.skill-category-dot     { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.skill-category-count   { font-size: .72rem; color: #505060; background: #0f0f13; padding: 2px 7px; border-radius: 99px; border: 1px solid #2a2a35; }
.skill-category-chevron { font-size: .7rem; color: #505060; display: inline-block; transition: transform .2s; }
.skill-category-body    { display: none; padding: 6px 8px 10px; background: #0f0f13; }
.cat-toggle:checked ~ .skill-category-header .skill-category-chevron { transform: rotate(180deg); }
.cat-toggle:checked ~ .skill-category-body { display: block; }

.skill-check-row { display: flex; align-items: center; gap: 12px; padding: 7px 10px; border-radius: 8px; cursor: pointer; font-size: .88rem; color: #a0a0b0; transition: background .12s, color .12s; }
.skill-check-row:hover { background: #16161d; color: #e0e0f0; }

/* custom adder */
.custom-skill-box       { margin-top: 10px; border: 1px dashed #2a2a35; border-radius: 10px; padding: 12px; background: #0d0d12; }
.custom-skill-box-title { font-size: .75rem; font-weight: 700; color: #505060; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }
.custom-skill-input-row { display: flex; gap: 8px; }
.custom-skill-input     { flex: 1; background: #16161d; border: 1px solid #2a2a35; border-radius: 8px; padding: 8px 12px; color: #e0e0f0; font-size: .85rem; font-family: inherit; outline: none; transition: border-color .2s; min-width: 0; }
.custom-skill-input:focus { border-color: #6366f1; }
.custom-skill-add-btn   { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
.custom-skill-add-btn:hover { opacity: .85; }
.custom-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; margin-bottom: 4px; }
.custom-tag  { display: inline-flex; align-items: center; gap: 6px; background: #1e1e28; border: 1px solid #2a2a35; border-radius: 99px; padding: 4px 6px 4px 12px; font-size: .8rem; color: #c0c0d0; }
.custom-tag-remove { background: #0f0f13; border: 1px solid #2a2a35; color: #505060; cursor: pointer; font-size: .75rem; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0; transition: color .15s, border-color .15s; line-height: 1; }
.custom-tag-remove:hover { color: #ef4444; border-color: #ef4444; }

.btn           { padding: 14px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: .95rem; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all .2s; }
.btn-primary   { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; box-shadow: 0 4px 14px rgba(99,102,241,.3); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.4); }
.btn-secondary { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; }
.btn-secondary:hover { background: #252532; color: #e0e0f0; }
.btn-lg { padding: 16px 32px; font-size: 1rem; }
.btn-sm { padding: 8px 16px; font-size: .85rem; border-radius: 8px; }
</style>

<?php
// ── Helper: render one skill column (teach or learn) ─────────
function renderSkillColumn(
    string $type,            // 'teach' or 'learn'
    array  $grouped_skills,
    array  $category_colors,
    array  $category_icons,
    array  $current_ids,     // skill_ids already saved for this user/type
    array  $custom_items     // custom skills in session for this type
): void {
    $accent = ($type === 'teach') ? '' : '#ec4899';
    foreach ($grouped_skills as $cat => $cat_skills):
        $color     = $category_colors[$cat];
        $icon      = $category_icons[$cat];
        $n_checked = count(array_filter($cat_skills, fn($s) => in_array($s['skill_id'], $current_ids)));
        $cat_id    = $type . '-' . preg_replace('/\W+/', '-', strtolower($cat));
        $field     = $type . '_skills[]';
        $acc       = ($type === 'teach') ? $color : '#ec4899';
?>
    <div class="skill-category">
        <input type="checkbox" class="cat-toggle" id="<?= $cat_id ?>"
            <?= $n_checked > 0 ? 'checked' : '' ?>>
        <label class="skill-category-header" for="<?= $cat_id ?>">
            <span class="skill-category-label">
                <span class="skill-category-dot" style="background:<?= $color ?>"></span>
                <?= $icon ?> <?= $cat ?>
            </span>
            <span style="display:flex;align-items:center;gap:8px;">
                <span class="skill-category-count"><?= count($cat_skills) ?></span>
                <span class="skill-category-chevron">▼</span>
            </span>
        </label>
        <div class="skill-category-body">
            <?php foreach ($cat_skills as $skill): ?>
            <label class="skill-check-row">
                <input type="checkbox" name="<?= $field ?>"
                    value="<?= $skill['skill_id'] ?>"
                    <?= in_array($skill['skill_id'], $current_ids) ? 'checked' : '' ?>
                    style="accent-color:<?= $acc ?>">
                <?= htmlspecialchars($skill['skill_name']) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    endforeach;

    // Show existing custom tags + their remove buttons
    if (!empty($custom_items)): ?>
    <div class="custom-tags">
        <?php foreach ($custom_items as $i => $cs): ?>
        <span class="custom-tag">
            <?= htmlspecialchars($cs) ?>
            <?php /* remove button is its own standalone form — NOT inside the main save form */ ?>
        </span>
        <?php endforeach; ?>
    </div>
    <?php endif;
}
?>

<div class="prof-wrap">

    <!-- ── STANDALONE REMOVE FORMS (outside main form) ──────── -->
    <?php foreach ($custom_teach as $i => $cs): ?>
    <form method="POST" action="profile.php" style="display:none;" id="rm-teach-<?= $i ?>">
        <input type="hidden" name="custom_type" value="teach">
        <input type="hidden" name="remove_custom_skill" value="<?= $i ?>">
    </form>
    <?php endforeach; ?>
    <?php foreach ($custom_learn as $i => $cs): ?>
    <form method="POST" action="profile.php" style="display:none;" id="rm-learn-<?= $i ?>">
        <input type="hidden" name="custom_type" value="learn">
        <input type="hidden" name="remove_custom_skill" value="<?= $i ?>">
    </form>
    <?php endforeach; ?>

    <!-- ── STANDALONE ADD FORMS (outside main form) ─────────── -->
    <form method="POST" action="profile.php" id="add-teach-form" style="display:none;">
        <input type="hidden" name="custom_type" value="teach">
        <input type="text"   name="add_custom_skill" id="add-teach-val" maxlength="80">
    </form>
    <form method="POST" action="profile.php" id="add-learn-form" style="display:none;">
        <input type="hidden" name="custom_type" value="learn">
        <input type="text"   name="add_custom_skill" id="add-learn-val" maxlength="80">
    </form>

    <div class="prof-card">
        <div class="prof-header">
            <div class="prof-av" style="background:<?= $grad ?>"><?= $initial ?></div>
            <div>
                <div class="prof-title"><?= htmlspecialchars($user['name'] ?? 'Your Profile') ?></div>
                <div class="prof-subtitle">Manage your bio, location, and skill categories.</div>
            </div>
        </div>

        <!-- ── MAIN SAVE FORM ─────────────────────────────────── -->
        <form action="update_profile.php" method="POST" id="main-save-form">

            <!-- Custom skills in session passed as hidden inputs to save form -->
            <?php foreach ($custom_teach as $cs): ?>
            <input type="hidden" name="custom_teach_skills[]" value="<?= htmlspecialchars($cs) ?>">
            <?php endforeach; ?>
            <?php foreach ($custom_learn as $cs): ?>
            <input type="hidden" name="custom_learn_skills[]" value="<?= htmlspecialchars($cs) ?>">
            <?php endforeach; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Physical Location</label>
                    <input type="text" name="location" class="form-input"
                        placeholder="e.g., Copenhagen, Denmark"
                        value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Personal Biography</label>
                    <textarea name="bio" class="form-textarea"
                        placeholder="Tell the community about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="skills-columns-wrap">

                <!-- TEACH -->
                <div>
                    <div class="skills-matrix-title">🎓 Skills I Can Teach</div>
                    <div class="skills-block">

                        <?php foreach ($grouped_skills as $cat => $cat_skills):
                            $color     = $category_colors[$cat];
                            $icon      = $category_icons[$cat];
                            $n_checked = count(array_filter($cat_skills, fn($s) => in_array($s['skill_id'], $current_teach)));
                            $cat_id    = 'teach-' . preg_replace('/\W+/','-',strtolower($cat));
                        ?>
                        <div class="skill-category">
                            <input type="checkbox" class="cat-toggle" id="<?= $cat_id ?>"
                                <?= $n_checked > 0 ? 'checked' : '' ?>>
                            <label class="skill-category-header" for="<?= $cat_id ?>">
                                <span class="skill-category-label">
                                    <span class="skill-category-dot" style="background:<?= $color ?>"></span>
                                    <?= $icon ?> <?= $cat ?>
                                </span>
                                <span style="display:flex;align-items:center;gap:8px;">
                                    <span class="skill-category-count"><?= count($cat_skills) ?></span>
                                    <span class="skill-category-chevron">▼</span>
                                </span>
                            </label>
                            <div class="skill-category-body">
                                <?php foreach ($cat_skills as $skill): ?>
                                <label class="skill-check-row">
                                    <input type="checkbox" name="teach_skills[]"
                                        value="<?= $skill['skill_id'] ?>"
                                        <?= in_array($skill['skill_id'], $current_teach) ? 'checked' : '' ?>
                                        style="accent-color:<?= $color ?>">
                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Custom teach tags (with remove buttons linking to standalone forms) -->
                        <?php if (!empty($custom_teach)): ?>
                        <div class="custom-tags">
                            <?php foreach ($custom_teach as $i => $cs): ?>
                            <span class="custom-tag">
                                <?= htmlspecialchars($cs) ?>
                                <button type="button" class="custom-tag-remove"
                                    onclick="document.getElementById('rm-teach-<?= $i ?>').submit()"
                                    title="Remove">✕</button>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Add teach skill box (submits standalone add form) -->
                        <div class="custom-skill-box">
                            <div class="custom-skill-box-title">✦ Not in the list? Add your own</div>
                            <div class="custom-skill-input-row">
                                <input type="text" id="teach-input" class="custom-skill-input"
                                    placeholder="e.g. Latte Art, Robotics..." maxlength="80">
                                <button type="button" class="custom-skill-add-btn"
                                    onclick="submitAdd('teach')">+ Add</button>
                            </div>
                        </div>

                    </div>
                </div><!-- /teach -->

                <!-- LEARN -->
                <div>
                    <div class="skills-matrix-title">🎯 Skills I Want to Learn</div>
                    <div class="skills-block">

                        <?php foreach ($grouped_skills as $cat => $cat_skills):
                            $color     = $category_colors[$cat];
                            $icon      = $category_icons[$cat];
                            $n_checked = count(array_filter($cat_skills, fn($s) => in_array($s['skill_id'], $current_learn)));
                            $cat_id    = 'learn-' . preg_replace('/\W+/','-',strtolower($cat));
                        ?>
                        <div class="skill-category">
                            <input type="checkbox" class="cat-toggle" id="<?= $cat_id ?>"
                                <?= $n_checked > 0 ? 'checked' : '' ?>>
                            <label class="skill-category-header" for="<?= $cat_id ?>">
                                <span class="skill-category-label">
                                    <span class="skill-category-dot" style="background:<?= $color ?>"></span>
                                    <?= $icon ?> <?= $cat ?>
                                </span>
                                <span style="display:flex;align-items:center;gap:8px;">
                                    <span class="skill-category-count"><?= count($cat_skills) ?></span>
                                    <span class="skill-category-chevron">▼</span>
                                </span>
                            </label>
                            <div class="skill-category-body">
                                <?php foreach ($cat_skills as $skill): ?>
                                <label class="skill-check-row">
                                    <input type="checkbox" name="learn_skills[]"
                                        value="<?= $skill['skill_id'] ?>"
                                        <?= in_array($skill['skill_id'], $current_learn) ? 'checked' : '' ?>
                                        style="accent-color:#ec4899">
                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Custom learn tags -->
                        <?php if (!empty($custom_learn)): ?>
                        <div class="custom-tags">
                            <?php foreach ($custom_learn as $i => $cs): ?>
                            <span class="custom-tag">
                                <?= htmlspecialchars($cs) ?>
                                <button type="button" class="custom-tag-remove"
                                    onclick="document.getElementById('rm-learn-<?= $i ?>').submit()"
                                    title="Remove">✕</button>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Add learn skill box -->
                        <div class="custom-skill-box">
                            <div class="custom-skill-box-title">✦ Not in the list? Add your own</div>
                            <div class="custom-skill-input-row">
                                <input type="text" id="learn-input" class="custom-skill-input"
                                    placeholder="e.g. Latte Art, Robotics..." maxlength="80">
                                <button type="button" class="custom-skill-add-btn"
                                    onclick="submitAdd('learn')">+ Add</button>
                            </div>
                        </div>

                    </div>
                </div><!-- /learn -->

            </div><!-- /skills-columns-wrap -->

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
                    Save Changes →
                </button>
                <a href="view_profile.php?id=<?= $user_id ?>" class="btn btn-secondary btn-lg">
                    View Profile
                </a>
            </div>

        </form><!-- /main-save-form -->
    </div><!-- /prof-card -->

    <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;">
        <a href="dashboard.php" class="btn btn-secondary btn-sm">← Dashboard</a>
        <a href="discovery.php" class="btn btn-secondary btn-sm">Discover People</a>
        <a href="chat.php"      class="btn btn-secondary btn-sm">My Messages</a>
    </div>
</div>

<script>
function submitAdd(type) {
    var val = document.getElementById(type + '-input').value.trim();
    if (!val) return;
    document.getElementById('add-' + type + '-val').value = val;
    document.getElementById('add-' + type + '-form').submit();
}
// Allow Enter key in the text boxes
document.getElementById('teach-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); submitAdd('teach'); }
});
document.getElementById('learn-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); submitAdd('learn'); }
});
</script>

<?php include 'includes/footer.php'; ?>