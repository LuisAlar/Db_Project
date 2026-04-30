<?php
require 'config/db_connect.php';

$email = isset($_GET['Email']) ? $_GET['Email'] : '';
$studentId = isset($_GET['StudentID']) ? $_GET['StudentID'] : '';
$generatedSql = null;
$result = null;
$errorMsg = null;

if (isset($_GET['login'])) {
    $generatedSql = "SELECT StudentID, FirstName, LastName, Email, Major, GradYear FROM Student WHERE Email = '$email' AND StudentID = '$studentId'";

    $result = $conn->query($generatedSql);
    if ($result === false) {
        $errorMsg = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Demo</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .note {
            background: #fff3cd;
            border: 1px solid #ffe08a;
            padding: 12px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <header>
            <h1>Student Portal Login</h1>
            <p>Demonstrates SQL injection</p>
        </header>

        <form method="GET" action="sql_injection_demo.php">
            <div class="form-group">
                <label for="Email">Student Email</label>
                <input type="text" id="Email" name="Email" value="<?php echo htmlspecialchars($email); ?>" placeholder="e.g. alice.j@university.edu or injection payload">
            </div>

            <div class="form-group">
                <label for="StudentID">Student ID</label>
                <input type="password" id="StudentID" name="StudentID" value="<?php echo htmlspecialchars($studentId); ?>" placeholder="e.g. 1001">
            </div>

            <button type="submit" name="login" value="1">Login</button>
            <a href="sql_injection_update_demo.php" class="back-link" style="margin-left: 15px; display: inline-block;">Change Student Id</a>
            <a href="sql_injection_protected.php" class="back-link" style="margin-left: 15px; display: inline-block;">Protected Login</a>
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

        <?php if ($result instanceof mysqli_result): ?>
            <h2 style="margin-top: 24px; color: #4caf50;">Results</h2>

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
                <p style="margin-top: 20px;">No rows returned.</p>
            <?php endif; ?>
        <?php endif; ?>

        <h2 style="margin-top: 28px; color: #4caf50;">How to Test Injections:</h2>
        <ul class="hint-list">
            <li><strong>Normal behavior:</strong> Type <code>alice.j@university.edu</code> in Email and <code>1001</code> in ID. It only returns Alice.</li>
            <li><strong>Authentication Bypass (Always True):</strong> Type <code>' OR 1=1 -- </code> in the Email field and leave the ID blank. The <code>-- </code> comments out the ID check entirely, and <code>1=1</code> makes the condition true, logging you in and returning EVERY student.</li>
            <li><strong>Targeted Bypass:</strong> Type <code>bob.s@university.edu' -- </code> in the Email field to log in as Bob without knowing his Student ID.</li>
        </ul>

        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>
