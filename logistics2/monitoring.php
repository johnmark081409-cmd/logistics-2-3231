<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';

// Logic to complete a tour
if(isset($_GET['complete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['complete']);
    
    // Update status to Completed
    mysqli_query($conn, "UPDATE bookings SET booking_status='Completed' WHERE id='$id'");
    
    header("Location: monitoring.php?msg=success");
    exit(); 
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Tour Operation & Monitoring</title>
    <style>
        /* Sidebar styles for internal navigation */
        .sidebar { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; background-color: #001a33; overflow-x: hidden; transition: 0.5s; padding-top: 60px; box-shadow: 2px 0 5px rgba(0,0,0,0.3); }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 17px; color: #d1d1d1; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #004080; }
        .sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; }
        
        /* Print Button Style */
        .btn-print { background: #28a745; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-print:hover { background: #218838; transform: translateY(-1px); }
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
    </div>

    <div class="navbar" style="background: #003366; color: white; padding: 15px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button onclick="openNav()" style="background: #0056b3; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">☰ Menu</button>
            <a href="home.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">Back</a>
        </div>
        <span style="font-weight: bold;">Logistics 2: Integrated Travel Operations and Supplier Management</span>
        <div style="width: 50px;"></div> 
    </div>

    <main class="main-wrapper" style="padding: 20px;">
        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top:0;">Ongoing Tours</h3>
            <p style="color: #666; font-size: 14px;">Track active travel services and coordinate with suppliers.</p>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; text-align: left;">Customer</th>
                        <th style="padding: 15px; text-align: left;">Supplier / Service</th>
                        <th style="padding: 15px; text-align: left;">Status</th>
                        <th style="padding: 15px; text-align: left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT b.*, s.supplier_name 
                                           FROM bookings b 
                                           JOIN manual_suppliers s ON b.supplier_id = s.id 
                                           WHERE b.payment_status = 'Paid' 
                                           AND b.booking_status NOT IN ('Completed', 'Reviewed')");
                
                if(mysqli_num_rows($res) == 0) {
                    echo "<tr><td colspan='4' style='text-align:center; padding: 30px; color: #888;'>No active tours at the moment.</td></tr>";
                }

                while($row = mysqli_fetch_assoc($res)) {
                    echo "<tr style='border-bottom: 1px solid #f0f0f0;'>
                        <td style='padding: 15px;'><b>{$row['customer_name']}</b></td>
                        <td style='padding: 15px;'>{$row['supplier_name']}</td>
                        <td style='padding: 15px;'><span style='color: #28a745; font-weight: bold;'>In Progress</span></td>
                        <td style='padding: 15px;'>
                            <a href='monitoring.php?complete={$row['id']}' class='btn' style='background:#17a2b8; color: white; text-decoration: none; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold;'>Mark Completed</a>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Operation History</h3>
                <button onclick="printReport()" class="btn-print">🖨️ Print History Report</button>
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; text-align: left;">Customer</th>
                        <th style="padding: 15px; text-align: left;">Supplier</th>
                        <th style="padding: 15px; text-align: left;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $history = mysqli_query($conn, "SELECT b.*, s.supplier_name 
                                               FROM bookings b 
                                               JOIN manual_suppliers s ON b.supplier_id = s.id 
                                               WHERE b.booking_status IN ('Completed', 'Reviewed')
                                               ORDER BY b.id DESC");
                
                if(mysqli_num_rows($history) == 0) {
                    echo "<tr><td colspan='3' style='text-align:center; padding: 30px; color: #888;'>No past operations found.</td></tr>";
                }

                while($h = mysqli_fetch_assoc($history)) {
                    $status_text = ($h['booking_status'] == 'Reviewed') ? 'Finished & Evaluated' : 'Finished';
                    $status_color = ($h['booking_status'] == 'Reviewed') ? '#00c016' : '#6c757d';

                    echo "<tr style='border-bottom: 1px solid #f0f0f0;'>
                        <td style='padding: 15px;'>{$h['customer_name']}</td>
                        <td style='padding: 15px;'>{$h['supplier_name']}</td>
                        <td style='padding: 15px;'><span style='color: $status_color; font-weight: bold;'>✓ $status_text</span></td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>

    <iframe id="print_frame" name="print_frame" src="print_all_payments.php" style="display:none;"></iframe>

    <script>
        function openNav() { document.getElementById("mySidebar").style.width = "260px"; }
        function closeNav() { document.getElementById("mySidebar").style.width = "0"; }

        // Pop-up Print logic using hidden iframe
        function printReport() {
            var reportFrame = document.getElementById('print_frame');
            reportFrame.contentWindow.location.reload(); // Refresh data to get latest history
            reportFrame.onload = function() {
                reportFrame.contentWindow.focus();
                reportFrame.contentWindow.print();
            };
        }
    </script>
</body>
</html>