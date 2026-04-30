<?php
require 'config/db_connect.php';

$email = isset($_POST['Email']) ? $_POST['Email'] : '';
$oldId = isset($_POST['OldID']) ? $_POST['OldID'] : '';
$newId = isset($_POST['NewID']) ? $_POST['NewID'] : '';
$generatedSql = null;
$errorMsg = null;
$successMsg = null;

if (isset($_POST['update'])) {
    // Intentionally vulnerable to stacked queries by using multi_query
    $generatedSql = "UPDATE Student SET StudentID = '$newId' WHERE Email = '$email' AND StudentID = '$oldId'";

    // We use multi_query instead of query to allow multiple SQL statements separated by a semicolon (;)
    if ($conn->multi_query($generatedSql)) {
        // We must consume all results from multi_query to avoid out-of-sync errors later
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        $successMsg = "Query executed successfully! (Check the database to see the effects)";
    } else {
        $errorMsg = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SQL Update Injection Demo</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .code-box { background: #f7f7f7; border: 1px solid #ddd; padding: 12px; font-family: Consolas, monospace; white-space: pre-wrap; word-break: break-word; }
        .hint-list { margin: 16px 0 0; padding-left: 18px; }
        .error-box { background: #f9d6d5; color: #8a1f11; padding: 12px; margin-bottom: 20px; }
        .success-box { background: #d4edda; color: #155724; padding: 12px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <header>
            <h1>Change Student ID (Update Injection)</h1>
            <p>Demonstrates UPDATE injection and Multiple SQL Statements (Stacked Queries).</p>
        </header>

        <form method="POST" action="sql_injection_update_demo.php">
            <div class="form-group">
                <label for="Email">Student Email</label>
                <input type="text" id="Email" name="Email" value="<?php echo htmlspecialchars($email); ?>" placeholder="e.g. alice.j@university.edu">
            </div>

            <div class="form-group">
                <label for="OldID">Old Student ID</label>
                <input type="password" id="OldID" name="OldID" value="<?php echo htmlspecialchars($oldId); ?>" placeholder="e.g. 1001">
            </div>
            
            <div class="form-group">
                <label for="NewID">New Student ID</label>
                <input type="text" id="NewID" name="NewID" value="<?php echo htmlspecialchars($newId); ?>" placeholder="e.g. 9999 or injection payload">
            </div>

            <button type="submit" name="update" value="1">Change ID</button>
        </form>

        <?php if ($generatedSql): ?>
            <h2 style="margin-top: 24px; color: #4caf50;">Generated SQL</h2>
            <div class="code-box"><?php echo htmlspecialchars($generatedSql); ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="error-box" style="margin-top: 20px;">
                Query error: <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($successMsg): ?>
            <div class="success-box" style="margin-top: 20px;">
                <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>

        <h2 style="margin-top: 28px; color: #4caf50;">How to Test Update Injections:</h2>
        <ul class="hint-list">
            <li><strong>Normal behavior:</strong> Enter a correct email, old ID, and a new number. It updates that specific student's ID.</li>
            <li><strong>Mass Update:</strong> Type <code>9999' -- </code> in the New ID field, and leave Old ID blank. This comments out the WHERE clause entirely, changing EVERY student's ID to 9999.</li>
            <li><strong>Multiple Statements (Stacked Queries):</strong> In the New ID field, type <code>9999' WHERE StudentID='1001'; UPDATE Student SET Major='Hacked' WHERE StudentID='1002'; -- </code>. This closes the first update, adds a semicolon, and executes a second malicious query to update a different student entirely!</li>
        </ul>

        <a href="sql_injection_demo.php" class="back-link" style="margin-top: 20px;">← Back to Login Injection Demo</a>
    </div>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>
