<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Logistics 2 Dashboard</title>
</head>
<body class="dashboard-body">

    <div class="navbar">
        <span>Logistics 2: Integrated Travel Operations and Supplier Management </span>
        <a href="logout.php" class="btn" style="background:#dc3545;">Logout</a>
    </div>

    <main class="main-wrapper">
        <div class="header-section">
            <h1>Travel Service Coordination</h1>
            <p>Welcome, <strong><?php echo $_SESSION['username'] ?? 'Admin'; ?></strong>.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>Manual Suppliers</h3>
                <p>Confirm supplier contracts and manage contact details.</p>
                <a href="suppliers.php" class="btn">View Suppliers</a>
            </div>

            <div class="card">
                <h3>Booking Management</h3>
                <p>Enter customer details and assign tour packages.</p>
                <a href="bookings.php" class="btn">Manage Bookings</a>
            </div>

            <div class="card">
                <h3>Payments & Billing</h3>
                <p>Process customer payments and prepare documents.</p>
                <a href="payments.php" class="btn">Process Payments</a>
            </div>

            <div class="card">
                <h3>Tour Operation</h3>
                <p>Monitor ongoing tours and coordinate with suppliers.</p>
                <a href="monitoring.php" class="btn">Monitor Tours</a>
            </div>

            <div class="card">
                <h3>Supplier Evaluation</h3>
                <p>Record completed tours and collect feedback for Logistics 1.</p>
                <a href="feedback.php" class="btn">Give Feedback</a>
            </div>
        </div>
    </main>

</body>
</html>