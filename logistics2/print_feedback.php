<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';

// 1. Fetch Chart Data
$chart_res = mysqli_query($conn, "SELECT s.supplier_name, AVG(f.rating) as avg_r 
                                 FROM manual_suppliers s
                                 JOIN bookings b ON s.id = b.supplier_id
                                 JOIN feedback f ON b.id = f.booking_id
                                 GROUP BY s.id");
$names = []; $ratings = [];
while($c = mysqli_fetch_assoc($chart_res)) {
    $names[] = $c['supplier_name'];
    $ratings[] = (float)round($c['avg_r'], 1);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Performance Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 13px; }
        th { background-color: #f8f9fa; }
        .red-flag { color: white; background-color: #d9534f; padding: 2px 5px; border-radius: 3px; font-weight: bold; }
        @media print { .no-print { display: none; } #printChart { width: 100% !important; height: auto !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Confirm Print</button>
    </div>

    <div class="header">
        <h1 style="color:#003366; margin:0;">LOGISTICS 2 TRAVEL & TOURS</h1>
        <h3>Official Supplier Performance Report</h3>
        <p>Date: <?php echo date('F d, Y'); ?></p>
    </div>

    <div style="width: 80%; margin: 0 auto;">
        <canvas id="printChart" style="max-height: 250px;"></canvas>
    </div>

    <h4>💬 Detailed Comment Feedback</h4>
    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Rating</th>
                <th>Internal Comments</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $history = mysqli_query($conn, "SELECT f.*, s.supplier_name 
                                           FROM feedback f 
                                           JOIN bookings b ON f.booking_id = b.id 
                                           JOIN manual_suppliers s ON b.supplier_id = s.id 
                                           ORDER BY f.date_submitted DESC");
            if(mysqli_num_rows($history) > 0) {
                while($row = mysqli_fetch_assoc($history)) {
                    echo "<tr>
                            <td><b>{$row['supplier_name']}</b></td>
                            <td>{$row['rating']} / 5</td>
                            <td><i>\"{$row['comment']}\"</i></td>
                            <td>" . date('M d, Y', strtotime($row['date_submitted'])) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No feedback records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h4 style="color:#d9534f;">🚩 Action Watchlist (Red Flags)</h4>
    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Status</th>
                <th>Priority</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $flags = mysqli_query($conn, "SELECT * FROM manual_suppliers WHERE red_flag = 1");
            if(mysqli_num_rows($flags) > 0) {
                while($f = mysqli_fetch_assoc($flags)) {
                    echo "<tr>
                            <td>{$f['supplier_name']}</td>
                            <td><span class='red-flag'>RED FLAG</span></td>
                            <td>HIGH</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center;'>No suppliers currently flagged.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        const ctx = document.getElementById('printChart').getContext('2d');
        const chartData = <?php echo json_encode($ratings); ?>;
        
        // Match the colors from your dashboard
        const barColors = chartData.map(r => {
            if (r >= 4.5) return '#28a745'; 
            if (r >= 3.0) return '#ffc107'; 
            return '#dc3545';               
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($names); ?>,
                datasets: [{
                    label: 'Avg Rating',
                    data: chartData,
                    backgroundColor: barColors
                }]
            },
            options: { 
                animation: false, // CRITICAL: Disables animation so it appears in print preview
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } } 
            }
        });
    </script>
</body>
</html>