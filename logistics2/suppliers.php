<?php
session_start();
if(!isset($_SESSION['auth'])) header("Location: index.php");
include 'db.php';

// Logic to toggle supplier status (Active/Inactive)
if(isset($_GET['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['toggle_status']);
    $current_status = mysqli_real_escape_string($conn, $_GET['current']);
    $new_status = ($current_status == 'Active') ? 'Inactive' : 'Active';
    
    mysqli_query($conn, "UPDATE manual_suppliers SET status='$new_status' WHERE id='$id'");
    header("Location: suppliers.php");
    exit();
}

if(isset($_POST['add_supplier'])) {
    $name = mysqli_real_escape_string($conn, $_POST['s_name']);
    $type = mysqli_real_escape_string($conn, $_POST['s_type']);
    $category = mysqli_real_escape_string($conn, $_POST['s_category']); 
    $contact = mysqli_real_escape_string($conn, $_POST['s_contact']);
    
    $query = "INSERT INTO manual_suppliers (supplier_name, supplier_type, category, contact_info, status) 
              VALUES ('$name', '$type', '$category', '$contact', 'Active')";
    
    if(mysqli_query($conn, $query)) {
        header("Location: suppliers.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Supplier Management</title>
    <style>
        .sidebar { height: 100%; width: 0; position: fixed; z-index: 1001; top: 0; left: 0; background-color: #001a33; overflow-x: hidden; transition: 0.5s; padding-top: 60px; box-shadow: 2px 0 5px rgba(0,0,0,0.3); }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 17px; color: #d1d1d1; display: block; transition: 0.3s; }
        .sidebar a:hover { color: #ffffff; background-color: #004080; }
        .sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; }
        
        /* Ensure table content stays aligned */
        th, td { text-align: left; padding: 15px; }
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
        <div class="card" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;">
            <h3 style="margin-top: 0;">Register New Supplier</h3>
            <form method="POST" style="display:flex; gap:12px; flex-wrap: wrap; justify-content: center; align-items: center;">
                <input type="text" name="s_name" placeholder="Supplier Name" required style="padding:10px; border-radius:8px; border:1px solid #ddd; width: 200px;">
                <select name="s_type" required style="padding:10px; border-radius:8px; border:1px solid #ddd; background: white; width: 180px;">
                    <option value="" disabled selected>Select Type</option>
                    <option value="Accommodation">Accommodation</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Tour Guide">Tour Guide</option>
                    <option value="Food & Catering">Food & Catering</option>
                </select>
                <select name="s_category" required style="padding:10px; border-radius:8px; border:1px solid #ddd; background: white; width: 180px;">
                    <option value="" disabled selected>Select Category</option>
                    <option value="Solo">Solo</option>
                    <option value="Joiner">Joiner</option>
                    <option value="Group / Family">Group / Family</option>
                </select>
                <input type="text" name="s_contact" placeholder="Contact Info" required style="padding:10px; border-radius:8px; border:1px solid #ddd; width: 180px;">
                <button type="submit" name="add_supplier" class="btn" style="background: #007bff; color: white; border: none; padding: 11px 25px; border-radius: 8px; cursor: pointer; font-weight: bold;">Add Supplier</button>
            </form>
        </div>

        <div class="card" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">Registered Suppliers</h3>
                <button onclick="printSupplierReport()" 
                   style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                   📄 Download Supplier Report
                </button>
            </div>

            <iframe id="supplierPrintFrame" style="display:none;"></iframe>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom: 2px solid #eee;">
                        <th>Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Contact Info</th>
                        <th>Date Registered</th> <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM manual_suppliers ORDER BY id DESC");
                while($row = mysqli_fetch_assoc($res)) {
                    $status = $row['status'];
                    $row_style = ($status == 'Inactive') ? 'opacity: 0.5;' : '';
                    $btn_color = ($status == 'Active') ? '#dc3545' : '#28a745';
                    $btn_text = ($status == 'Active') ? 'Deactivate' : 'Reactivate';
                    
                    // Format the registration date
                    $reg_date = isset($row['created_at']) ? date("M d, Y", strtotime($row['created_at'])) : "N/A";

                    echo "<tr style='border-bottom: 1px solid #f0f0f0; $row_style'>
                        <td><b>{$row['supplier_name']}</b></td>
                        <td>{$row['supplier_type']}</td>
                        <td>" . ($row['category'] ?: '<span style="color:red;">Empty</span>') . "</td>
                        <td>{$row['contact_info']}</td>
                        <td>$reg_date</td> <td style='color:".($status=='Active'?'green':'red')."; font-weight: bold;'>$status</td>
                        <td>
                            <a href='suppliers.php?toggle_status={$row['id']}&current=$status' 
                               style='background:$btn_color; color:white; padding:5px 10px; text-decoration:none; border-radius:5px; font-size:11px; font-weight:bold;'>$btn_text</a>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function openNav() { document.getElementById("mySidebar").style.width = "260px"; }
        function closeNav() { document.getElementById("mySidebar").style.width = "0"; }

        // Logic to trigger the "Pop Out" report
        function printSupplierReport() {
            var reportFrame = document.getElementById('supplierPrintFrame');
            reportFrame.src = "print_suppliers.php";
            reportFrame.onload = function() {
                reportFrame.contentWindow.focus();
                reportFrame.contentWindow.print();
            };
        }
    </script>
</body>
</html>