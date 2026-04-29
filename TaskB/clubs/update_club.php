<?php
require '../config/db_connect.php';

// 1. Check if ClubID is provided in the URL (GET request)
$clubID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$club = null;
$errorMsg = null;

// 2. Fetch existing data to populate the form
if ($clubID > 0) {
    $stmt = $conn->prepare("SELECT * FROM Club WHERE ClubID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $clubID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $club = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// 3. Handle the update submission (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3a. Retrieve updated form data
    $idToUpdate  = intval($_POST['ClubID']); // ensuring safety
    $clubName    = trim($_POST['ClubName']);
    $contactInfo = trim($_POST['ContactInfo']);
    $presidentID = intval($_POST['PresidentID']);
    $field       = trim($_POST['Field']);

    // 3b. Prepare SQL UPDATE statement using a prepared statement
    $updateStmt = $conn->prepare("UPDATE Club SET ClubName = ?, ContactInfo = ?, PresidentID = ?, Field = ? WHERE ClubID = ?");
    
    if ($updateStmt) {
        // Bind parameters (s = string, i = integer)
        $updateStmt->bind_param("ssisi", $clubName, $contactInfo, $presidentID, $field, $idToUpdate);
        
        // 3c. Execute statement and handle results
        if ($updateStmt->execute()) {
            // Redirection after successful update
            header("Location: view_clubs.php?msg=updated");
            exit();
        } else {
            $errorMsg = "Error updating database: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $errorMsg = "Error preparing update statement: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Club</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Update Club</h1>
        </header>
        <?php if ($errorMsg) echo "<p style='color:red;'>$errorMsg</p>"; ?>
        <?php if ($club): ?>
            <form action="update_club.php?id=<?php echo $clubID; ?>" method="POST">
                <div class="form-group">
                    <label>Club ID (Not Editable)</label>
                    <input type="number" name="ClubID" value="<?php echo htmlspecialchars($club['ClubID']); ?>" readonly style="background-color: #e9ecef;">
                </div>
                <div class="form-group">
                    <label>Club Name</label>
                    <input type="text" name="ClubName" value="<?php echo htmlspecialchars($club['ClubName']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="ContactInfo" value="<?php echo htmlspecialchars($club['ContactInfo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>President ID</label>
                    <input type="number" name="PresidentID" value="<?php echo htmlspecialchars($club['PresidentID']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Associated Field</label>
                    <input type="text" name="Field" value="<?php echo htmlspecialchars($club['Field']); ?>" required>
                </div>
                <button type="submit">Update Club</button>
            </form>
        <?php else: ?>
            <p>Club not found.</p>
        <?php endif; ?>
        <a href="view_clubs.php" class="back-link">Cancel</a>
    </div>
</body>
</html>
