<?php
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Retrieve form data
    $clubID = intval($_POST['ClubID']);
    $clubName = trim($_POST['ClubName']);
    $contactInfo = trim($_POST['ContactInfo']);
    $presidentID = intval($_POST['PresidentID']);
    $field = trim($_POST['Field']);

    // 2. Prepare SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO Club (ClubID, ClubName, ContactInfo, PresidentID, Field) VALUES (?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("<h2>Error preparing statement: " . htmlspecialchars($conn->error) . "</h2>");
    }

    $stmt->bind_param("issis", $clubID, $clubName, $contactInfo, $presidentID, $field);

    // 3. Execute statement and handle errors
    if ($stmt->execute()) {
        header("Location: view_clubs.php?msg=created");
        exit();
    }

    echo "<h2>Error executing club insertion: " . htmlspecialchars($stmt->error) . "</h2>";
    echo "<a href='club_form.html'>Go Back</a>";

    $stmt->close();
    $conn->close();
} else {
    header("Location: club_form.html");
    exit();
}
?>
