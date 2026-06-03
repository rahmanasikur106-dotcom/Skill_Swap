<?php include 'db.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <style>
        .php-notification {
            padding: 15px;
            margin-bottom: 20px;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            background: <?php echo ($_GET['type'] == 'success') ? '#28a745' : '#dc3545'; ?>;
            
            /* The '10s' below is the timer. 'forwards' keeps it hidden. */
            animation: fadeOutContainer 0.5s ease-in 3s forwards;
            display: block;
            overflow: hidden;
        }

        @keyframes fadeOutContainer {
            0% { opacity: 1; max-height: 100px; padding: 15px; margin-bottom: 20px; }
            99% { opacity: 0; max-height: 100px; padding: 15px; margin-bottom: 20px; }
            100% { opacity: 0; max-height: 0; padding: 0; margin: 0; display: none; }
        }
    </style>

    <div class="php-notification">
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>
<!DOCTYPE html>
<html>
<head>
    <title>SkillSwap</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Add Skill</h2>

<form action="add_skill.php" method="POST">
    <input type="hidden" name="user_id" value="1">

    <label>Skill Name:</label>
    <select name="skill_id">
        <?php
        $skills = $conn->query("SELECT * FROM skills");
        while ($row = $skills->fetch_assoc()) {
            echo "<option value='{$row['skill_id']}'>{$row['skill_name']}</option>";
        }
        ?>
    </select>

    <label>Level:</label>
    <select name="level_name">
        <?php
        $levels = $conn->query("SELECT * FROM skill_levels");
        while ($row = $levels->fetch_assoc()) {
            // value is now the text name
            echo "<option value='{$row['level_name']}'>{$row['level_name']}</option>";
        }
        ?>
    </select>

    <label>Type:</label>
    <input type="radio" name="type_name" value="Teach" required> Teach
    <input type="radio" name="type_name" value="Learn"> Learn

    <button type="submit">Add Skill</button>
</form>

<hr>

<h2>Your Skills</h2>

<table>
<tr>
    <th>Skill</th>
    <th>Level</th>
    <th>Type</th>
    <th>Action</th>
</tr>

<?php
include 'get_skills.php';

while ($row = $result->fetch_assoc()) {

    echo "<tr>";
    echo "<td>{$row['skill_name']}</td>";
    echo "<td>{$row['level_name']}</td>";
    echo "<td>{$row['type_name']}</td>";

    echo "<td>

    <form action='edit_skill.php' method='POST' style='display:inline-block;'>
        <input type='hidden' name='user_skill_id' value='{$row['user_skill_id']}'>

        <select name='level_name'>
            <option value='Beginner' ".($row['level_name']=='Beginner'?'selected':'').">Beginner</option>
            <option value='Intermediate' ".($row['level_name']=='Intermediate'?'selected':'').">Intermediate</option>
            <option value='Advanced' ".($row['level_name']=='Advanced'?'selected':'').">Advanced</option>
            <option value='Expert' ".($row['level_name']=='Expert'?'selected':'').">Expert</option>
        </select>

        <select name='type_name'>
            <option value='Teach' ".($row['type_name']=='Teach'?'selected':'').">Teach</option>
            <option value='Learn' ".($row['type_name']=='Learn'?'selected':'').">Learn</option>
        </select>

        <button type='submit'>Update</button>
    </form>

    <form action='delete_skill.php' method='POST' 
          onsubmit=\"return confirm('Are you sure you want to delete this skill?');\" 
          style='display:inline-block; margin-left:5px;'>

        <input type='hidden' name='user_skill_id' value='{$row['user_skill_id']}'>

        <button type='submit' style='background:red; color:white;'>Delete</button>
    </form>

    </td>";

    echo "</tr>";
}
?>


</table>
<br>
<a href="Matches.php">
    <button>View Matches</button>
</a>

</body>
</html>