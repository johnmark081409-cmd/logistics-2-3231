<?php
session_start();
if (!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// 1. HANDLE NEW BOOKING SUBMISSION
if (isset($_POST['confirm_booking'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    
    $dur_val = mysqli_real_escape_string($conn, $_POST['duration_value']);
    $dur_unit = mysqli_real_escape_string($conn, $_POST['duration_unit']);
    
    // Formatting the duration string
    if ($dur_unit == "Days & Nights") {
        $night_val = mysqli_real_escape_string($conn, $_POST['night_value']);
        $duration = $dur_val . "D " . $night_val . "N Stay"; 
    } else {
        $duration = $dur_val . " " . $dur_unit;
    }

    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $travel_date = mysqli_real_escape_string($conn, $_POST['travel_date']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']); 
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']); 

    $sql = "INSERT INTO bookings (customer_name, destination, duration, supplier_id, travel_date, amount, payment_method, booking_status, payment_status) 
            VALUES ('$customer_name', '$destination', '$duration', '$supplier_id', '$travel_date', '$amount', '$payment_method', 'Reviewed', 'Unpaid')";

    if (mysqli_query($conn, $sql)) {
        header("Location: bookings.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Booking Management</title>
    <style>
        .sidebar { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; background-color: #001a33; overflow-x: hidden; transition: 0.5s; padding-top: 60px; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 17px; color: #d1d1d1; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #004080; }
        .sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; }
        
        .btn-print { background: #28a745; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-print:hover { background: #218838; }
    </style>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f0f2f5;">
    
    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="home.php">🏠 Home Dashboard</a>
        <a href="suppliers.php">🏢 Manual Suppliers</a>
        <a href="bookings.php">📅 Booking Management</a>
        <a href="payments.php">💳 Payments & Billing</a>
        <a href="monitoring.php">📊 Tour Operation</a>
    </div>

    <div class="navbar" style="background: #003366; color: white; padding: 15px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button onclick="openNav()" style="background: #0056b3; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">☰ Menu</button>
            <a href="home.php" style="color: white; text-decoration: none; font-size: 14px;">Back</a>
        </div>
        <span style="font-weight: bold;">Logistics 2: Integrated Travel Operations</span>
    </div>

    <main style="padding: 20px;">
        <div class="card" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 1250px; margin: 0 auto;">
            <h3 style="margin-bottom: 20px;">New Booking</h3>
            <form method="POST" style="display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <input type="text" name="customer_name" placeholder="Customer" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 130px;">
                <input type="text" name="destination" placeholder="Destination" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 120px;">
                
                <div style="display: flex; gap: 2px; align-items: center;">
                    <input type="number" name="duration_value" placeholder="D" min="1" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 45px;">
                    <input type="number" name="night_value" id="night_box" placeholder="N" min="0" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 45px; display: none;">
                    <select name="duration_unit" id="unit_select" onchange="toggleNightBox()" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; background: #f8f9fa; font-size: 12px; width: 110px;">
                        <option value="Days">Days</option>
                        <option value="Days & Nights">Days & Nights</option>
                        <option value="Hours">Hours</option>
                    </select>
                </div>

                <select name="supplier_id" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 130px;">
                    <option value="">Select Supplier</option>
                    <?php
                    $suppliers = mysqli_query($conn, "SELECT * FROM manual_suppliers WHERE status = 'Active'");
                    while($s = mysqli_fetch_assoc($suppliers)) {
                        echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>";
                    }
                    ?>
                </select>
                <input type="date" name="travel_date" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                <input type="number" name="amount" placeholder="Amount (₱)" step="0.01" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 90px;">
                <select name="payment_method" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    <option value="Cash">Cash</option>
                    <option value="Gcash">Gcash</option>
                </select>
                <button type="submit" name="confirm_booking" style="background: #007bff; color: white; border: none; padding: 11px 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">Confirm Booking</button>
            </form>
        </div>

        <div class="card" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1250px; margin: 25px auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Recent Bookings</h3>
                <button onclick="printReport()" class="btn-print">🖨️ Print All Records</button>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; background-color: #fafafa;">
                        <th style="padding: 15px;">Customer</th>
                        <th style="padding: 15px;">Destination</th>
                        <th style="padding: 15px;">Duration</th> 
                        <th style="padding: 15px;">Supplier</th>
                        <th style="padding: 15px;">Amount</th>
                        <th style="padding: 15px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT b.*, s.supplier_name FROM bookings b LEFT JOIN manual_suppliers s ON b.supplier_id = s.id ORDER BY b.id DESC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr style='border-bottom: 1px solid #f0f0f0;'>
                            <td style='padding: 15px;'><b>{$row['customer_name']}</b></td>
                            <td style='padding: 15px;'>" . ($row['destination'] ?? 'N/A') . "</td>
                            <td style='padding: 15px;'>" . ($row['duration'] ?? 'N/A') . "</td>
                            <td style='padding: 15px;'>" . ($row['supplier_name'] ?? 'N/A') . "</td>
                            <td style='padding: 15px;'>₱" . number_format((float)($row['amount'] ?? 0), 2) . "</td>
                            <td style='padding: 15px;'><span style='color: #00c016; font-weight: bold;'>{$row['booking_status']}</span></td>
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
        
        function toggleNightBox() {
            var unit = document.getElementById('unit_select').value;
            var nightBox = document.getElementById('night_box');
            nightBox.style.display = (unit === "Days & Nights") ? "block" : "none";
            nightBox.required = (unit === "Days & Nights");
        }

        // Script to trigger pop-up print
        function printReport() {
            var reportFrame = document.getElementById('print_frame');
            reportFrame.contentWindow.location.reload(); // Refresh data
            reportFrame.onload = function() {
                reportFrame.contentWindow.focus();
                reportFrame.contentWindow.print();
            };
        }
    </script>
</body>
</html>