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

// Query per estrarre i dati dalle tabelle
$sqlOfferta = "SELECT qtaofferta, prezzoofferta FROM offerta";
$sqlDomanda = "SELECT qtadomanda, prezzodomanda FROM domanda";

// Esegui le query
$resultOfferta = $conn->query($sqlOfferta);
$resultDomanda = $conn->query($sqlDomanda);

// Crea array per memorizzare i dati
$offertaData = [];
$domandaData = [];

// Estrai i dati dalle tabelle e memorizzali negli array
if ($resultOfferta->num_rows > 0) {
    while($rowOfferta = $resultOfferta->fetch_assoc()) {
        $offertaData[] = $rowOfferta;
    }
}

if ($resultDomanda->num_rows > 0) {
    while($rowDomanda = $resultDomanda->fetch_assoc()) {
        $domandaData[] = $rowDomanda;
    }
}

// Costruisci la tabella HTML
echo "<table border='1'>";
echo "<tr><th>qtaofferta</th><th>prezzoofferta</th><th>qtadomanda</th><th>prezzodomanda</th></tr>";

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

// Chiudi la connessione
$conn->close();
?>
