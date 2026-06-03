<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_id'])) {
    die("❌ Event ID missing.");
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];


// check if user is participant
$check = $conn->query("
    SELECT * FROM event_participant
    WHERE event_id = $event_id
    AND user_id = $user_id
");

if ($check->num_rows == 0) {
    die("❌ Only participants can update this event.");
}


// get current event data
$result = $conn->query("
    SELECT * FROM events
    WHERE event_id = $event_id
");

$event = $result->fetch_assoc();

if (!$event) {
    die("❌ Event not found.");
}


// update event
if (isset($_POST['update'])) {

    $location = $_POST['location'];
    $date_time = $_POST['date_time'];

    $conn->query("
        UPDATE events
        SET location='$location',
            date_time='$date_time'
        WHERE event_id=$event_id
    ");

    echo "✅ Event updated successfully!";
    echo "<br><a href='dashboard.php'>Back to Dashboard</a>";

    exit();
}
?>

<h2>Update Event</h2>

<form method="POST">

    <label>Location:</label><br>
    <input type="text"
           name="location"
           value="<?php echo $event['location']; ?>"
           required><br><br>

    <label>Date & Time:</label><br>
    <input type="datetime-local"
           name="date_time"
           value="<?php echo date('Y-m-d\TH:i', strtotime($event['date_time'])); ?>"
           required><br><br>

    <button name="update">Update Event</button>

</form>