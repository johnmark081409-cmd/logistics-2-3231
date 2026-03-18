<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';

// Handle payment confirmation action
if(isset($_GET['pay_id'])) {
    $pay_id = mysqli_real_escape_string($conn, $_GET['pay_id']);
    mysqli_query($conn, "UPDATE bookings SET payment_status = 'Paid' WHERE id = '$pay_id'");
    header("Location: payments.php?success=paid");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Payments & Billing</title>
    <style>
        /* Sidebar Styles */
        .sidebar { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; background-color: #001a33; overflow-x: hidden; transition: 0.5s; padding-top: 60px; box-shadow: 2px 0 5px rgba(0,0,0,0.3); }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 17px; color: #d1d1d1; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #004080; }
        .sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; }

        /* Receipt Styles */
        #printable-receipt { display: none; width: 100%; max-width: 750px; margin: 0 auto; font-family: 'Segoe UI', sans-serif; color: #333; padding: 30px; }
        .receipt-header { text-align: center; border-bottom: 3px solid #004085; padding-bottom: 15px; margin-bottom: 25px; }
        .receipt-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f9f9f9; }
        
        @media print {
            body * { visibility: hidden; }
            #printable-receipt, #printable-receipt * { visibility: visible; }
            #printable-receipt { display: block !important; position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div style="padding: 20px; color: white; text-align: center;"><h4>Quick Switch</h4><hr></div>
        <a href="home.php">🏠 Home Dashboard</a>
        <a href="suppliers.php">🏢 Manual Suppliers</a>
        <a href="bookings.php">📅 Booking Management</a>
        <a href="payments.php">💳 Payments & Billing</a>
        <a href="monitoring.php">📊 Tour Operation</a>
    </div>

    <div class="navbar no-print" style="background: #003366; color: white; padding: 10px 15px; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button onclick="openNav()" style="background: #0056b3; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">☰ Menu</button>
            <a href="home.php" style="color: white; text-decoration: none; font-size: 14px; opacity: 0.8;">Back</a>
        </div>
        <div style="text-align: center; line-height: 1.2;">
            <span style="font-weight: bold; font-size: 16px;">Logistics 2: Integrated Travel Operations and Supplier Management</span><br>

        </div>
        <div style="width: 100px;"></div> 
    </div>

    <main class="main-wrapper no-print" style="padding: 20px; background-color: #f0f2f5;">
        <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3>Pending Payments</h3>
            <table style="width:100%; border-collapse: collapse;">
                <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding:12px;">Customer</th><th>Supplier</th><th>Amount</th><th>Method</th><th>Action</th>
                </tr>
                <?php
                $pending = mysqli_query($conn, "SELECT b.*, s.supplier_name FROM bookings b JOIN manual_suppliers s ON b.supplier_id = s.id WHERE b.payment_status = 'Unpaid' OR b.payment_status IS NULL ORDER BY b.id DESC");
                while($row = mysqli_fetch_assoc($pending)) {
                    $amt = isset($row['amount']) ? number_format($row['amount'], 2) : "0.00";
                    echo "<tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding:12px;'><b>{$row['customer_name']}</b></td>
                        <td>{$row['supplier_name']}</td>
                        <td>₱$amt</td>
                        <td>" . ($row['payment_method'] ?? 'N/A') . "</td>
                        <td><a href='payments.php?pay_id={$row['id']}' class='btn' style='background:#007bff; color:white; text-decoration:none; padding:5px 10px; border-radius:5px; font-size:12px;'>Mark as Paid</a></td>
                    </tr>";
                }
                ?>
            </table>
        </div>

        <div class="card" style="margin-top: 25px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Payment History</h3>
                <button onclick="printAllRecords()" 
                   style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer;">
                   📄 Print All Records
                </button>
            </div>

            <iframe id="printHistoryFrame" src="print_all_payments.php" style="display:none;"></iframe>

            <table style="width:100%; border-collapse: collapse;">
                <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding:12px;">Customer</th><th>Supplier</th><th>Amount</th><th>Method</th><th>Status</th><th>Receipt</th>
                </tr>
                <?php
                $history = mysqli_query($conn, "SELECT b.*, s.supplier_name FROM bookings b JOIN manual_suppliers s ON b.supplier_id = s.id WHERE b.payment_status = 'Paid' ORDER BY b.id DESC");
                while($row = mysqli_fetch_assoc($history)) {
                    $jsData = json_encode($row);
                    $amt = isset($row['amount']) ? number_format($row['amount'], 2) : "0.00";
                    echo "<tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding:12px;'><b>{$row['customer_name']}</b></td>
                        <td>{$row['supplier_name']}</td>
                        <td>₱$amt</td>
                        <td>{$row['payment_method']}</td>
                        <td style='color:green; font-weight:bold;'>✓ Paid</td>
                        <td><button onclick='downloadPDF($jsData)' style='background:#28a745; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;'>📄 Download PDF</button></td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </main>

    <div id="printable-receipt">
        <div class="receipt-header">
            <h1 style="margin:0;">LOGISTICS 2 TRAVEL & TOURS</h1>
            <p>Official Transaction Receipt</p>
        </div>
        <div style="text-align: right; margin-bottom: 20px;">
            <strong>Receipt No:</strong> <span id="r-id"></span><br>
            <strong>Date Generated:</strong> <?php echo date("F j, Y"); ?>
        </div>
        <div class="receipt-row"><strong>Customer Name:</strong> <span id="r-cust"></span></div>
        <div class="receipt-row"><strong>Supplier / Service:</strong> <span id="r-supp"></span></div>
        <div class="receipt-row"><strong>Service Date:</strong> <span id="r-date"></span></div>
        <div class="receipt-row"><strong>Payment Method:</strong> <span id="r-method"></span></div>
        <div class="receipt-row" style="background: #f1f8ff; border: 2px solid #004085; margin-top: 20px; padding: 15px;">
            <strong style="font-size: 18px;">TOTAL PAID:</strong> 
            <span id="r-amount" style="font-size: 18px; font-weight: bold; color: #004085;"></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 60px;">
            <div style="text-align: center; width: 200px; border-top: 1px solid #333;"><p>Customer Signature</p></div>
            <div style="text-align: center; width: 200px; border-top: 1px solid #333;"><p>Authorized Representative</p></div>
        </div>
    </div>

    <script>
    function openNav() { document.getElementById("mySidebar").style.width = "260px"; }
    function closeNav() { document.getElementById("mySidebar").style.width = "0"; }

    // Logic for individual PDF download pop-out
    function downloadPDF(data) {
        document.getElementById('r-id').innerText = "TXN-" + String(data.id).padStart(5, '0');
        document.getElementById('r-cust').innerText = data.customer_name;
        document.getElementById('r-supp').innerText = data.supplier_name;
        document.getElementById('r-date').innerText = data.travel_date || 'N/A';
        document.getElementById('r-method').innerText = data.payment_method || 'N/A';
        
        let total = parseFloat(data.amount);
        if (isNaN(total)) total = 0;
        
        document.getElementById('r-amount').innerText = "₱" + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        window.print();
    }

    // New logic for the full history pop-out
    function printAllRecords() {
        var reportFrame = document.getElementById('printHistoryFrame');
        
        // Refresh the iframe to ensure it has the latest data
        reportFrame.src = reportFrame.src;
        
        // Wait for it to load, then trigger print dialog
        reportFrame.onload = function() {
            reportFrame.contentWindow.focus();
            reportFrame.contentWindow.print();
        };
    }
    </script>
</body>
</html>