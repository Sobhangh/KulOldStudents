<!DOCTYPE html>
<html>
<head>
    <title>Student Search</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .sticky-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: blue;
            color: white;
            text-align: center;
            margin: 0;
            padding: 10px 0;
            z-index: 100;
        }
        .content-wrapper {
            padding-top: 70px; /* Adjust this value based on your header height */
        }
        form {
            border-width: 4px;
            border-style: solid;
            border-color: blue;
            border-radius: 5px;
        }
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
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .sticky-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: blue;
            color: white;
            text-align: center;
            margin: 0;
            padding: 10px 0;
            z-index: 100;
        }
        .content-wrapper {
            padding-top: 70px;
        }
        form {
            border-width: 4px;
            border-style: solid;
            border-color: blue;
            border-radius: 5px;
            width: 80%;
            margin: 0 auto;
            margin-bottom: 20px;
            padding: 20px;
        }
        .result-table {
            margin-top: 20px;
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
        }
        .form-input {
            width: 95%;
            padding: 8px;
            font-size: 16px;
        }
        .submit-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1 class="sticky-header">Student Search</h1>
    <div class="content-wrapper">
        <form method="GET" style="width: 80%; margin: 0 auto; padding: 20px;">
            <div class="form-group">
                <label for="firstname" class="form-label">First name:</label>
                <input type="text" id="firstname" name="firstname" class="form-input" placeholder="Can use operators AND, OR, NOT (use paranthesis for disambiguation) and SQL string operators _ , %">
            </div>
            <div class="form-group">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Same format as First name">
            </div>
            <div class="form-group">
                <label for="origin" class="form-label">Origin:</label>
                <input type="text" id="origin" name="origin" class="form-input" placeholder="Same format as First name">
            </div>
            <div class="form-group">
                <label for="datefrom" class="form-label">Date from:</label>
                <input type="text" id="datefrom" name="datefrom" class="form-input" placeholder="Date format: YEAR-MONTH-DAY; If empty means open ended; put * if the same as the other date field">
            </div>
            <div class="form-group">
                <label for="dateto" class="form-label">Date to:</label>
                <input type="text" id="dateto" name="dateto" class="form-input" placeholder="Date format: YEAR-MONTH-DAY; If empty means open ended; put * if the same as the other date field">
            </div>
            <div class="form-group">
                <label for="orderby" class="form-label">Order by:</label>
                <input type="text" id="orderby" name="orderby" class="form-input" placeholder="Comma separated fields to order the output; Use Date for date fields; use ASC, DESC for sort direction">
            </div>
            <input type="submit" value="Search" class="submit-button">
        </form>

        <?php

            function parse_string_fields(&$params,$content, $db_field, $field){
                $result = '(';
                $clen = strlen($content);
                $i = 0;
                $highlight_begin = -1;
                $highlight = $pending_not = $not_state = $and_state = $or_state = $para_state= false;
                while ($i < $clen){
                    //setting the states
                    if($highlight){
                        if(substr($content,$i+1,5) == ' NOT '){
                            $highlight = false;
                        }
                        else if(substr($content,$i+1,5) == ' AND '){
                            $highlight = false;
                        }
                        else if(substr($content,$i+1,4) == ' OR '){
                            $highlight = false;
                        }
                        else if(substr($content,$i+1,1) == ')' || substr($content,$i+1,1) == '('){
                            $highlight = false;
                        }
                    }
                    else{
                        if(substr($content,$i,4) == 'NOT '){
                            $not_state = true;
                        }
                        else if(substr($content,$i,4) == 'AND '){
                            $and_state = true;
                        }
                        else if(substr($content,$i,3) == 'OR '){
                            $or_state = true;
                        }
                        else if(substr($content,$i,1) == ')' || substr($content,$i,1) == '('){
                            $para_state = true;
                        }
                        else if($content[$i] != ' '){
                            $highlight_begin = $i;
                            $highlight = true;
                        }
                    }
                    /*if($not_state || $and_state || $or_state || $para_state){
                        $highlight = false;
                    }*/

                    if(!$highlight && $highlight_begin != -1){
                        $word = substr($content,$highlight_begin,$i-$highlight_begin+1);
                        $params[$field.$i] = $word;
                        $result .= $db_field;
                        if($pending_not){
                            $result .= ' NOT';
                            $pending_not = false;
                        }
                        $result .= ' LIKE :'.$field.$i;
                        $i++;
                        $highlight_begin = -1;
                    }
                    else if($not_state){
                        //expressions like NOT (firstname LIKE 'a' AND name LIKE 'b') are not allowed
                        $pending_not = true;
                        $not_state = false;
                        $i+=4;
                    }
                    else if($and_state){  
                        $result .= ' AND ';
                        $and_state = false;
                        $i+=4;
                    }
                    else if($or_state){
                        $result .= ' OR ';
                        $or_state = false;
                        $i+=3;
                    }
                    else if($para_state){
                        $result .= $content[$i];
                        $para_state = false;
                        $i++;
                    }
                    else{
                        $i++;
                    }
                }
                if($highlight_begin != -1){
                    $word = substr($content,$highlight_begin,$i-$highlight_begin+1);
                    $params[$field.$i] = $word;
                    $result .= ' '.$db_field;
                    if($pending_not){
                        $result .= ' NOT';
                        $pending_not = false;
                    }
                    $result .= ' LIKE :'.$field.$i;
                }
                $result .= ')';
                return $result;

            }

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
                    //$where_conditions[] = "($db_field LIKE :$field)";
                    $where_conditions[] = parse_string_fields($params,$_GET[$field],$db_field,$field);
                    //TODO: what about and or not 
                    //$params[$field] = $_GET[$field];
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
            //echo "<br>";
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
    </div>
</body>
</html>

