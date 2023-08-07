<!DOCTYPE html>
<html>

<head>
    <title>Deck/Card Browser</title>

    <script>
        // JavaScript function to handle sorting
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("cardsTable");
            switching = true;
            dir = "asc";

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];

                    if (dir === "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }

                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount === 0 && dir === "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }

        // Add event listener for sorting when clicking on table headers
        document.addEventListener("DOMContentLoaded", function() {
            var headers = document.querySelectorAll("th");
            headers.forEach(function(header, index) {
                header.addEventListener("click", function() {
                    sortTable(index);
                });
            });
        });
    </script>

    <style>
        body {
            background-color: #1b1b1b;
            color: #ffffff;
            font-family: "Arial", sans-serif;
            line-height: 1.4rem;
            margin: 0;
            padding: 0;
            font-size: 0.9rem;
            padding: 2rem;
        }

        h1 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            border: 1px solid #000;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #444;
            border-right: 1px solid #444;
        }


        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        th {
            background-color: rgb(144, 19, 254);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            /* Custom cursor for sortable headers */
        }

        /* Bold and italic formatting for table cell content */
        table strong {
            font-weight: bold;
        }

        table em {
            font-style: italic;
        }

        /* Optional: Add a subtle box shadow to the table for a 3D effect */
        table {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        a {
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php
    // Function to parse CSV data from a file and return an array
    function parseCSV($filename)
    {
        $data = array();
        $file = fopen($filename, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $data[] = $line;
        }
        fclose($file);
        return $data;
    }

    // Function to format card_text
    function formatCardText($text)
    {
        // Replace **bold** with <strong>bold</strong>
        $text = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $text);
        // Replace ~italic~ with <em>italic</em>
        $text = preg_replace('/~(.*?)~/', '<em>$1</em>', $text);

        return $text;
    }

    function addColorCube($color)
    {
        // Define an array of color-word pairs and their corresponding cube emoji
        $colorCubes = array(
            'blue' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: blue;"></div> Blue',
            'red' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: red;"></div> Red',
            'green' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: green;"></div> Green',
            'orange' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: orange;"></div> Orange',
            'purple' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: purple;"></div> Purple',
            'pink' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: pink;"></div> Pink',
            'yellow' => '<div style="display: inline-block; width: 15px; height: 15px; border-radius:50%; background-color: yellow;"></div> Yellow'
        );

        // Convert the color to lowercase for case-insensitive matching
        $colorLowercase = strtolower($color);

        // Check if the color is in the array and get the corresponding cube emoji
        if (array_key_exists($colorLowercase, $colorCubes)) {
            return $colorCubes[$colorLowercase];
        } else {
            return $color; // If color is not in the array, return the original color word
        }
    }

    function niceName($inputString) {
        // Replace hyphens with spaces
        $stringWithSpaces = str_replace('-', ' ', $inputString);
    
        // Capitalize each word
        $capitalizedString = ucwords($stringWithSpaces);
    
        return $capitalizedString;
    }

    //1) All card data

    // Load data from CSV file into memory
    $csvFile = 'all_cards.csv';
    $cardsData = parseCSV($csvFile);

    //2) A deck with a selection of cards
    $deckFile = (isset($_GET["deck"]) ? $_GET["deck"] : ""); //'deck.csv';

    if (!empty($deckFile) && file_exists($deckFile)) {



        echo  '<h1>' . niceName(sprintf("%s", str_replace(".csv", "", $deckFile))) . '</h1><p>Or view <a href="index.php">all cards</a></p>';


        // Load data from deck.csv into memory    
        $deckData = parseCSV($deckFile);
        // Create an associative array to store amounts by local_id
        $amountsByLocalID = array();
        foreach ($deckData as $row) {
            // Skip the header row
            if ($row[0] === 'local_id') continue;

            $localID = $row[0];
            $amount = $row[1];
            $amountsByLocalID[$localID] = $amount;
        }

        echo '<table id="cardsTable" border="1">
    <tr>
    <th>Amount</th>
    <th>Color</th>
    <th>Name</th>
    <th>Card Text</th>
    </tr>';

        $sum = 0;
        foreach ($cardsData as $card) {
            // Skip the header row
            if ($card[0] === 'local_id') continue;

            $localID = $card[0];

            // Check if the card's local_id is present in deck.csv
            if (isset($amountsByLocalID[$localID])) {
                echo '<tr>';

                // Display the amount for the matching local_id from decks.csv
                $amount = $amountsByLocalID[$localID];
                echo '<td>' . $amount . '</td>';
                if (is_numeric($amount)) $sum += $amount;
                echo '<td>' . addColorCube($card[6]) . '</td>';
                echo '<td>' . $card[2] . '</td>';
                echo '<td>' . formatCardText($card[3]) . '</td>';

                /*
            for ($i = 0; $i < count($card); $i++) {
                if (!in_array($i, array(2,3,4,5,6))) continue;
                echo '<td>' . formatCardText($card[$i]) . '</td>';
            }
            */


                echo '</tr>';
            }
        }

        echo '</table>';

        echo "<h2>Total cards: " . sprintf("%d", $sum) . "</h2>";
    } else {

        echo  '<h1>All RTLOL Cards</h1>
        <p>Or view a deck: 
        <a href="index.php?deck=red-theme-deck.csv">Red Theme Deck</a> - 
        <a href="index.php?deck=blue-theme-deck.csv">Blue Theme Deck</a> - 
        <a href="index.php?deck=orange-theme-deck.csv">Orange Theme Deck</a> - 
        <a href="index.php?deck=pink-theme-deck.csv">Pink Theme Deck</a> - 
        <a href="index.php?deck=purple-theme-deck.csv">Purple Theme Deck</a> - 
        <a href="index.php?deck=yellow-theme-deck.csv">Yellow Theme Deck</a> - 
        <a href="index.php?deck=green-theme-deck.csv">Green Theme Deck</a>
        </p>';

        echo '<table id="cardsTable" border="1">
      <tr>
      <th>Local ID</th>
      <th>NFT ID</th>
      <th>Name</th>
      <th>Card Text</th>
      <th>Power</th>
      <th>HP</th>
      <th>Color</th>
      <th>Tier</th>
      <th>Supply</th>
      <th>Set</th>
      <th>Colorless Cost</th>
      <th>Red Cost</th>
      <th>Blue Cost</th>
      <th>Orange Cost</th>
      <th>Pink Cost</th>
      <th>Purple Cost</th>
      <th>Yellow Cost</th>
      <th>Green Cost</th>
      <th>Rainbow Cost</th>
      <th>Max in Deck</th>
      <th>Type</th>
      <th>Icon</th>
      </tr>';

        foreach ($cardsData as $card) {
            // Skip the header row
            if ($card[0] === 'local_id') continue;


            //Example filter:

            // Check if the card is red and has a colorless cost less than 5
            //if ($card[6] === 'red' && intval($card[10]) < 5) {

            echo '<tr>';
            for ($i = 0; $i < count($card); $i++) {

                //if(in_array($i,array(0,1,19))) continue;

                if ($i === 3) {
                    echo '<td>' . formatCardText($card[$i]) . '</td>';
                } elseif ($i === 6) {
                    echo '<td>' . addColorCube($card[6]) . '</td>';
                } else {
                    echo '<td>' . $card[$i] . '</td>';
                }
            }
            echo '</tr>';

            // }




        }

        echo '</table>';
    }
    ?>

</body>

</html>