<!DOCTYPE html>
<html>
<head>
    <title>Student Search</title>
    <style>
        .result-table {
            height: 400px;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <form method="GET" style="width: 80%; margin: 0 auto; padding: 20px;">
        <div style="margin-bottom: 15px;">
            <label for="firstname" style="display: block; margin-bottom: 5px;">First name:</label>
            <input type="text" id="firstname" name="firstname" placeholder="Can use operators AND, OR, NOT (use paranthesis for disambiguation) and SQL string operators _ , %" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; margin-bottom: 5px;">Name:</label>
            <input type="text" id="name" name="name" placeholder="Same format as First name" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="origin" style="display: block; margin-bottom: 5px;">Origin:</label>
            <input type="text" id="origin" name="origin" placeholder="Same format as First name" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="datefrom" style="display: block; margin-bottom: 5px;">Date from:</label>
            <input type="text" id="datefrom" name="datefrom" placeholder="Date format: YEAR-MONTH-DAY; If empty means open ended; put * if the same as the other date field" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="dateto" style="display: block; margin-bottom: 5px;">Date to:</label>
            <input type="text" id="dateto" name="dateto" placeholder="Date format: YEAR-MONTH-DAY; If empty means open ended; put * if the same as the other date field" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="orderby" style="display: block; margin-bottom: 5px;">Order by:</label>
            <input type="text" id="orderby" name="orderby" placeholder="Comma separated fields to order the output; Use Date for date fields; use ASC, DESC for sort direction" style="width: 100%; padding: 8px; font-size: 16px;">
        </div>
        <input type="submit" value="Search" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET)) {
        $conn = new PDO("mysql:host=localhost;dbname=kulstudents", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $where_conditions = [];
        $params = [];

        // Process string fields (firstname, name, origin)
        $string_fields = [
            'firstname' => 'Voornaam',
            'name' => 'Naam',
            'origin' => 'Herkomst'
        ];

        foreach ($string_fields as $field => $db_field) {
            if (!empty($_GET[$field])) {
                $where_conditions[] = "($db_field LIKE :$field)";
                //TODO: what about and or not 
                $params[$field] = $_GET[$field];
            }
        }

        // Process date fields
        if (!empty($_GET['datefrom']) || !empty($_GET['dateto'])) {
            if (!empty($_GET['datefrom']) && !empty($_GET['dateto'])) {
                if ($_GET['datefrom'] == '*') {
                    $where_conditions[] = "Datum_Inschrijving = :dateto";
                    $params['dateto'] = $_GET['dateto'];
                } elseif ($_GET['dateto'] == '*') {
                    $where_conditions[] = "Datum_Inschrijving = :datefrom";
                    $params['datefrom'] = $_GET['datefrom'];
                } else {
                    $where_conditions[] = "Datum_Inschrijving BETWEEN :datefrom AND :dateto";
                    $params['datefrom'] = $_GET['datefrom'];
                    $params['dateto'] = $_GET['dateto'];
                }
            } elseif (!empty($_GET['datefrom'])) {
                $where_conditions[] = "Datum_Inschrijving > :datefrom";
                $params['datefrom'] = $_GET['datefrom'];
            } elseif (!empty($_GET['dateto'])) {
                $where_conditions[] = "Datum_Inschrijving < :dateto";
                $params['dateto'] = $_GET['dateto'];
            }
        }

        $query = "SELECT * FROM students";
        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(" AND ", $where_conditions);
        }

        // Process ORDER BY
        if (!empty($_GET['orderby'])) {
            $orderby_fields = explode(',', $_GET['orderby']);
            $valid_fields = [
                'First name' => 'Voornaam',
                'Name' => 'Naam',
                'Origin' => 'Herkomst',
                'Date' => 'Datum_Inschrijving'
            ];
            
            $order_parts = [];
            foreach ($orderby_fields as $field) {
                $field = trim($field);
                $direction = '';
                if (stripos($field, 'ASC') !== false) {
                    $field = trim(str_replace('ASC', '', $field));
                    $direction = 'ASC';
                } elseif (stripos($field, 'DESC') !== false) {
                    $field = trim(str_replace('DESC', '', $field));
                    $direction = 'DESC';
                }
                
                if (isset($valid_fields[$field])) {
                    $order_parts[] = $valid_fields[$field] . ' ' . $direction;
                }
            }
            
            if (!empty($order_parts)) {
                $query .= " ORDER BY " . implode(', ', $order_parts);
            }
        }

        $stmt = $conn->prepare($query);
        //var_dump($stmt);
        //var_dump($params);
        //echo "<br>";
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    

        if ($results) {
            echo '<div class="result-table">';
            echo '<table>';
            echo '<tr>';
            echo '<th>First name</th>';
            echo '<th>Name</th>';
            echo '<th>Origin</th>';
            echo '<th>Origin current name</th>';
            echo '<th>Bisdom</th>';
            echo '<th>Registration date</th>';
            echo '</tr>';

            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['Voornaam']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Naam']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Herkomst']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Herkomst_actuele_Schrijfwijze']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Bisdom']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Datum_Inschrijving']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>No results found.</p>';
        }
    }
    ?>
</body>
</html>

