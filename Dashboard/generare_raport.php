<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
include '../db_connect.php';

use TCPDF;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reportType'])) {
    $reportType = $_POST['reportType'];

    // Creează un nou obiect TCPDF
    $pdf = new TCPDF();

    // Setări de bază pentru document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Raport');

    // Adaugă o pagină
    $pdf->AddPage();

    // Setează fontul
    $pdf->SetFont('helvetica', '', 12);

    switch ($reportType) {
        case 'vanzariBilete':
            generateVanzariBileteReport($conn, $pdf);
            break;
        case 'disponibilitateBilete':
            generateDisponibilitateBileteReport($conn, $pdf);
            break;
        case 'performantaEveniment':
            generatePerformantaEvenimentReport($conn, $pdf);
            break;
        case 'venituri':
            generateVenituriReport($conn, $pdf);
            break;
        case 'utilizatori':
            generateUtilizatoriReport($conn, $pdf);
            break;
        case 'facturi':
            generateFacturiReport($conn, $pdf);
            break;
        default:
            $pdf->Cell(0, 10, "Tip de raport necunoscut.", 0, 1, 'C');
    }

    // Output PDF
    $pdf->Output('raport.pdf', 'I');
}

function generateVanzariBileteReport($conn, $pdf) {
    $sql = "SELECT Evenimente.Nume_Eveniment, COUNT(Vanzari.BiletID) AS BileteVandute
            FROM Vanzari
            JOIN Evenimente ON Vanzari.EvenimentID = Evenimente.EvenimentID
            GROUP BY Evenimente.Nume_Eveniment";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Vanzari Bilete', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Eveniment</th>
                        <th>Bilete Vandute</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td>' . htmlspecialchars($row['BileteVandute']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateDisponibilitateBileteReport($conn, $pdf) {
    $sql = "SELECT Disponibilitate, COUNT(*) AS NumarLocuri
            FROM DetaliiLocuri
            GROUP BY Disponibilitate";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Disponibilitate Bilete', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Disponibilitate</th>
                        <th>Numar Locuri</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['Disponibilitate']) . '</td>
                        <td>' . htmlspecialchars($row['NumarLocuri']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu există date disponibile.', 0, 1, 'C');
    }
}

function generatePerformantaEvenimentReport($conn, $pdf) {
    $sql = "SELECT Evenimente.Nume_Eveniment, 
                   COUNT(CASE WHEN DetaliiLocuri.Disponibilitate = 'Ocupat' THEN 1 END) / COUNT(*) * 100 AS GradOcupare
            FROM DetaliiLocuri
            JOIN Evenimente ON DetaliiLocuri.EvenimentID = Evenimente.EvenimentID
            GROUP BY Evenimente.Nume_Eveniment";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Performanta Eveniment', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Eveniment</th>
                        <th>Grad de Ocupare (%)</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td>' . htmlspecialchars(number_format($row['GradOcupare'], 2)) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateVenituriReport($conn, $pdf) {
    $sql = "SELECT Evenimente.Nume_Eveniment, SUM(Plati.Suma_Platita) AS Venituri
            FROM Plati
            JOIN Evenimente ON Plati.EvenimentID = Evenimente.EvenimentID
            GROUP BY Evenimente.Nume_Eveniment";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Venituri', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Eveniment</th>
                        <th>Venituri (RON)</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td>' . htmlspecialchars(number_format($row['Venituri'], 2)) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateUtilizatoriReport($conn, $pdf) {
    $sql = "SELECT Rol, COUNT(*) AS NumarUtilizatori
            FROM Utilizatori
            GROUP BY Rol";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Utilizatori', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Rol</th>
                        <th>Numar Utilizatori</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['Rol']) . '</td>
                        <td>' . htmlspecialchars($row['NumarUtilizatori']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateFacturiReport($conn, $pdf) {
    $sql = "SELECT Facturi.FacturaID, Facturi.Data_Factura, Utilizatori.Nume AS NumeUtilizator, Plati.Suma_Platita
            FROM Facturi
            JOIN Utilizatori ON Facturi.UtilizatorID = Utilizatori.UtilizatorID
            JOIN Plati ON Facturi.PlataID = Plati.PlataID";
    $result = $conn->query($sql);

    $pdf->Cell(0, 10, 'Raport Facturi', 0, 1, 'C');
    $pdf->Ln(10);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th>Factura ID</th>
                        <th>Data Factura</th>
                        <th>Utilizator</th>
                        <th>Suma Platita (RON)</th>
                    </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td>' . htmlspecialchars($row['FacturaID']) . '</td>
                        <td>' . htmlspecialchars($row['Data_Factura']) . '</td>
                        <td>' . htmlspecialchars($row['NumeUtilizator']) . '</td>
                        <td>' . htmlspecialchars(number_format($row['Suma_Platita'], 2)) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

$conn->close();
?>
