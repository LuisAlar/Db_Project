<?php
require 'config/db_connect.php';

$email = isset($_GET['Email']) ? $_GET['Email'] : '';
$studentId = isset($_GET['StudentID']) ? $_GET['StudentID'] : '';
$generatedSql = null;
$result = null;
$errorMsg = null;

if (isset($_GET['login'])) {
    $generatedSql = "SELECT StudentID, FirstName, LastName, Email, Major, GradYear FROM Student WHERE Email = ? AND StudentID = ?";

    $stmt = $conn->prepare($generatedSql);
    
    if ($stmt === false) {
        $errorMsg = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("ss", $email, $studentId);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
        } else {
            $errorMsg = "Execute failed: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Prevention - Prepared Statements</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .note {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            margin-bottom: 20px;
            color: #155724;
        }
        .code-box {
            background: #f7f7f7;
            border: 1px solid #ddd;
            padding: 12px;
            font-family: Consolas, monospace;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .hint-list {
            margin: 16px 0 0;
            padding-left: 18px;
        }
        .error-box {
            background: #f9d6d5;
            color: #8a1f11;
            padding: 12px;
            margin-bottom: 20px;
        }
        .protection-info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <header>
            <h1>Student Portal Login (Protected)</h1>
            <p>Demonstrates SQL injection prevention using Prepared Statements.</p>
        </header>

        <div class="protection-info">
            <strong>Protected Version:</strong> This page uses prepared statements to prevent SQL injection attacks. 
            User input is treated as data, not executable code.
        </div>

        <form method="GET" action="sql_injection_protected.php">
            <div class="form-group">
                <label for="Email">Student Email</label>
                <input type="text" id="Email" name="Email" value="<?php echo htmlspecialchars($email); ?>" placeholder="e.g. alice.j@university.edu or injection payload">
            </div>

            <div class="form-group">
                <label for="StudentID">Student ID</label>
                <input type="password" id="StudentID" name="StudentID" value="<?php echo htmlspecialchars($studentId); ?>" placeholder="e.g. 1001">
            </div>

            <button type="submit" name="login" value="1">Login</button>
        </form>

        <?php if ($generatedSql): ?>
            <h2 style="margin-top: 24px; color: #2196F3;">Prepared Statement SQL</h2>
            <div class="code-box"><?php echo htmlspecialchars($generatedSql); ?></div>
            <div class="note" style="margin-top: 12px;">
                <strong>How it works:</strong> The ? placeholders are replaced with sanitized values at execution time. 
                User input is never directly concatenated into the SQL query.
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="error-box" style="margin-top: 20px;">
                Query error: <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <?php if ($result instanceof mysqli_result): ?>
            <h2 style="margin-top: 24px; color: #2196F3;">Results</h2>

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Major</th>
                            <th>Grad Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['StudentID']); ?></td>
                                <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                                <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Major']); ?></td>
                                <td><?php echo htmlspecialchars($row['GradYear']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin-top: 20px;">No rows returned. Injection attempts are blocked!</p>
            <?php endif; ?>
        <?php endif; ?>

        <h2 style="margin-top: 28px; color: #2196F3;">Try These Injection Payloads</h2>
        <div class="note">
            These payloads would work on the vulnerable page but are blocked here:
        </div>
        <ul class="hint-list">
            <li><code>' OR 1=1 -- </code> in the Email field</li>
            <li><code>bob.s@university.edu' -- </code> in the Email field</li>
        </ul>

        <div style="margin-top: 20px;">
            <a href="sql_injection_demo.php" style="margin-right: 16px;">View Vulnerable Login</a>
            <a href="index.html" class="back-link">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
<?php
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>
