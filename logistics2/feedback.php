<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';

// 1. Logic to Toggle Red Flag Status
if(isset($_GET['toggle_flag'])) {
    $id = mysqli_real_escape_string($conn, $_GET['toggle_flag']);
    mysqli_query($conn, "UPDATE manual_suppliers SET red_flag = NOT red_flag WHERE id = '$id'");
    header("Location: feedback.php?msg=status_updated");
    exit();
}

// 2. Handle Supplier Review Submission
if(isset($_POST['submit_review'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $query = "INSERT INTO feedback (booking_id, rating, comment, date_submitted) 
              VALUES ('$booking_id', '$rating', '$comment', NOW())";
    
    if(mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE bookings SET booking_status = 'Reviewed' WHERE id = '$booking_id'");
        header("Location: feedback.php?success=1");
        exit();
    }
}

// 3. Fetch Data for Chart Analytics
$chart_res = mysqli_query($conn, "SELECT s.supplier_name, AVG(f.rating) as avg_r 
                                 FROM feedback f 
                                 JOIN bookings b ON f.booking_id = b.id 
                                 JOIN manual_suppliers s ON b.supplier_id = s.id 
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
    <link rel="stylesheet" href="style.css">
    <title>Supplier Performance & Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; background-color: #001a33; overflow-x: hidden; transition: 0.5s; padding-top: 60px; box-shadow: 2px 0 5px rgba(0,0,0,0.3); }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 17px; color: #d1d1d1; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #004080; }
        .sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; }

        /* Star Rating UI */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 25px; color: #ccc; cursor: pointer; transition: color 0.2s; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f39c12; }

        .btn-print { background: #28a745; color: white; padding: 10px 18px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; }
        .flag-row { background-color: #fff0f0 !important; border-left: 5px solid #d9534f; }
        .badge-red { background: #d9534f; color: white; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f0f2f5;">

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div style="padding: 20px; color: white; text-align: center;"><h4>Quick Switch</h4><hr style="border:0.5px solid #444;"></div>
        <a href="home.php">🏠 Home Dashboard</a>
        <a href="suppliers.php">🏢 Manual Suppliers</a>
        <a href="bookings.php">📅 Booking Management</a>
        <a href="payments.php">💳 Payments & Billing</a>
        <a href="monitoring.php">📊 Tour Operation</a>
        <a href="feedback.php">⭐ Supplier Feedback</a>
    </div>

    <div class="navbar" style="background: #003366; color: white; padding: 15px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button onclick="openNav()" style="background: #0056b3; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">☰ Menu</button>
            <a href="home.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">Back</a>
        </div>
        <span style="font-weight: bold;">Logistics 2: Supplier Performance Management </span>
        <button onclick="printReport()" class="btn-print">🖨️ Print Performance Report</button>
    </div>

    <main class="main-wrapper" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
        
        <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top:0;">Average Ratings Overview</h3>
            <canvas id="performanceChart" style="max-height: 250px;"></canvas>
        </div>

        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top:0; color: #d9534f;">🚩 Supplier Action Watchlist</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 12px; text-align: left;">Supplier Name</th>
                        <th style="padding: 12px; text-align: left;">Status</th>
                        <th style="padding: 12px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $suppliers = mysqli_query($conn, "SELECT * FROM manual_suppliers ORDER BY red_flag DESC, supplier_name ASC");
                while($s = mysqli_fetch_assoc($suppliers)) {
                    $is_red = $s['red_flag'] == 1;
                    echo "<tr style='border-bottom: 1px solid #f0f0f0;' " . ($is_red ? "class='flag-row'" : "") . ">
                        <td style='padding: 12px;'><b>{$s['supplier_name']}</b></td>
                        <td style='padding: 12px;'>" . ($is_red ? "<span class='badge-red'>⚠️ RED FLAG</span>" : "Normal") . "</td>
                        <td style='padding: 12px; text-align: center;'>
                            <a href='feedback.php?toggle_flag={$s['id']}' style='text-decoration:none; color: #007bff; font-size: 13px;'>
                                " . ($is_red ? "Clear Flag" : "Mark Red Flag") . "
                            </a>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top:0;">Pending Supplier Evaluations</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                <?php
                $pending = mysqli_query($conn, "SELECT b.*, s.supplier_name FROM bookings b JOIN manual_suppliers s ON b.supplier_id = s.id WHERE b.booking_status = 'Completed'");
                if(mysqli_num_rows($pending) > 0) {
                    while($row = mysqli_fetch_assoc($pending)) {
                        $id = $row['id'];
                        echo "<tr style='border-bottom: 1px solid #f0f0f0;'>
                            <td style='padding: 15px;'><b>{$row['supplier_name']}</b><br><small>{$row['customer_name']}</small></td>
                            <td style='padding: 15px;'>
                                <form method='POST' style='display:flex; flex-direction:column; gap:10px;'>
                                    <input type='hidden' name='booking_id' value='$id'>
                                    <div class='star-rating'>
                                        <input type='radio' id='star5-$id' name='rating' value='5' required/><label for='star5-$id'>★</label>
                                        <input type='radio' id='star4-$id' name='rating' value='4' /><label for='star4-$id'>★</label>
                                        <input type='radio' id='star3-$id' name='rating' value='3' /><label for='star3-$id'>★</label>
                                        <input type='radio' id='star2-$id' name='rating' value='2' /><label for='star2-$id'>★</label>
                                        <input type='radio' id='star1-$id' name='rating' value='1' /><label for='star1-$id'>★</label>
                                    </div>
                                    <div style='display:flex; gap:10px;'>
                                        <input type='text' name='comment' placeholder='Internal notes...' style='flex:1; padding:8px;' required>
                                        <button type='submit' name='submit_review' style='background:#007bff; color:white; border:none; padding:8px 12px; border-radius:5px;'>Submit</button>
                                    </div>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' style='text-align:center; padding:30px; color:#888;'>All reviewed.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0;">Performance History</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                <?php
                $history = mysqli_query($conn, "SELECT f.*, b.customer_name, s.supplier_name FROM feedback f JOIN bookings b ON f.booking_id = b.id JOIN manual_suppliers s ON b.supplier_id = s.id ORDER BY f.date_submitted DESC");
                while($row = mysqli_fetch_assoc($history)) {
                    $stars = str_repeat('<span style="color:#f39c12;">★</span>', $row['rating']) . str_repeat('<span style="color:#ccc;">☆</span>', 5 - $row['rating']);
                    echo "<tr style='border-bottom: 1px solid #f0f0f0;'>
                        <td style='padding: 15px;'><b>{$row['supplier_name']}</b></td>
                        <td style='padding: 15px;'>$stars</td>
                        <td style='padding: 15px;'><i>\"{$row['comment']}\"</i></td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>

    <iframe id="print_frame" name="print_frame" src="print_feedback.php" style="display:none;"></iframe>

    <script>
        function openNav() { document.getElementById("mySidebar").style.width = "260px"; }
        function closeNav() { document.getElementById("mySidebar").style.width = "0"; }

        function printReport() {
            var reportFrame = document.getElementById('print_frame');
            reportFrame.contentWindow.location.reload();
            reportFrame.onload = function() {
                reportFrame.contentWindow.focus();
                reportFrame.contentWindow.print();
            };
        }

        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chartData = <?php echo json_encode($ratings); ?>;
        const barColors = chartData.map(r => {
            if (r >= 4.5) return '#28a745';
            if (r >= 3.5) return '#94d82d';
            if (r >= 2.5) return '#ffc107';
            if (r >= 1.5) return '#fd7e14';
            return '#dc3545';
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($names); ?>,
                datasets: [{
                    data: chartData,
                    backgroundColor: barColors,
                    borderRadius: 5
                }]
            },
            options: { 
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } } 
            }
        });
    </script>
</body>
</html>