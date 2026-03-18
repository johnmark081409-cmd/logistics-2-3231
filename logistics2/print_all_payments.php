<?php
include 'db.php';

// UPDATED QUERY: Included destination and duration
$query = "SELECT b.*, s.supplier_name 
          FROM bookings b 
          LEFT JOIN manual_suppliers s ON b.supplier_id = s.id 
          ORDER BY b.id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Payment & Booking Report</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .header { text-align: center; border-bottom: 3px solid #003366; padding-bottom: 20px; margin-bottom: 30px; }
        .footer { margin-top: 60px; display: flex; justify-content: space-between; }
        .sig-box { border-top: 1px solid #333; width: 220px; text-align: center; padding-top: 8px; font-weight: bold; }
        .status-paid { color: green; font-weight: bold; }
        .status-unpaid { color: red; font-weight: bold; }
        @media print { 
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; color: #003366;">LOGISTICS 2 TRAVEL & TOURS</h1>
        <p style="font-size: 18px; margin: 5px 0;">Complete Booking & Payment Report</p>
        <p style="color: #666;">Date Generated: <?php echo date('F d, Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Destination</th> <th>Duration</th>    <th>Supplier / Service</th>
                <th>Travel Date</th>
                <th>Status</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_sum = 0;
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $val = (float)($row['amount'] ?? 0);
                    $total_sum += $val;
                    $amt = number_format($val, 2);
                    
                    $status = ($row['payment_status'] == 'Paid') ? '<span class="status-paid">Paid</span>' : '<span class="status-unpaid">Pending</span>';
                    
                    echo "<tr>
                        <td><b>{$row['customer_name']}</b></td>
                        <td>" . ($row['destination'] ?: 'N/A') . "</td> <td>" . ($row['duration'] ?: 'N/A') . "</td>    <td>" . ($row['supplier_name'] ?: 'N/A') . "</td>
                        <td>" . ($row['travel_date'] ?: 'N/A') . "</td>
                        <td>$status</td>
                        <td style='font-weight: bold;'>₱$amt</td>
                    </tr>";
                }
                // Updated Summary Row colspan to 6 to account for new columns
                echo "<tr style='background: #f1f8ff;'>
                        <td colspan='6' style='text-align: right; font-weight: bold;'>TOTAL VALUE:</td>
                        <td style='font-weight: bold; color: #003366; font-size: 16px;'>₱" . number_format($total_sum, 2) . "</td>
                      </tr>";
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-box">Prepared By</div>
        <div class="sig-box">Authorized Manager</div>
    </div>

    <div class="no-print" style="margin-top: 40px; text-align: center;">
        <hr>
        <p>This is a preview mode. Use the main Print button to trigger the dialog.</p>
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ Print Now</button>
    </div>
</body>
</html>