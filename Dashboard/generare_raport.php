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

    // Adaugă logo-ul
    $logoPath = '../Imagini/LogoDinamoBucuresti.png';
    $pdf->Image($logoPath, 10, 10, 15, 0, 'PNG');

    $pdf->SetY(30);

    $pdf->Ln(5);

    switch ($reportType) {
        case 'utilizatori':
            generateUtilizatoriReport($conn, $pdf);
            break;
        case 'performantaEveniment':
            generatePerformantaEvenimentReport($conn, $pdf);
            break;
        case 'disponibilitateBilete':
            generateDisponibilitateBileteReport($conn, $pdf);
            break;
        case 'plati':
            generatePlatiReport($conn, $pdf);
            break;
        case 'vanzari':
            generateVanzariReport($conn, $pdf);
            break;
        case 'venituri':
            generateVenituriReport($conn, $pdf);
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

function generateUtilizatoriReport($conn, $pdf) {
    $sql = "SELECT Nume, Prenume, Email, Telefon 
            FROM Utilizatori WHERE Rol='user'";
    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12); 
    $pdf->Cell(0, 10, 'Raport Utilizatori', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="20%">Nume</th>
                    <th width="20%">Prenume</th>
                    <th width="35%">Email</th>
                    <th width="25%">Nr. de telefon</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $tbl .= '<tr>
                        <td width="20%">' . htmlspecialchars($row['Nume']) . '</td>
                        <td width="20%">' . htmlspecialchars($row['Prenume']) . '</td>
                        <td width="35%">' . htmlspecialchars($row['Email']) . '</td>
                        <td width="25%">' . htmlspecialchars($row['Telefon']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        // Setare font pentru conținut
        $pdf->SetFont('helvetica', '', 10); 
        $pdf->writeHTML($tbl, true, false, false, false, '');
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generatePerformantaEvenimentReport($conn, $pdf) {
    $sql = "SELECT 
                E.Nume_Eveniment, 
                E.Data_Eveniment, 
                E.Locatie_Eveniment,
                COUNT(B.BiletID) AS BileteVandute
            FROM 
                Evenimente E
            LEFT JOIN 
                Bilete B ON E.EvenimentID = B.EvenimentID
            GROUP BY 
                E.Nume_Eveniment, E.Data_Eveniment, E.Locatie_Eveniment";

    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Performanta Eveniment', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="43%">Denumire Eveniment</th>
                    <th width="17%">Data Eveniment</th>
                    <th width="25%">Locatie Eveniment</th>
                    <th width="15%" style="text-align:center;">Bilete Vandute</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $dataEveniment = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Eveniment'])));
            $tbl .= '<tr>
                        <td width="43%">' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td width="17%">' . $dataEveniment . '</td>
                        <td width="25%">' . htmlspecialchars($row['Locatie_Eveniment']) . '</td>
                        <td width="15%" style="text-align:center;">' . htmlspecialchars($row['BileteVandute']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        // Setare font pentru conținut
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu există date disponibile.', 0, 1, 'C');
    }
}

function generateDisponibilitateBileteReport($conn, $pdf) {
    $sql = "SELECT 
                E.Nume_Eveniment, 
                E.Data_Eveniment, 
                E.Locatie_Eveniment,
                (SELECT COUNT(DL.DetaliiLocID) 
                FROM DetaliiLocuri DL 
                WHERE DL.DetaliiLocID NOT IN (SELECT B.DetaliiLocID FROM Bilete B WHERE B.EvenimentID = E.EvenimentID)
                AND DL.Disponibilitate = 'Disponibil') AS NumarLocuriDisponibile
            FROM 
                Evenimente E";
    
    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Disponibilitate Bilete', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="40%">Denumire Eveniment</th>
                    <th width="17%">Data Eveniment</th>
                    <th width="25%">Locatie Eveniment</th>
                    <th width="15%" style="text-align:center;">Nr. Bilete Disponibile</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $dataEveniment = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Eveniment'])));
            $tbl .= '<tr>
                        <td width="40%">' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td width="17%">' . $dataEveniment . '</td>
                        <td width="25%">' . htmlspecialchars($row['Locatie_Eveniment']) . '</td>
                        <td width="15%" style="text-align:center;">' . htmlspecialchars($row['NumarLocuriDisponibile']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu există date disponibile.', 0, 1, 'C');
    }
}

function generatePlatiReport($conn, $pdf) {
    $sql = "SELECT 
                E.Nume_Eveniment, 
                E.Data_Eveniment, 
                E.Locatie_Eveniment, 
                P.Suma_Platita, 
                P.Data_Plata, 
                CONCAT(U.Nume, ' ', U.Prenume) AS Utilizator
            FROM 
                Plati P
            JOIN 
                Evenimente E ON P.EvenimentID = E.EvenimentID
            JOIN 
                Utilizatori U ON P.UtilizatorID = U.UtilizatorID";
    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Plati', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="20%">Denumire Eveniment</th>
                    <th width="15%">Data Eveniment</th>
                    <th width="25%">Locatie Eveniment</th>
                    <th width="13%" style="text-align:center;">Suma Platita (RON)</th>
                    <th width="12%">Data Plata</th>
                    <th width="15%">Utilizator</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $dataEveniment = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Eveniment'])));
            $dataPlata = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Plata'])));
            $tbl .= '<tr>
                        <td width="20%">' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td width="15%">' . $dataEveniment . '</td>
                        <td width="25%">' . htmlspecialchars($row['Locatie_Eveniment']) . '</td>
                        <td width="13%" style="text-align:center;">' . htmlspecialchars(number_format($row['Suma_Platita'], 2)) . '</td>
                        <td width="12%">' . $dataPlata . '</td>
                        <td width="15%">' . htmlspecialchars($row['Utilizator']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateVanzariReport($conn, $pdf) {
    $sql = "SELECT Evenimente.Nume_Eveniment, Evenimente.Data_Eveniment, Evenimente.Locatie_Eveniment, 
                    Vanzari.Data_Vanzare, CONCAT(Utilizatori.Nume, ' ', Utilizatori.Prenume) AS Utilizator, 
                    COUNT(Vanzari.BiletID) AS BileteVandute
            FROM Vanzari
            JOIN Evenimente ON Vanzari.EvenimentID = Evenimente.EvenimentID
            JOIN Utilizatori ON Vanzari.UtilizatorID = Utilizatori.UtilizatorID
            GROUP BY Evenimente.Nume_Eveniment, Evenimente.Data_Eveniment, Evenimente.Locatie_Eveniment, 
                    Vanzari.Data_Vanzare, Utilizator";
    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Vanzari', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="20%">Denumire Eveniment</th>
                    <th width="15%">Data Eveniment</th>
                    <th width="25%">Locatie Eveniment</th>
                    <th width="15%">Data Vanzare</th>
                    <th width="15%">Utilizator</th>
                    <th width="10%" style="text-align:center;">Bilete Vandute</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $dataEveniment = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Eveniment'])));
            $dataVanzare = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Vanzare'])));
            $tbl .= '<tr>
                        <td width="20%">' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td width="15%">' . $dataEveniment . '</td>
                        <td width="25%">' . htmlspecialchars($row['Locatie_Eveniment']) . '</td>
                        <td width="15%">' . $dataVanzare . '</td>
                        <td width="15%">' . htmlspecialchars($row['Utilizator']) . '</td>
                        <td width="10%" style="text-align:center;">' . htmlspecialchars($row['BileteVandute']) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateVenituriReport($conn, $pdf) {
    $sql = "SELECT 
                E.Nume_Eveniment, 
                E.Data_Eveniment, 
                E.Locatie_Eveniment, 
                SUM(P.Suma_Platita) AS Venituri
            FROM 
                Plati P
            JOIN 
                Evenimente E ON P.EvenimentID = E.EvenimentID
            GROUP BY 
                E.Nume_Eveniment, E.Data_Eveniment, E.Locatie_Eveniment";

    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Venituri', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="40%">Denumire Eveniment</th>
                    <th width="17%">Data Eveniment</th>
                    <th width="25%">Locatie Eveniment</th>
                    <th width="15%" style="text-align:center;">Venituri (RON)</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $dataEveniment = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Eveniment'])));
            $tbl .= '<tr>
                        <td width="40%">' . htmlspecialchars($row['Nume_Eveniment']) . '</td>
                        <td width="17%">' . $dataEveniment . '</td>
                        <td width="25%">' . htmlspecialchars($row['Locatie_Eveniment']) . '</td>
                        <td width="15%" style="text-align:center;">' . htmlspecialchars(number_format($row['Venituri'], 2)) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

function generateFacturiReport($conn, $pdf) {
    $sql = "SELECT Facturi.FacturaID, Facturi.Data_Factura, Utilizatori.Nume, Utilizatori.Prenume, Plati.Suma_Platita
            FROM Facturi
            JOIN Utilizatori ON Facturi.UtilizatorID = Utilizatori.UtilizatorID
            JOIN Plati ON Facturi.PlataID = Plati.PlataID";
    $result = $conn->query($sql);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Raport Facturi', 0, 1, 'C');
    $pdf->Ln(5);

    if ($result->num_rows > 0) {
        $tbl = '<table cellspacing="0" cellpadding="6" border="1">';
        $tbl .= '<tr style="background-color:#ED1C24; color:#fff; font-weight:bold;">
                    <th width="25%">Nr. Factura</th>
                    <th width="17%">Data Factura</th>
                    <th width="33%">Utilizator</th>
                    <th width="25%" style="text-align:center;">Suma facturata (RON)</th>
                </tr>';
        while ($row = $result->fetch_assoc()) {
            $numeComplet = htmlspecialchars($row['Nume']) . ' ' . htmlspecialchars($row['Prenume']);
            $dataFactura = htmlspecialchars(date('d-m-Y H:i', strtotime($row['Data_Factura'])));
            $tbl .= '<tr>
                        <td width="25%">' . htmlspecialchars($row['FacturaID']) . '</td>
                        <td width="17%">' . $dataFactura . '</td>
                        <td width="33%">' . $numeComplet . '</td>
                        <td width="25%" style="text-align:center;">' . htmlspecialchars(number_format($row['Suma_Platita'], 2)) . '</td>
                    </tr>';
        }
        $tbl .= '</table>';
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($tbl, true, false, false, false);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nu exista date disponibile.', 0, 1, 'C');
    }
}

$conn->close();
?>
