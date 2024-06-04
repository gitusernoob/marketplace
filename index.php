<?php
// Dati per la connessione al database
$servername = "localhost";
$username = "root";
$password = "Sw2023";
$dbname = "book";

// Crea connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Verifica se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'];
    $quantita = $_POST['quantita'];
    $prezzo = $_POST['prezzo'];

    // Preparare ed eseguire la query di inserimento in base al tipo
    if ($tipo == "offerta") {
        $sqlInsert = "INSERT INTO offerta (qtaofferta, prezzoofferta) VALUES (?, ?)";
    } else {
        $sqlInsert = "INSERT INTO domanda (qtadomanda, prezzodomanda) VALUES (?, ?)";
    }

    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("id", $quantita, $prezzo);

    if ($stmt->execute()) {
        echo "Inserimento avvenuto con successo!";
    } else {
        echo "Errore durante l'inserimento: " . $stmt->error;
    }

    $stmt->close();
}

// Query per estrarre i dati dalle tabelle
$sqlOfferta = "SELECT qtaofferta, prezzoofferta FROM offerta";
$sqlDomanda = "SELECT qtadomanda, prezzodomanda FROM domanda";

// Esegui le query
$resultOfferta = $conn->query($sqlOfferta);
$resultDomanda = $conn->query($sqlDomanda);

// Crea array per memorizzare i dati
$offertaData = [];
$domandaData = [];
$transazioni = [];

// Estrai i dati dalle tabelle e memorizzali negli array
if ($resultOfferta->num_rows > 0) {
    while ($rowOfferta = $resultOfferta->fetch_assoc()) {
        $offertaData[] = $rowOfferta;
    }
}

if ($resultDomanda->num_rows > 0) {
    while ($rowDomanda = $resultDomanda->fetch_assoc()) {
        $domandaData[] = $rowDomanda;
    }
}

// Controlla se ci sono corrispondenze tra domanda e offerta
foreach ($domandaData as $dKey => $domanda) {
    foreach ($offertaData as $oKey => $offerta) {
        if ($domanda['prezzodomanda'] == $offerta['prezzoofferta']) {
            $quantita_da_rimuovere = min($domanda['qtadomanda'], $offerta['qtaofferta']);

            // Aggiungi la transazione all'array delle transazioni
            $transazioni[] = [
                'quantita' => $quantita_da_rimuovere,
                'prezzo' => $domanda['prezzodomanda']
            ];

            // Aggiorna le quantità di offerta e domanda
            $domandaData[$dKey]['qtadomanda'] -= $quantita_da_rimuovere;
            $offertaData[$oKey]['qtaofferta'] -= $quantita_da_rimuovere;

            // Rimuovi l'offerta se esaurita
            if ($offertaData[$oKey]['qtaofferta'] == 0) {
                unset($offertaData[$oKey]);
            }

            // Rimuovi la domanda se esaurita
            if ($domandaData[$dKey]['qtadomanda'] == 0) {
                unset($domandaData[$dKey]);
                break;
            }
        }
    }
}

// Filtra gli array per rimuovere le chiavi non numeriche (dopo unset)
$offertaData = array_values($offertaData);
$domandaData = array_values($domandaData);

// Inizio HTML
echo '<!DOCTYPE html>';
echo '<html lang="it">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Gestione Domanda e Offerta</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo 'h2 { color: #333; }';
echo 'form { margin-bottom: 20px; }';
echo 'label { display: inline-block; width: 100px; margin-bottom: 10px; }';
echo 'input[type="number"], input[type="submit"] { padding: 5px; margin-bottom: 10px; }';
echo 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
echo 'table, th, td { border: 1px solid #ddd; }';
echo 'th, td { padding: 10px; text-align: left; }';
echo 'th { background-color: #f4f4f4; }';
echo 'tr:nth-child(even) { background-color: #f9f9f9; }';
echo 'tr:hover { background-color: #f1f1f1; }';
echo '</style>';
echo '</head>';
echo '<body>';

// Form HTML per l'inserimento dei dati
echo '<form method="post" action="">';
echo '<label for="tipo">Tipo:</label>';
echo '<input type="radio" id="offerta" name="tipo" value="offerta" required>';
echo '<label for="offerta">Offerta</label>';
echo '<input type="radio" id="domanda" name="tipo" value="domanda" required>';
echo '<label for="domanda">Domanda</label><br>';
echo '<label for="quantita">Quantità:</label>';
echo '<input type="number" id="quantita" name="quantita" required><br>';
echo '<label for="prezzo">Prezzo:</label>';
echo '<input type="number" id="prezzo" name="prezzo" step="0.01" required><br>';
echo '<input type="submit" value="Inserisci">';
echo '</form>';

// Costruisci la tabella HTML per le domande e le offerte
echo "<h2>Offerte e Domande</h2>";
echo "<table>";
echo "<tr><th>Quantità Offerta</th><th>Prezzo Offerta</th><th>Quantità Domanda</th><th>Prezzo Domanda</th></tr>";

// Determina il numero massimo di righe da visualizzare
$maxRows = max(count($offertaData), count($domandaData));

// Stampa le righe della tabella combinando i dati disponibili
for ($i = 0; $i < $maxRows; $i++) {
    echo "<tr>";
    if (isset($offertaData[$i])) {
        echo "<td>" . $offertaData[$i]['qtaofferta'] . "</td>";
        echo "<td>" . $offertaData[$i]['prezzoofferta'] . "</td>";
    } else {
        echo "<td></td><td></td>";
    }

    if (isset($domandaData[$i])) {
        echo "<td>" . $domandaData[$i]['qtadomanda'] . "</td>";
        echo "<td>" . $domandaData[$i]['prezzodomanda'] . "</td>";
    } else {
        echo "<td></td><td></td>";
    }
    echo "</tr>";
}

echo "</table>";

// Costruisci la tabella HTML per le transazioni
echo "<h2>Transazioni Eseguite</h2>";
echo "<table>";
echo "<tr><th>Quantità</th><th>Prezzo</th></tr>";

// Stampa le righe della tabella delle transazioni
foreach ($transazioni as $transazione) {
    echo "<tr>";
    echo "<td>" . $transazione['quantita'] . "</td>";
    echo "<td>" . $transazione['prezzo'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Chiudi la connessione
$conn->close();

echo '</body>';
echo '</html>';
?>
