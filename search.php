<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Skills</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>🔍 Search Users by Skill</h2>
    <a href="index.php" class="back-link">← Back to Home</a>

    <form method="GET">
        <input 
            type="text" 
            name="keyword" 
            placeholder="Search skill or category" 
            value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>"
            required
        >
        <button type="submit">Search</button>
    </form>

    <?php
    if (isset($_GET['keyword'])) {
        $keyword = "%" . $_GET['keyword'] . "%";

        $sql = "SELECT u.user_id, u.name, u.email, s.skill_name, s.skill_category, us.type
                FROM users u
                JOIN user_skill us ON u.user_id = us.user_id
                JOIN skill s ON us.skill_id = s.skill_id
                WHERE s.skill_name LIKE ? OR s.skill_category LIKE ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<h3>Search Results</h3>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $user_id = htmlspecialchars($row['user_id']);
                $name = htmlspecialchars($row['name']);
                $email = htmlspecialchars($row['email']);
                $skill_name = htmlspecialchars($row['skill_name']);
                $skill_category = htmlspecialchars($row['skill_category']);
                $type = htmlspecialchars($row['type']);

                echo "
                <div class='card'>
                    <b>$name</b><br>
                    <span class='small-text'>$email</span><br>
                    $type <i>$skill_name</i> 
                    <span class='small-text'>($skill_category)</span><br><br>
                    <a href='matchc.php?other_id=$user_id&me=1'>Send Match</a>
                </div>";
            }
        } else {
            echo "<p>No results found.</p>";
        }
    }
    ?>

</body>
</html>
