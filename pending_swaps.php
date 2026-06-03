<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Handle Accept / Decline Actions
|--------------------------------------------------------------------------
*/

if (isset($_GET['action']) && isset($_GET['id'])) {

    $swap_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'accept') {

        $update = $conn->prepare("
            UPDATE swaps 
            SET status='accepted'
            WHERE id=? AND receiver_id=?
        ");

        $update->bind_param("ii", $swap_id, $user_id);
        $update->execute();

    } elseif ($action == 'decline') {

        $update = $conn->prepare("
            UPDATE swaps 
            SET status='declined'
            WHERE id=? AND receiver_id=?
        ");

        $update->bind_param("ii", $swap_id, $user_id);
        $update->execute();
    }

    header("Location: pending_swaps.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Fetch Pending Swaps
|--------------------------------------------------------------------------
*/

$query = $conn->prepare("
    SELECT 
        swaps.*,
        CONCAT(users.first_name, ' ', users.last_name) AS sender_name
    FROM swaps
    JOIN users ON swaps.sender_id = users.user_id
    WHERE swaps.receiver_id = ?
    AND swaps.status = 'pending'
    ORDER BY swaps.created_at DESC
");

$query->bind_param("i", $user_id);
$query->execute();

$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Swap Requests</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background:#f4f6f9;
            font-family:'Segoe UI', sans-serif;
        }

        .page-title{
            font-weight:700;
            color:#333;
        }

        .swap-card{
            background:#fff;
            border-radius:16px;
            padding:24px;
            margin-bottom:20px;
            box-shadow:0 4px 14px rgba(0,0,0,0.08);
            transition:0.2s ease;
        }

        .swap-card:hover{
            transform:translateY(-2px);
        }

        .sender-name{
            color:#6366f1;
            font-weight:700;
        }

        .message-box{
            background:#f8f9fc;
            border-left:4px solid #6366f1;
            padding:14px;
            border-radius:10px;
            margin-top:12px;
        }

        .date-text{
            color:#777;
            font-size:14px;
        }

        .btn{
            border-radius:10px;
            padding:8px 18px;
            font-weight:600;
        }

        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .back-btn{
            text-decoration:none;
            background:#6366f1;
            color:white;
            padding:10px 18px;
            border-radius:10px;
            font-weight:600;
        }

        .back-btn:hover{
            background:#4f46e5;
        }

    </style>
</head>

<body>

<div class="container py-5">

    <div class="top-bar">
        <h2 class="page-title">Pending Swap Requests</h2>

        <a href="swaps.php" class="back-btn">
            ← Back to Chats
        </a>
    </div>

    <?php if($result->num_rows > 0): ?>

        <?php while($swap = $result->fetch_assoc()): ?>

            <div class="swap-card">

                <h5>
                    Request from:
                    <span class="sender-name">
                        <?= htmlspecialchars($swap['sender_name']) ?>
                    </span>
                </h5>

                <?php if(isset($swap['message']) && !empty($swap['message'])): ?>

                    <div class="message-box">
                        <strong>Message:</strong><br>
                        <?= htmlspecialchars($swap['message']) ?>
                    </div>

                <?php else: ?>

                    <div class="message-box">
                        No message provided.
                    </div>

                <?php endif; ?>

                <p class="date-text mt-3">
                    Request sent on:
                    <?= date("d M Y, h:i A", strtotime($swap['created_at'])) ?>
                </p>

                <div class="mt-4 d-flex gap-2">

                    <a href="?action=accept&id=<?= $swap['id'] ?>"
                       class="btn btn-success">
                        Accept
                    </a>

                    <a href="?action=decline&id=<?= $swap['id'] ?>"
                       class="btn btn-danger">
                        Decline
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="alert alert-info">
            No pending swap requests found.
        </div>

    <?php endif; ?>

</div>

</body>
</html>