<?php
session_start();

// Check if the user is an admin
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ganesh";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data from CSV Table</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-image: url('indexback.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 10px 20px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 img {
            max-height: 50px;
        }

        header nav ul {
            list-style-type: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        header nav ul li {
            margin-left: 10px;
        }

        header nav button {
            background-color: #ff9800; /* Orange for operators */
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        header nav button:hover {
            background-color: #e68900; /* Darker orange on hover */
        }

        .admin-mode header nav button {
            background-color: #28a745; /* Green for admin */
        }

        .admin-mode header nav button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        main {
            width: 100%;
            margin: 20px 0;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        main section {
            margin-bottom: 20px;
        }

        main section h2 {
            color: #333;
        }

        main section p {
            color: #666;
        }

        .tables-container {
            display: flex;
            justify-content: flex-start;
            width: 100%;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            background-color: #ffffff;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background-color: #f2f2f2;
        }

        .quantities-box {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 16px;
            margin-top: 6px;
        }
    </style>
</head>
<body class="<?php echo $isAdmin ? 'admin-mode' : ''; ?>">
    <header class="header">
        <h1><img src="R1.jpg" alt="Logo"></h1>
        <nav>
            <ul class="header-buttons">
                <li><button onclick="editTable()">Edit</button></li>
                <li><button id="saveButton" onclick="saveAllRows()" style="display: none;">Save</button></li>
                <li><button onclick="location.href='login.php';">Admin</button></li>
                <?php if ($isAdmin): ?>
                    <li><button onclick="location.href='login.php?logout=true';">Logout</button></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <div class="tables-container">
                <div class="table-wrapper">
                    <h1>Data from CSV Table</h1>
                    <table>
                        <thead>
                            <tr>
                                <?php
                                // Fetch column names
                                $sql = "SHOW COLUMNS FROM csv";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $columns = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $columns[] = $row['Field'];
                                        echo "<th>" . $row['Field'] . "</th>";
                                    }
                                    echo "<th>Actions</th>"; // Add a column for actions
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch data from the CSV table
                            $sql = "SELECT * FROM csv";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    foreach ($row as $key => $value) {
                                        echo "<td data-column=\"$key\">" . htmlspecialchars($value) . "</td>";
                                    }
                                    echo "<td></td>"; // Placeholder for actions
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='100%'>No data found</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        let isEditing = false;
        const isAdmin = <?php echo json_encode($isAdmin); ?>;

        function editTable() {
            isEditing = !isEditing;
            let rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let cells = row.getElementsByTagName('td');
                for (let i = 0; i < cells.length - 1; i++) { // Exclude the last cell (actions)
                    if (isEditing) {
                        if (i < 31) {
                            cells[i].contentEditable = isAdmin; // Only admin can edit columns 1-31
                        } else {
                            cells[i].contentEditable = true; // Anyone can edit other columns
                        }
                    } else {
                        cells[i].contentEditable = false;
                    }
                }
                // Add or remove action buttons
                let actionCell = cells[cells.length - 1];
                if (isEditing) {
                    actionCell.innerHTML = ''; // No need for individual row buttons in this mode
                } else {
                    actionCell.innerHTML = ''; // Remove action buttons if not editing
                }
            });

            // Toggle visibility of the Save button
            document.getElementById('saveButton').style.display = isEditing ? 'inline-block' : 'none';
            document.querySelector('button[onclick="editTable()"]').textContent = isEditing ? 'Cancel' : 'Edit';
        }

        function saveAllRows() {
            let rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let cells = row.getElementsByTagName('td');
                let rowData = [];
                for (let i = 0; i < cells.length - 1; i++) { // Exclude the last cell (actions)
                    rowData.push(cells[i].textContent);
                }
                
                // Send the row data to the server via AJAX
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_row.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify(rowData));

                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log('Row updated successfully');
                    }
                };
            });

            // Switch back to view mode
            editTable();
        }
    </script>
</body>
</html>
