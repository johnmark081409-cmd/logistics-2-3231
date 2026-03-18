<?php
include 'db.php';
// Fetch all suppliers alphabetically
$query = mysqli_query($conn, "SELECT * FROM manual_suppliers ORDER BY supplier_name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Report</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #003366; padding-bottom: 20px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; text-transform: uppercase; font-size: 12px; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-box { border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; color: #003366;">LOGISTICS 2 TRAVEL & TOURS</h1>
        <p style="font-size: 18px; margin: 5px 0;">Official Supplier Directory</p>
        <p style="color: #666;">Generated on: <?php echo date('F d, Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Contact Information</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td><b><?php echo $row['supplier_name']; ?></b></td>
                <td><?php echo $row['supplier_type']; ?></td>
                <td><?php echo $row['category'] ?: 'N/A'; ?></td>
                <td><?php echo $row['contact_info']; ?></td>
                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-box">Prepared By</div>
        <div class="sig-box">Operations Manager</div>
    </div>
</body>
</html>