<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

include '../db_connect.php';

$disponibilitati = [];

$sql = "SELECT Sector, COUNT(*) as numar_disponibile FROM DetaliiLocuri WHERE Disponibilitate = 'Disponibil' GROUP BY Sector";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $disponibilitati[$row["Sector"]] = $row["numar_disponibile"];
    }
} else {
    echo "0 rezultate";
}

$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selectare locuri</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="/SelectareLocuri/selectare_locuri.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid position-relative">
            <a class="navbar-brand logo-outside" href="/MeniuPrincipal/meniu_principal.php">
                <img src="/Imagini/LogoDinamoBucuresti.png" alt="Logo" height="100" width="90">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars text-black"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item mr-4">
                        <a class="nav-link text-white" href="../InformatiiUtile/informatii_utile.php">INFORMAȚII UTILE</a>
                    </li>
                    <!-- Verifică dacă utilizatorul este autentificat și afișează link-uri corespunzătoare -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item mr-4">
                            <a class="nav-link text-white" href="/Profil/profil.php">CONTUL MEU</a>
                        </li>
                        <li class="nav-item mr-3">
                            <a class="nav-link text-white" href="../logout.php">DECONECTEAZĂ-TE</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mr-3">
                            <a class="nav-link text-white" href="/Autentificare/autentificare.php">INTRĂ ÎN CONT</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="step-progress">
        <div class="step">
            <div class="step-circle active"><span>1</span></div>
            <div class="step-label">SELECTARE LOCURI</div>
        </div>
        <div class="step">
            <div class="step-circle"><span>2</span></div>
            <div class="step-label">COMANDA</div>
        </div>
        <div class="step">
            <div class="step-circle"><span>3</span></div>
            <div class="step-label">FINALIZARE</div>
        </div>
    </div>

    <h2 class="mt-5 mb-4">Selectează locurile:</h2>

    <div class="row row-even-spacing" style="margin-right: 0px;">                   
        <div class="col-md-5 col-xs-12 row-even-spacing" style="margin-left: auto;">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>
                        <small class="text-help pull-right">
                            <i class="icon-left fa fa-question-circle"></i>
                            click pe zona sau prețul dorit
                        </small>
                        Harta interactivă
                    </h4>
                </div>
                <div class="panel-body">
                    <div id="svg_map_container" class="svg-map-container" style="position: relative; overflow: hidden;">
                        <div class="svg-map-controls" style="position: absolute; right: 4px; top: 4px; z-index: 100;">
                            <button class="svg-map-zoom-in btn btn-default">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button class="svg-map-zoom-out btn btn-default">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <div class="svg-map-parent" style="height: 300px; transform: none; backface-visibility: hidden; transform-origin: 50% 50%; transition: transform 200ms ease-in-out 0s;">
                            <svg id="Layer_1" version="1.1" xmlns:x="&ns_extend;" xmlns:i="&ns_ai;" xmlns:graph="&ns_graphs;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 2400 1900" style="enable-background:new 0 0 2400 1900;" xml:space="preserve" width="100%" height="100%">              
                                <g>
                                    <g>
                                        <path style="fill:#E6E6E6;" d="M206,923.8l425.1-627c68.3-100.7,182.1-161.1,303.8-161.1h1255.4c29.8,0,54,24.2,54,54v1522
                                            c0,29.8-24.2,54-54,54h-1966c-29.8,0-54-24.2-54-54V1040C170.3,998.6,182.8,958.1,206,923.8z"></path>
                                        <text transform="matrix(1 0 0 1 1020.5798 93.7314)" style="font-family:'Arial'; font-size:60px;">Tribuna I Vest</text>
                                        <text transform="matrix(1 0 0 1 1028.929 1838.2756)" style="font-family:'Arial'; font-size:60px;">Tribuna II Est</text>
                                        <text transform="matrix(-1.836970e-16 1 -1 -1.836970e-16 2302.1777 785.6434)" style="font-family:'Arial'; font-size:60px;">Peluza Nord</text>
                                        <text transform="matrix(6.123234e-17 -1 1 6.123234e-17 97.8219 1104.1738)" style="font-family:'Arial'; font-size:60px;">Peluza Sud</text>
                                        <g>
                                            <path style="fill:#548A6A;" d="M1964,1509l-1477.3,0c-6.3,0-11.3-5.1-11.3-11.3l0-925.9c0-6.3,5.1-11.3,11.3-11.3l1477.3,0
                                                c6.3,0,11.3,5.1,11.3,11.3l0,925.9C1975.3,1503.9,1970.3,1509,1964,1509z"></path>
                                            <g>
                                                <path id="path2258" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M549.6,595.4l1349.7,0l1.7,879.3l-1351.4,0
                                                    L549.6,595.4z"></path>
                                                <path id="path3169" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M693.6,662.7h-21.9"></path>
                                                <path id="path2266" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M912.6,595.9c1,871.1-1,879.7-1,879.7"></path>
                                                <path id="path2268" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M657.7,595.9c1,871.1-1,872-1,872"></path>
                                                <path id="path2264" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M1231.8,596.8c1,871.1-1,875.8-1,875.8"></path>
                                                <path id="path4888" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M1538.5,595c1,871.1-1,880.1-1,880.1"></path>
                                                <path id="path4895" style="fill:none;stroke:#FFFFFF;stroke-width:1.8093;" d="M1789.8,595.4c1,878.4-1,879.3-1,879.3"></path>
                                                <path id="path5866" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M735.2,662.7h-25"></path>
                                                <path id="path5868" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M776.8,662.7h-25"></path>
                                                <path id="path5870" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M818.4,662.7h-25"></path>
                                                <path id="path5872" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M860,662.7h-25"></path>
                                                <path id="path5874" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M901.6,662.7h-25"></path>
                                                <path id="path5876" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M943.2,662.7h-25"></path>
                                                <path id="path5878" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M984.8,662.7h-25"></path>
                                                <path id="path5880" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1026.4,662.7l-25,0"></path>
                                                <path id="path5882" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1068,662.7h-25"></path>
                                                <path id="path5884" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1109.6,662.7h-25"></path>
                                                <path id="path5886" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1151.2,662.7h-25"></path>
                                                <path id="path5888" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1192.8,662.7h-25"></path>
                                                <path id="path5890" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1234.5,662.7h-25"></path>
                                                <path id="path5892" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1276.1,662.7h-25"></path>
                                                <path id="path5895" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1317.7,662.7h-25"></path>
                                                <path id="path5897" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1359.3,662.7h-25"></path>
                                                <path id="path5899" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1400.9,662.7l-25,0"></path>
                                                <path id="path5901" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1442.5,662.7h-25"></path>
                                                <path id="path5903" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1484.1,662.7h-25"></path>
                                                <path id="path5905" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1525.7,662.7h-25"></path>
                                                <path id="path5907" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1567.3,662.7h-25"></path>
                                                <path id="path5909" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1608.9,662.7h-25"></path>
                                                <path id="path5911" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1650.5,662.7h-25"></path>
                                                <path id="path5917" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1775.3,662.7l-25,0"></path>
                                                <path id="path6890" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M692.3,1394.7h-21.9"></path>
                                                <path id="path6892" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M733.9,1394.7h-25"></path>
                                                <path id="path6894" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M775.5,1394.7h-25"></path>
                                                <path id="path6896" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M817.1,1394.7h-25"></path>
                                                <path id="path6898" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M858.7,1394.7h-25"></path>
                                                <path id="path6900" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M900.3,1394.7h-25"></path>
                                                <path id="path6902" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M941.9,1394.7h-25"></path>
                                                <path id="path6904" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M983.5,1394.7h-25"></path>
                                                <path id="path6906" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1025.1,1394.7h-25"></path>
                                                <path id="path6908" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1066.7,1394.7h-25"></path>
                                                <path id="path6912" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1150,1394.7h-25"></path>
                                                <path id="path6914" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1191.6,1394.7h-25"></path>
                                                <path id="path6916" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1233.2,1394.7h-25"></path>
                                                <path id="path6918" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1274.8,1394.7h-25"></path>
                                                <path id="path6920" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1316.4,1394.7h-25"></path>
                                                <path id="path6922" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1358,1394.7h-25"></path>
                                                <path id="path6924" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1399.6,1394.7h-25"></path>
                                                <path id="path6926" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1441.2,1394.7h-25"></path>
                                                <path id="path6928" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1482.8,1394.7h-25"></path>
                                                <path id="path6930" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1524.4,1394.7h-25"></path>
                                                <path id="path6932" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1566,1394.7h-25"></path>
                                                <path id="path6934" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1607.6,1394.7h-25"></path>
                                                <path id="path6936" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1649.2,1394.7h-25"></path>
                                                <path id="path6942" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1774.1,1394.7h-25"></path>
                                                <path id="path7023" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1733.1,1394.7h-25"></path>
                                                <path id="path7025" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1733.7,662.7h-25"></path>
                                                <path id="path5913" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1696,662.7h-25"></path>
                                                <path id="path6938" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1690.6,1394.7h-25"></path>
                                                <path id="path6946" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1690.6,1394.7h-25"></path>
                                                <path id="path16549" style="fill:none;stroke:#FFFFFF;stroke-width:1.2062;" d="M1108.9,1394.7h-25"></path>
                                                <g id="g8121">
                                                    <g id="g17620" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path16647" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,809.9v19.4"></path>
                                                        <path id="path17618" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,839l0,19.4"></path>
                                                    </g>
                                                    <g id="g17624" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path17626" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,809.9v19.4"></path>
                                                        <path id="path17628" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,839l0,19.4"></path>
                                                    </g>
                                                    <g id="g17630" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path17632" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,809.9v19.4"></path>
                                                        <path id="path17634" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1580.3,839l0,19.4"></path>
                                                    </g>
                                                    <g id="g17636" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path17638" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,868.1v19.4"></path>
                                                        <path id="path17640" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,897.2v19.4"></path>
                                                    </g>
                                                    <g id="g17642" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path17644" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,868.1v19.4"></path>
                                                        <path id="path17646" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,897.2v19.4"></path>
                                                    </g>
                                                    <g id="g17648" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path17650" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,868.1v19.4"></path>
                                                        <path id="path17652" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1550.3,897.2v19.4"></path>
                                                    </g>
                                                    <g id="g17654" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path17656" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,926.3v19.4"></path>
                                                        <path id="path17658" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,955.4v19.4"></path>
                                                    </g>
                                                    <g id="g17660" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path17662" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,926.3v19.4"></path>
                                                        <path id="path17664" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,955.4v19.4"></path>
                                                    </g>
                                                    <g id="g17666" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path17668" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,926.3v19.4"></path>
                                                        <path id="path17670" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1520.3,955.4v19.4"></path>
                                                    </g>
                                                    <g id="g17672" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path17674" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,984.5v19.4"></path>
                                                        <path id="path17676" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,1013.6v19.4"></path>
                                                    </g>
                                                    <g id="g17678" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path17680" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,984.5v19.4"></path>
                                                        <path id="path17682" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,1013.6v19.4"></path>
                                                    </g>
                                                    <g id="g17684" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path17686" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,984.5v19.4"></path>
                                                        <path id="path17688" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1490.3,1013.6v19.4"></path>
                                                    </g>
                                                    <g id="g17690" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path17692" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1042.7v19.4"></path>
                                                        <path id="path17694" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1071.8v19.4"></path>
                                                    </g>
                                                    <g id="g17696" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path17698" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1042.7v19.4"></path>
                                                        <path id="path17700" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1071.8v19.4"></path>
                                                    </g>
                                                    <g id="g17702" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path17704" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1042.7v19.4"></path>
                                                        <path id="path17706" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1460.3,1071.8v19.4"></path>
                                                    </g>
                                                    <g id="g17708" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path17710" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1100.9v19.4"></path>
                                                        <path id="path17712" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1130v19.4"></path>
                                                    </g>
                                                    <g id="g17714" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path17716" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1100.9v19.4"></path>
                                                        <path id="path17718" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1130v19.4"></path>
                                                    </g>
                                                    <g id="g17720" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path17722" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1100.9v19.4"></path>
                                                        <path id="path17724" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1430.3,1130v19.4"></path>
                                                    </g>
                                                    <g id="g17726" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path17728" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1159.1v19.4"></path>
                                                        <path id="path17730" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1188.2v19.4"></path>
                                                    </g>
                                                    <g id="g17732" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path17734" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1159.1v19.4"></path>
                                                        <path id="path17736" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1188.2v19.4"></path>
                                                    </g>
                                                    <g id="g17738" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path17740" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1159.1v19.4"></path>
                                                        <path id="path17742" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1400.3,1188.2v19.4"></path>
                                                    </g>
                                                    <g id="g17744" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path17746" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1217.3v19.4"></path>
                                                        <path id="path17748" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1246.4v19.4"></path>
                                                    </g>
                                                    <g id="g17750" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path17752" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1217.3v19.4"></path>
                                                        <path id="path17754" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1246.4v19.4"></path>
                                                    </g>
                                                    <g id="g17756" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path17758" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1217.3v19.4"></path>
                                                        <path id="path17760" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1370.3,1246.4v19.4"></path>
                                                    </g>
                                                    <g id="g17762" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path17764" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1275.5v19.4"></path>
                                                        <path id="path17766" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1304.6v19.4"></path>
                                                    </g>
                                                    <g id="g17768" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path17770" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1275.5v19.4"></path>
                                                        <path id="path17772" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1304.6v19.4"></path>
                                                    </g>
                                                    <g id="g17774" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path17776" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1275.5v19.4"></path>
                                                        <path id="path17778" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1340.3,1304.6v19.4"></path>
                                                    </g>
                                                    <g id="g17780" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path17782" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1333.7v19.4"></path>
                                                        <path id="path17784" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1362.8v19.4"></path>
                                                    </g>
                                                    <g id="g17786" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path17788" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1333.7v19.4"></path>
                                                        <path id="path17790" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1362.8v19.4"></path>
                                                    </g>
                                                    <g id="g17792" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path17794" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1333.7v19.4"></path>
                                                        <path id="path17796" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1310.3,1362.8v19.4"></path>
                                                    </g>
                                                    <g id="g17798" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path17800" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1391.9v19.4"></path>
                                                        <path id="path17802" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1421v19.4"></path>
                                                    </g>
                                                    <g id="g17804" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path17806" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1391.9v19.4"></path>
                                                        <path id="path17808" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1421v19.4"></path>
                                                    </g>
                                                    <g id="g17810" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path17812" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1391.9v19.4"></path>
                                                        <path id="path17814" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1280.3,1421v19.4"></path>
                                                    </g>
                                                    <g id="g17816" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path17818" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1450v19.4"></path>
                                                        <path id="path17820" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1479.1v19.4"></path>
                                                    </g>
                                                    <g id="g17822" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path17824" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1450v19.4"></path>
                                                        <path id="path17826" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1479.1v19.4"></path>
                                                    </g>
                                                    <g id="g17828" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path17830" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1450v19.4"></path>
                                                        <path id="path17832" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1250.3,1479.1v19.4"></path>
                                                    </g>
                                                    <g id="g17834" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path17836" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1508.2v19.4"></path>
                                                        <path id="path17838" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1537.3v19.4"></path>
                                                    </g>
                                                    <g id="g17840" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path17842" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1508.2v19.4"></path>
                                                        <path id="path17844" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1537.3v19.4"></path>
                                                    </g>
                                                    <g id="g17846" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path17848" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1508.2v19.4"></path>
                                                        <path id="path17850" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1220.3,1537.3v19.4"></path>
                                                    </g>
                                                    <g id="g17852" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path17854" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1566.4v19.4"></path>
                                                        <path id="path17856" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1595.5v19.4"></path>
                                                    </g>
                                                    <g id="g17858" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path17860" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1566.4v19.4"></path>
                                                        <path id="path17862" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1595.5v19.4"></path>
                                                    </g>
                                                    <g id="g17864" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path17866" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1566.4v19.4"></path>
                                                        <path id="path17868" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1190.3,1595.5v19.4"></path>
                                                    </g>
                                                    <g id="g17870" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path17872" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1624.6v19.4"></path>
                                                        <path id="path17874" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1653.7v19.4"></path>
                                                    </g>
                                                    <g id="g17876" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path17878" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1624.6v19.4"></path>
                                                        <path id="path17880" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1653.7v19.4"></path>
                                                    </g>
                                                    <g id="g17882" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path17884" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1624.6v19.4"></path>
                                                        <path id="path17886" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1160.3,1653.7v19.4"></path>
                                                    </g>
                                                    <g id="g17888" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path17890" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1682.8v19.4"></path>
                                                        <path id="path17892" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1711.9v19.4"></path>
                                                    </g>
                                                    <g id="g17894" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path17896" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1682.8v19.4"></path>
                                                        <path id="path17898" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1711.9v19.4"></path>
                                                    </g>
                                                    <g id="g17900" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path17902" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1682.8v19.4"></path>
                                                        <path id="path17904" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1130.3,1711.9v19.4"></path>
                                                    </g>
                                                    <g id="g17906" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path17908" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1741v19.4"></path>
                                                        <path id="path17910" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1770.1v19.4"></path>
                                                    </g>
                                                    <g id="g17912" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path17914" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1741v19.4"></path>
                                                        <path id="path17916" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1770.1v19.4"></path>
                                                    </g>
                                                    <g id="g17918" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path17920" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1741v19.4"></path>
                                                        <path id="path17922" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1100.3,1770.1v19.4"></path>
                                                    </g>
                                                    <g id="g17924" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path17926" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1799.2v19.4"></path>
                                                        <path id="path17928" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1828.3l0,19.4"></path>
                                                    </g>
                                                    <g id="g17930" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path17932" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1799.2v19.4"></path>
                                                        <path id="path17934" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1828.3l0,19.4"></path>
                                                    </g>
                                                    <g id="g17936" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path17938" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1799.2v19.4"></path>
                                                        <path id="path17940" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1070.3,1828.3l0,19.4"></path>
                                                    </g>
                                                    <g id="g17942" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path17944" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1857.4v19.4"></path>
                                                        <path id="path17946" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1886.5v19.4"></path>
                                                    </g>
                                                    <g id="g17948" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path17950" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1857.4v19.4"></path>
                                                        <path id="path17952" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1886.5v19.4"></path>
                                                    </g>
                                                    <g id="g17954" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path17956" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1857.4v19.4"></path>
                                                        <path id="path17958" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1040.3,1886.5v19.4"></path>
                                                    </g>
                                                    <g id="g17960" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path17962" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1915.6v19.4"></path>
                                                        <path id="path17964" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1944.7v19.4"></path>
                                                    </g>
                                                    <g id="g17966" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path17968" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1915.6v19.4"></path>
                                                        <path id="path17970" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1944.7v19.4"></path>
                                                    </g>
                                                    <g id="g17972" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path17974" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1915.6v19.4"></path>
                                                        <path id="path17976" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1010.3,1944.7v19.4"></path>
                                                    </g>
                                                    <g id="g17978" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path17980" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,1973.8v19.4"></path>
                                                        <path id="path17982" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,2002.9l0,19.4"></path>
                                                    </g>
                                                    <g id="g17984" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path17986" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,1973.8v19.4"></path>
                                                        <path id="path17988" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,2002.9l0,19.4"></path>
                                                    </g>
                                                    <g id="g17990" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path17992" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,1973.8v19.4"></path>
                                                        <path id="path17994" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M980.3,2002.9l0,19.4"></path>
                                                    </g>
                                                    <g id="g17996" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path17998" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2032v19.4"></path>
                                                        <path id="path18000" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2061.1v19.4"></path>
                                                    </g>
                                                    <g id="g18002" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path18004" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2032v19.4"></path>
                                                        <path id="path18006" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2061.1v19.4"></path>
                                                    </g>
                                                    <g id="g18008" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path18010" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2032v19.4"></path>
                                                        <path id="path18012" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M950.3,2061.1v19.4"></path>
                                                    </g>
                                                </g>
                                                <path id="path18016" style="fill:none;stroke:#FFFFFF;stroke-width:0.9511;" d="M1100.6,1459.6v13.1"></path>
                                                <path id="path18022" style="fill:none;stroke:#FFFFFF;stroke-width:0.9511;" d="M1100.6,1459.6v13.1"></path>
                                                <path id="path18028" style="fill:none;stroke:#FFFFFF;stroke-width:0.9511;" d="M1100.6,1459.6v13.1"></path>
                                                <path id="path23547" style="fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;stroke:#FFFFFF;" d="M1232.2,1029.5
                                                    c-6.2,0-11.2-1.7-11.2-3.8c0-2.1,5-3.8,11.1-3.8c0,0,0,0,0,0c6.2,0,11.2,1.7,11.2,3.8
                                                    C1243.4,1027.8,1238.4,1029.5,1232.2,1029.5C1232.2,1029.5,1232.2,1029.5,1232.2,1029.5z"></path>
                                                <path id="path3268" style="fill-rule:evenodd;clip-rule:evenodd;stroke:#FFFFFF;" d="M1702.4,643.3c0,41,0,42.5,0,42.5l0,0"></path>
                                                <path id="path4239" style="fill-rule:evenodd;clip-rule:evenodd;stroke:#FFFFFF;" d="M1697.7,1372.8c0,41,0,42.5,0,42.5l0,0"></path>
                                                <path id="path4241" style="fill-rule:evenodd;clip-rule:evenodd;stroke:#FFFFFF;" d="M748.7,636.6c0,41,0,42.5,0,42.5l0,0"></path>
                                                <path id="path4243" style="fill-rule:evenodd;clip-rule:evenodd;stroke:#FFFFFF;" d="M739.8,1367.4c0,41,0,42.5,0,42.5l0,0"></path>
                                                <g id="g8321" transform="matrix(0.9999862,5.2518717e-3,-5.2518717e-3,0.9999862,5.4976105,-121.21082)">
                                                    <g id="g8323" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path8325" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,988.5v19.4"></path>
                                                        <path id="path8327" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,1017.6v19.4"></path>
                                                    </g>
                                                    <g id="g8329" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path8331" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,988.5v19.4"></path>
                                                        <path id="path8333" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,1017.6v19.4"></path>
                                                    </g>
                                                    <g id="g8335" transform="matrix(0.5946236,0,0,0.6761715,160.91015,46.222933)">
                                                        <path id="path8337" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,988.5v19.4"></path>
                                                        <path id="path8339" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M2023.4,1017.6v19.4"></path>
                                                    </g>
                                                    <g id="g8341" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path8343" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1046.7v19.4"></path>
                                                        <path id="path8345" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1075.8l0,19.4"></path>
                                                    </g>
                                                    <g id="g8347" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path8349" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1046.7v19.4"></path>
                                                        <path id="path8351" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1075.8l0,19.4"></path>
                                                    </g>
                                                    <g id="g8353" transform="matrix(0.5946236,0,0,0.6761715,178.74886,46.222933)">
                                                        <path id="path8355" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1046.7v19.4"></path>
                                                        <path id="path8357" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1993.4,1075.8l0,19.4"></path>
                                                    </g>
                                                    <g id="g8359" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path8361" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1104.9v19.4"></path>
                                                        <path id="path8363" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1134v19.4"></path>
                                                    </g>
                                                    <g id="g8365" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path8367" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1104.9v19.4"></path>
                                                        <path id="path8369" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1134v19.4"></path>
                                                    </g>
                                                    <g id="g8371" transform="matrix(0.5946236,0,0,0.6761715,196.58756,46.222933)">
                                                        <path id="path8373" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1104.9v19.4"></path>
                                                        <path id="path8375" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1963.4,1134v19.4"></path>
                                                    </g>
                                                    <g id="g8377" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path8379" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1163.1v19.4"></path>
                                                        <path id="path8381" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1192.2v19.4"></path>
                                                    </g>
                                                    <g id="g8383" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path8385" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1163.1v19.4"></path>
                                                        <path id="path8387" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1192.2v19.4"></path>
                                                    </g>
                                                    <g id="g8389" transform="matrix(0.5946236,0,0,0.6761715,214.42627,46.222933)">
                                                        <path id="path8391" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1163.1v19.4"></path>
                                                        <path id="path8393" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1933.4,1192.2v19.4"></path>
                                                    </g>
                                                    <g id="g8395" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path8397" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1221.3v19.4"></path>
                                                        <path id="path8399" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1250.4v19.4"></path>
                                                    </g>
                                                    <g id="g8401" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path8403" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1221.3v19.4"></path>
                                                        <path id="path8405" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1250.4v19.4"></path>
                                                    </g>
                                                    <g id="g8407" transform="matrix(0.5946236,0,0,0.6761715,232.26498,46.222933)">
                                                        <path id="path8409" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1221.3v19.4"></path>
                                                        <path id="path8411" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1903.4,1250.4v19.4"></path>
                                                    </g>
                                                    <g id="g8413" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path8415" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1279.5v19.4"></path>
                                                        <path id="path8417" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1308.6v19.4"></path>
                                                    </g>
                                                    <g id="g8419" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path8421" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1279.5v19.4"></path>
                                                        <path id="path8423" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1308.6v19.4"></path>
                                                    </g>
                                                    <g id="g8425" transform="matrix(0.5946236,0,0,0.6761715,250.10368,46.222933)">
                                                        <path id="path8427" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1279.5v19.4"></path>
                                                        <path id="path8429" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1873.4,1308.6v19.4"></path>
                                                    </g>
                                                    <g id="g8431" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path8433" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1337.6v19.4"></path>
                                                        <path id="path8435" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1366.7v19.4"></path>
                                                    </g>
                                                    <g id="g8437" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path8439" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1337.6v19.4"></path>
                                                        <path id="path8441" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1366.7v19.4"></path>
                                                    </g>
                                                    <g id="g8443" transform="matrix(0.5946236,0,0,0.6761715,267.9424,46.222933)">
                                                        <path id="path8445" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1337.6v19.4"></path>
                                                        <path id="path8447" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1843.4,1366.7v19.4"></path>
                                                    </g>
                                                    <g id="g8449" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path8451" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1395.8v19.4"></path>
                                                        <path id="path8453" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1424.9v19.4"></path>
                                                    </g>
                                                    <g id="g8455" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path8457" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1395.8v19.4"></path>
                                                        <path id="path8459" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1424.9v19.4"></path>
                                                    </g>
                                                    <g id="g8461" transform="matrix(0.5946236,0,0,0.6761715,285.7811,46.222933)">
                                                        <path id="path8463" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1395.8v19.4"></path>
                                                        <path id="path8465" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1813.4,1424.9v19.4"></path>
                                                    </g>
                                                    <g id="g8467" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path8469" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1454v19.4"></path>
                                                        <path id="path8471" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1483.1v19.4"></path>
                                                    </g>
                                                    <g id="g8473" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path8475" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1454v19.4"></path>
                                                        <path id="path8477" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1483.1v19.4"></path>
                                                    </g>
                                                    <g id="g8479" transform="matrix(0.5946236,0,0,0.6761715,303.61981,46.222933)">
                                                        <path id="path8481" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1454v19.4"></path>
                                                        <path id="path8483" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1783.4,1483.1v19.4"></path>
                                                    </g>
                                                    <g id="g8485" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path8487" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1512.2v19.4"></path>
                                                        <path id="path8489" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1541.3v19.4"></path>
                                                    </g>
                                                    <g id="g8491" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path8493" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1512.2v19.4"></path>
                                                        <path id="path8495" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1541.3v19.4"></path>
                                                    </g>
                                                    <g id="g8497" transform="matrix(0.5946236,0,0,0.6761715,321.45852,46.222933)">
                                                        <path id="path8499" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1512.2v19.4"></path>
                                                        <path id="path8501" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1753.4,1541.3v19.4"></path>
                                                    </g>
                                                    <g id="g8503" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path8505" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1570.4v19.4"></path>
                                                        <path id="path8507" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1599.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8509" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path8511" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1570.4v19.4"></path>
                                                        <path id="path8513" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1599.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8515" transform="matrix(0.5946236,0,0,0.6761715,339.29722,46.222933)">
                                                        <path id="path8517" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1570.4v19.4"></path>
                                                        <path id="path8519" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1723.4,1599.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8521" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path8523" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1628.6v19.4"></path>
                                                        <path id="path8525" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1657.7v19.4"></path>
                                                    </g>
                                                    <g id="g8527" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path8529" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1628.6v19.4"></path>
                                                        <path id="path8531" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1657.7v19.4"></path>
                                                    </g>
                                                    <g id="g8533" transform="matrix(0.5946236,0,0,0.6761715,357.13594,46.222933)">
                                                        <path id="path8535" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1628.6v19.4"></path>
                                                        <path id="path8537" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1693.4,1657.7v19.4"></path>
                                                    </g>
                                                    <g id="g8539" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path8541" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1686.8v19.4"></path>
                                                        <path id="path8543" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1715.9v19.4"></path>
                                                    </g>
                                                    <g id="g8545" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path8547" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1686.8v19.4"></path>
                                                        <path id="path8549" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1715.9v19.4"></path>
                                                    </g>
                                                    <g id="g8551" transform="matrix(0.5946236,0,0,0.6761715,374.97464,46.222933)">
                                                        <path id="path8553" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1686.8v19.4"></path>
                                                        <path id="path8555" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1663.4,1715.9v19.4"></path>
                                                    </g>
                                                    <g id="g8557" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path8559" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1745v19.4"></path>
                                                        <path id="path8561" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1774.1v19.4"></path>
                                                    </g>
                                                    <g id="g8563" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path8565" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1745v19.4"></path>
                                                        <path id="path8567" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1774.1v19.4"></path>
                                                    </g>
                                                    <g id="g8569" transform="matrix(0.5946236,0,0,0.6761715,392.81335,46.222933)">
                                                        <path id="path8571" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1745v19.4"></path>
                                                        <path id="path8573" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1633.4,1774.1v19.4"></path>
                                                    </g>
                                                    <g id="g8575" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path8577" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1803.2v19.4"></path>
                                                        <path id="path8579" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1832.3v19.4"></path>
                                                    </g>
                                                    <g id="g8581" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path8583" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1803.2v19.4"></path>
                                                        <path id="path8585" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1832.3v19.4"></path>
                                                    </g>
                                                    <g id="g8587" transform="matrix(0.5946236,0,0,0.6761715,410.65206,46.222933)">
                                                        <path id="path8589" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1803.2v19.4"></path>
                                                        <path id="path8591" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1603.4,1832.3v19.4"></path>
                                                    </g>
                                                    <g id="g8593" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path8595" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1861.4v19.4"></path>
                                                        <path id="path8597" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1890.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8599" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path8601" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1861.4v19.4"></path>
                                                        <path id="path8603" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1890.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8605" transform="matrix(0.5946236,0,0,0.6761715,428.49076,46.222933)">
                                                        <path id="path8607" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1861.4v19.4"></path>
                                                        <path id="path8609" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1573.4,1890.5l0,19.4"></path>
                                                    </g>
                                                    <g id="g8611" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path8613" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1919.6v19.4"></path>
                                                        <path id="path8615" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1948.7v19.4"></path>
                                                    </g>
                                                    <g id="g8617" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path8619" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1919.6v19.4"></path>
                                                        <path id="path8621" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1948.7v19.4"></path>
                                                    </g>
                                                    <g id="g8623" transform="matrix(0.5946236,0,0,0.6761715,446.32947,46.222933)">
                                                        <path id="path8625" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1919.6v19.4"></path>
                                                        <path id="path8627" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1543.4,1948.7v19.4"></path>
                                                    </g>
                                                    <g id="g8629" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path8631" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,1977.8v19.4"></path>
                                                        <path id="path8633" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,2006.9v19.4"></path>
                                                    </g>
                                                    <g id="g8635" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path8637" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,1977.8v19.4"></path>
                                                        <path id="path8639" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,2006.9v19.4"></path>
                                                    </g>
                                                    <g id="g8641" transform="matrix(0.5946236,0,0,0.6761715,464.16818,46.222933)">
                                                        <path id="path8643" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,1977.8v19.4"></path>
                                                        <path id="path8645" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1513.4,2006.9v19.4"></path>
                                                    </g>
                                                    <g id="g8647" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path8649" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2036v19.4"></path>
                                                        <path id="path8651" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2065.1v19.4"></path>
                                                    </g>
                                                    <g id="g8653" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path8655" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2036v19.4"></path>
                                                        <path id="path8657" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2065.1v19.4"></path>
                                                    </g>
                                                    <g id="g8659" transform="matrix(0.5946236,0,0,0.6761715,482.00688,46.222933)">
                                                        <path id="path8661" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2036v19.4"></path>
                                                        <path id="path8663" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1483.4,2065.1v19.4"></path>
                                                    </g>
                                                    <g id="g8665" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path8667" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2094.2v19.4"></path>
                                                        <path id="path8669" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2123.3v19.4"></path>
                                                    </g>
                                                    <g id="g8671" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path8673" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2094.2v19.4"></path>
                                                        <path id="path8675" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2123.3v19.4"></path>
                                                    </g>
                                                    <g id="g8677" transform="matrix(0.5946236,0,0,0.6761715,499.84559,46.222933)">
                                                        <path id="path8679" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2094.2v19.4"></path>
                                                        <path id="path8681" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1453.4,2123.3v19.4"></path>
                                                    </g>
                                                    <g id="g8683" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path8685" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2152.4v19.4"></path>
                                                        <path id="path8687" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2181.5v19.4"></path>
                                                    </g>
                                                    <g id="g8689" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path8691" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2152.4v19.4"></path>
                                                        <path id="path8693" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2181.5v19.4"></path>
                                                    </g>
                                                    <g id="g8695" transform="matrix(0.5946236,0,0,0.6761715,517.6843,46.222933)">
                                                        <path id="path8697" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2152.4v19.4"></path>
                                                        <path id="path8699" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1423.4,2181.5v19.4"></path>
                                                    </g>
                                                    <g id="g8701" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path8703" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2210.6v19.4"></path>
                                                        <path id="path8705" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2239.7v19.4"></path>
                                                    </g>
                                                    <g id="g8707" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path8709" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2210.6v19.4"></path>
                                                        <path id="path8711" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2239.7v19.4"></path>
                                                    </g>
                                                    <g id="g8713" transform="matrix(0.5946236,0,0,0.6761715,535.52301,46.222933)">
                                                        <path id="path8715" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2210.6v19.4"></path>
                                                        <path id="path8717" style="fill:none;stroke:#FFFFFF;stroke-width:1.2436;" d="M1393.4,2239.7v19.4"></path>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                    <g>
                                        <rect x="854.1" y="241" style="fill:#17438E;" width="159" height="40"></rect>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5508" data-maparea-identifier="maparea-id-5508" data-root-identifier="root-id-2623804">
                                        <path style="" d="M764.1,416.2L853.7,304c2.6-3.3,6.6-5.2,10.8-5.2H996v140.7H622.6c-3,0-5.4-2.4-5.4-5.4v-5.6
                                            c0-3,2.4-5.4,5.4-5.4H750C755.5,423,760.7,420.5,764.1,416.2z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 885.4366 383.6667)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">A</text>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5509" data-maparea-identifier="maparea-id-5509" data-root-identifier="root-id-2623804">
                                        <polygon style="" points="1197.7,298.8 1001.7,298.8 1001.7,439.5 1060.8,439.5 1060.8,427.8 1169.3,427.8 
                                            1169.3,402.8 1197.7,402.8 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1076.9124 383.6667)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">B</text>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5510" data-maparea-identifier="maparea-id-5510" data-root-identifier="root-id-2623804">
                                        <polygon style="" points="1203.7,298.8 1465.8,298.8 1465.8,439.5 1340.5,439.5 1340.5,427.8 1223.3,427.8 
                                            1223.3,402.8 1203.7,402.8 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1310.5886 383.6668)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">C</text>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5511" data-maparea-identifier="maparea-id-5511" data-root-identifier="root-id-2623804">
                                        <rect x="1473.3" y="298.8" style="" width="278.2" height="140.7" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1595.0846 383.4756)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">D</text>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5512" data-maparea-identifier="maparea-id-5512" data-root-identifier="root-id-2623804">
                                        <rect x="1758.3" y="298.8" style="" width="284" height="140.7" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1865.5184 383.4756)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">E</text>
                                    </g>
                                    <g class="svg-map-area price_color_1" id="svg-map-area-id-5513" data-maparea-identifier="maparea-id-5513" data-root-identifier="root-id-2623804">
                                        <path style="" d="M2056,439.5h-3.2V298.8h128.5c5.9,0,9.1,7.1,5.1,11.4L2077.5,430
                                            C2072,436.1,2064.2,439.5,2056,439.5z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 2068.3328 369.1667)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">E1</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5514" data-maparea-identifier="maparea-id-5514" data-root-identifier="root-id-2624040">
                                        <path style="" d="M2082.8,463.4v3.2l140.7,0V338c0-5.9-7.1-9.1-11.4-5.1l-119.7,108.9
                                            C2086.3,447.4,2082.8,455.2,2082.8,463.4z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 2141.0137 439.5002)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">F1</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5515" data-maparea-identifier="maparea-id-5515" data-root-identifier="root-id-2624040">
                                        <polygon style="" points="2223.5,716.8 2223.5,473.2 2082.8,473.2 2082.8,696.3 2141,696.3 2141,716.8 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 2141.1997 594.9724)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">F</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5516" data-maparea-identifier="maparea-id-5516" data-root-identifier="root-id-2624040">
                                        <polygon style="" points="2223.5,722.6 2223.5,1019.8 2082.8,1019.8 2082.8,747.5 2141,747.5 2141,722.6 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 2137.1921 890.4756)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">G</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5517" data-maparea-identifier="maparea-id-5517" data-root-identifier="root-id-2624040">
                                        <polygon style="" points="2223.5,1321.8 2223.5,1027 2082.8,1027 2082.8,1297 2141,1297 2141,1321.8 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 2135.8066 1174.3888)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">H</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5518" data-maparea-identifier="maparea-id-5518" data-root-identifier="root-id-2624040">
                                        <polygon style="" points="2223.5,1328.6 2223.5,1574 2082.8,1574 2082.8,1349.2 2141,1349.2 2141,1328.6 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 2146.4702 1475.3888)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">I</text>
                                    </g>
                                    <g class="svg-map-area price_color_6" id="svg-map-area-id-5519" data-maparea-identifier="maparea-id-5519" data-root-identifier="root-id-2624040">
                                        <path style="" d="M2082.8,1583.7v-3.2l140.7,0v128.5c0,5.9-7.1,9.1-11.4,5.1l-119.7-108.9
                                            C2086.3,1599.7,2082.8,1591.9,2082.8,1583.7z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 2149.0059 1634.722)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">I1</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5520" data-maparea-identifier="maparea-id-5520" data-root-identifier="root-id-2623935">
                                        <path style="" d="M2050,1607.2h-3.2v140.7h128.5c5.9,0,9.1-7.1,5.1-11.4l-108.9-119.7
                                            C2066,1610.6,2058.2,1607.2,2050,1607.2z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 2057.4578 1701.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">J1</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5521" data-maparea-identifier="maparea-id-5521" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="1747.7,1747.8 2039.9,1747.8 2039.9,1607.2 1844.3,1607.2 1844.3,1665.7 1747.7,1665.7 
                                                        " class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1895.8055 1701.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">J</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5522" data-maparea-identifier="maparea-id-5522" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="1794.8,1661.4 1794.8,1608.3 1503.9,1608.3 1503.9,1747.8 1741,1747.8 1741,1661.4 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1600.7977 1701.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">K</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5523" data-maparea-identifier="maparea-id-5523" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="1209,1747.8 1495.9,1747.8 1495.9,1608.3 1236.8,1608.3 1236.8,1665.9 1209,1665.9 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1328.7913 1701.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">L</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5524" data-maparea-identifier="maparea-id-5524" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="1181.1,1608.3 918.4,1608.3 918.4,1747.8 1201.3,1747.8 1201.3,1665.9 1181.1,1665.9 			
                                            " class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 1032.8134 1701.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">M</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5525" data-maparea-identifier="maparea-id-5525" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="619.9,1661 619.9,1607.9 910.8,1607.9 910.8,1747.4 673.7,1747.4 673.7,1661 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 766.8068 1704.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">N</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5526" data-maparea-identifier="maparea-id-5526" data-root-identifier="root-id-2623935">
                                        <polygon style="" points="667.6,1747 375.3,1747 375.3,1606.3 570.9,1606.3 570.9,1664.8 667.6,1664.8 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 476.8042 1704.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">O</text>
                                    </g>
                                    <g class="svg-map-area price_color_5" id="svg-map-area-id-5527" data-maparea-identifier="maparea-id-5527" data-root-identifier="root-id-2623935">
                                        <path style="" d="M365.3,1606.2h3.2v140.7H239.9c-5.9,0-9.1-7.1-5.1-11.4l108.9-119.7
                                            C349.3,1609.6,357.1,1606.2,365.3,1606.2z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 293.7899 1711.9407)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">O1</text>
                                    </g>
                                    <g class="svg-map-area price_color_7 no-ticket" id="svg-map-area-id-5528" data-maparea-identifier="maparea-id-5528" data-root-identifier="root-id-2624119">
                                        <path style="" d="M336.6,1579.7v-3.2l-140.7,0l0,128.5c0,5.9,7.1,9.1,11.4,5.1l119.7-108.9
                                            C333.1,1595.7,336.6,1587.9,336.6,1579.7z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 212.45 1633.7223)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">P1</text>
                                    </g>
                                    <g class="svg-map-area price_color_7 no-ticket" id="svg-map-area-id-5529" data-maparea-identifier="maparea-id-5529" data-root-identifier="root-id-2624119">
                                        <polygon style="" points="281.7,1319.8 195.9,1319.8 195.9,1563.8 336.6,1563.8 336.6,1342.9 281.7,1342.9 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 244.5237 1468.3887)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">P</text>
                                    </g>
                                    <g class="svg-map-area price_color_7 no-ticket" id="svg-map-area-id-5531" data-maparea-identifier="maparea-id-5531" data-root-identifier="root-id-2624119">
                                        <path style="" d="M336.6,1004H195.9v-17.8c0-2.4,0.9-4.6,2.5-6.4l136-150.8c0.8-0.9,2.2-0.3,2.2,0.8V1004z" class="svg-map-area-shape"></path>
                                        <text transform="matrix(1 0 0 1 254.8905 969.8337)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">R</text>
                                    </g>
                                    <g class="svg-map-area price_color_7 no-ticket" id="svg-map-area-id-5530" data-maparea-identifier="maparea-id-5530" data-root-identifier="root-id-2624119">
                                        <polygon style="" points="336.6,1290 336.6,1013 195.9,1013 195.9,1309.3 281.7,1309.3 281.7,1290 			" class="svg-map-area-shape"></polygon>
                                        <text transform="matrix(1 0 0 1 241.8638 1174.3884)" style="font-family:'Arial'; font-size:48px;" class="svg-map-area-name">Q</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5532" data-maparea-identifier="maparea-id-5532" data-root-identifier="root-id-2623887">
                                    <rect x="854.1" y="241" style="" width="159" height="40" class="svg-map-area-shape"></rect>
                                    <text transform="matrix(1 0 0 1 907.8827 268.3333)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 1</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5533" data-maparea-identifier="maparea-id-5533" data-root-identifier="root-id-2623887">
                                        <rect x="1018.1" y="241" style="" width="103.1" height="40" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1046.8827 268.3332)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 2</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5534" data-maparea-identifier="maparea-id-5534" data-root-identifier="root-id-2623887">
                                        <rect x="1126.1" y="241" style="" width="111.3" height="40" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1156.0876 268.333)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 3</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5535" data-maparea-identifier="maparea-id-5535" data-root-identifier="root-id-2623887">
                                        <rect x="1242.4" y="241" style="" width="224.8" height="40" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1329.1218 268.3332)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 4</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5536" data-maparea-identifier="maparea-id-5536" data-root-identifier="root-id-2623887">
                                        <rect x="1472.2" y="241" style="" width="227.9" height="40" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1560.4751 268.3332)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 5</text>
                                    </g>
                                    <g class="svg-map-area price_color_2" id="svg-map-area-id-5537" data-maparea-identifier="maparea-id-5537" data-root-identifier="root-id-2623887">
                                        <rect x="1705.1" y="241" style="" width="176.4" height="40" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1767.64 268.3335)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">VIP 6</text>
                                    </g>
                                    <g class="svg-map-area price_color_3 no-ticket" id="svg-map-area-id-5505" data-maparea-identifier="maparea-id-5505" data-root-identifier="root-id-2623912">
                                        <rect x="1191.9" y="201.4" style="" width="93" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1205.5031 223.1672)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">LOJA 1</text>
                                    </g>
                                    <g class="svg-map-area price_color_3 no-ticket" id="svg-map-area-id-5506" data-maparea-identifier="maparea-id-5506" data-root-identifier="root-id-2623912">
                                        <rect x="1424.8" y="201.4" style="" width="93" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1438.3162 223.1668)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">LOJA 2</text>
                                    </g>
                                    <g class="svg-map-area price_color_3 no-ticket" id="svg-map-area-id-5507" data-maparea-identifier="maparea-id-5507" data-root-identifier="root-id-2623912">
                                        <rect x="1654.6" y="201.4" style="" width="93" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1666.0985 223.167)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">LOJA 3</text>
                                    </g>
                                    <g class="svg-map-area price_color_4 no-ticket" id="svg-map-area-id-5538" data-maparea-identifier="maparea-id-5538" data-root-identifier="root-id-2623919">
                                        <rect x="862.9" y="161.9" style="" width="230" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 933.5665 183.8889)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">PRESA 1</text>
                                    </g>
                                    <g class="svg-map-area price_color_4 no-ticket" id="svg-map-area-id-5539" data-maparea-identifier="maparea-id-5539" data-root-identifier="root-id-2623919">
                                        <rect x="1097.9" y="161.9" style="" width="230" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1168.5668 183.8891)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">PRESA 2</text>
                                    </g>
                                    <g class="svg-map-area price_color_4 no-ticket" id="svg-map-area-id-5540" data-maparea-identifier="maparea-id-5540" data-root-identifier="root-id-2623919">
                                        <rect x="1332.9" y="161.9" style="" width="230" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1399.5673 183.8891)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">PRESA 3</text>
                                    </g>
                                    <g class="svg-map-area price_color_4 no-ticket" id="svg-map-area-id-5541" data-maparea-identifier="maparea-id-5541" data-root-identifier="root-id-2623919">
                                        <rect x="1567.9" y="161.9" style="" width="230" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1638.5665 183.8891)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">PRESA 4</text>
                                    </g>
                                    <g class="svg-map-area price_color_4 no-ticket" id="svg-map-area-id-5542" data-maparea-identifier="maparea-id-5542" data-root-identifier="root-id-2623919">
                                        <rect x="1802.9" y="161.9" style="" width="210" height="30" class="svg-map-area-shape"></rect>
                                        <text transform="matrix(1 0 0 1 1863.5665 183.8891)" style="font-family:'Arial'; font-size:21px;" class="svg-map-area-name">PRESA 5</text>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="panel-list-group">
                    <div class="list-group root-legend hidden-xs hidden-sm">
                        <a href="#" class="list-group-item root-legend-item sectormap-root-clicker clearfix" data-root-identifier="root-id-2623804">
                            <h6 class="pull-left margin-0">
                                <span class="bullet-price-color price_color_1">&nbsp;</span>
                                <span>Tribuna I</span>
                            </h6>
                            <span class="pull-right">
                                <i class="icon-left fa fa-chevron-right text-larger" aria-hidden="true"></i>
                            </span>
                            <span class="pull-right">preț bilet 50,00 RON</span>
                        </a>
                        <a href="#" class="list-group-item root-legend-item sectormap-root-clicker clearfix" data-root-identifier="root-id-2623887">
                            <h6 class="pull-left margin-0">
                                <span class="bullet-price-color price_color_2">&nbsp;</span>
                                <span>Tribuna I VIP</span>
                            </h6>
                            <span class="pull-right">
                                <i class="icon-left fa fa-chevron-right text-larger" aria-hidden="true"></i>
                            </span>
                            <span class="pull-right">preț bilet 100,00 RON</span>
                        </a>
                        <a href="#" class="list-group-item root-legend-item sectormap-root-clicker clearfix" data-root-identifier="root-id-2623935">
                            <h6 class="pull-left margin-0">
                                <span class="bullet-price-color price_color_5">&nbsp;</span>
                                <span>Tribuna II</span>
                            </h6>
                            <span class="pull-right">
                                <i class="icon-left fa fa-chevron-right text-larger" aria-hidden="true"></i>
                            </span>
                            <span class="pull-right">preț bilet 50,00 RON</span>		
                        </a>
                        <a href="#" class="list-group-item root-legend-item sectormap-root-clicker clearfix" data-root-identifier="root-id-2624040">
                            <h6 class="pull-left margin-0">
                                <span class="bullet-price-color price_color_6">&nbsp;</span>
                                <span>Peluza Nord - PCH</span>
                            </h6>
                            <span class="pull-right">
                                <i class="icon-left fa fa-chevron-right text-larger" aria-hidden="true"></i>
                            </span>
                            <span class="pull-right">preț bilet 25,00 RON</span>
                        </a>
                    </div>
                </div>    
            </div>

            <div class="col-sm-4 mt-5" style="height: 80px;">
                <h6 class="m-0" style="opacity: 0.6; font-weight: bold; width: 260px;">
                    <i style="min-width: 40px; font-size: 40px; vertical-align: middle; margin-bottom: 5px; margin-right: 10px;" class="fa fa-credit-card"></i>
                    Plata online cu cardul
                </h6>
                <p class="text-hint text-help" style="font-weight: normal; line-weight: 2.2em; color: #aaa;">poți plăti cu cardul online și primești biletul în format digital</p>
            </div>
        </div>

        <div class="col-md-5 col-xs-12 row-even-spacing" style="margin-right: auto;">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="hidden-xs">Alege locurile</h4>
                    <form class="form-inline">
                        <div class="form-group">
                            <label for="root_selector">Categoria de preț</label>
                            <select id="root_selector" class="form-control sectormap-root-selector">
                                <option>afișează toate</option>
                                <option value="root-id-2623804">Tribuna I (50,00 RON)</option>
                                <option value="root-id-2623887">Tribuna I VIP (100,00 RON)</option>
                                <option value="root-id-2623935">Tribuna II (50,00 RON)</option>
                                <option value="root-id-2624040">Peluza Nord - PCH (25,00 RON)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="panel-list-group">
                    <div class="list-group ticket-offer-list">
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5508">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector A']) ? $disponibilitati['Sector A'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector A</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector A">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5509">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector B']) ? $disponibilitati['Sector B'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector B</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector B">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5510">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector C']) ? $disponibilitati['Sector C'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector C</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector C">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5511">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector D']) ? $disponibilitati['Sector D'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector D</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector D">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5512">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector E']) ? $disponibilitati['Sector E'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector E</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector E">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623804 struct-id-2623804 maparea-id-5513">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector E1']) ? $disponibilitati['Sector E1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna I Vest, Sector E1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector E1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5532">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 1']) ? $disponibilitati['Sector VIP 1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5533">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 2']) ? $disponibilitati['Sector VIP 2'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 2</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 2">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5534">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 3']) ? $disponibilitati['Sector VIP 3'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 3</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 3">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5535">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 4']) ? $disponibilitati['Sector VIP 4'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 4</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 4">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5536">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 5']) ? $disponibilitati['Sector VIP 5'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 5</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 5">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623887 struct-id-2623887 maparea-id-5537">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA I VIP</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector VIP 6']) ? $disponibilitati['Sector VIP 6'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 100,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>VIP 6</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector VIP 6">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5520">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector J1']) ? $disponibilitati['Sector J1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector J1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector J1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5521">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector J']) ? $disponibilitati['Sector J'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector J</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector J">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5522">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector K']) ? $disponibilitati['Sector K'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector K</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector K">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5523">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector L']) ? $disponibilitati['Sector L'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector L</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector L">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5524">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector M']) ? $disponibilitati['Sector M'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector M</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector M">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5525">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector N']) ? $disponibilitati['Sector N'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector N</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector N">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5526">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector O']) ? $disponibilitati['Sector O'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector O</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector O">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2623935 struct-id-2623935 maparea-id-5527">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>TRIBUNA II</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector O1']) ? $disponibilitati['Sector O1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 50,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Tribuna II Est, Sector O1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector O1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5514">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector F1']) ? $disponibilitati['Sector F1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector F1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector F1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5515">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector F']) ? $disponibilitati['Sector F'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector F</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector F">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5516">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector G']) ? $disponibilitati['Sector G'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector G</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector G">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5517">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector H']) ? $disponibilitati['Sector H'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector H</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector H">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5518">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector I']) ? $disponibilitati['Sector I'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector I</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector I">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item ticket-offer-item root-id-2624040 struct-id-2624040 maparea-id-5519">
                            <div class="row row-even-spacing">
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing">
                                    <strong>PELUZA NORD - PCH</strong>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right hidden-xs">
                                    <small><?php echo isset($disponibilitati['Sector I1']) ? $disponibilitati['Sector I1'] : '0'; ?> bilete disponibile</small>
                                </div>
                                <div class="col-md-4 col-sm-6 col-xs-4 col-even-spacing text-right">
                                    <label> 25,00 RON </label>
                                </div>
                                <div class="col-sm-8 col-xs-12 col-even-spacing">
                                    <small>Peluza Nord, Sector I1</small>
                                </div>
                                <div class="col-sm-4 col-xs-12 col-even-spacing text-right">
                                    <a href="javascript:void(0);" class="btn custom-btn btn-xs open-popup" data-sector="Sector I1">Alege locurile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>

    <!-- Pop-up modal -->
    <div id="popupModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="mt-4 ml-5 mb-3">Selectează locurile</h2>
            <div class="legend text-right">
                <span class="legend-item"><span class="dot unavailable"></span> Indisponibil</span>
                <span class="legend-item"><span class="dot occupied"></span> Ocupat</span>
                <span class="legend-item"><span class="dot available"></span> Disponibil</span>
            </div>
            <div class="seats-container mt-3 ml-5">
            <!-- Aici vor fi generate locurile -->
            </div>
            <div class="field">
                <div class="field-label">Teren</div>
                <div class="field-drawing">
                    <!-- Reprezentarea grafică a terenului -->
                </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn custom-btn-order">COMANDĂ</button>
            </div>
        </div>
    </div>

    <footer class="footer-custom">
        <div class="footer-container">
            <div class="row" style="margin-right: 0px;">
                <div class="col-md-5 footer-left">
                    <a href="../Legal/politica_confidentialitate.html" target="_blank">Politica de confidențialitate</a> | 
                    <a href="../Legal/contact.html" target="_blank">Contact</a>
                    <p class="p-copyrights">Dinamo 1948 București <i class="far fa-copyright"></i> 2024. Toate drepturile sunt rezervate.</p>
                </div>
                <div class="col-md-5 footer-right">
                    <a href="https://ec.europa.eu/consumers/odr" target="_blank" style="margin-right: 0px; margin-top: 20px;">
                        <img src="/Imagini/solutionare_online_litigii.png" alt="Solutionarea online a litigiilor" height="50" class="first-image">
                    </a>
                    <a href="https://anpc.ro/ce-este-sal/" target="_blank">
                        <img src="/Imagini/solutionare_alternativa_litigii.png" alt="Solutionarea alternativa a litigiilor" height="50" class="second-image">
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.navbar-toggler').click(function () {
                // Schimbă clasa iconiței la click
                var icon = $(this).find('i'); // Găsește elementul <i> din interiorul butonului
                if (icon.hasClass('fa-bars')) {
                    icon.removeClass('fa-bars').addClass('fa-times'); // Schimbă în X
                } else {
                    icon.removeClass('fa-times').addClass('fa-bars'); // Schimbă înapoi în hamburger
                }
            });
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var svgContainer = document.querySelector('.svg-map-parent');
            var zoomInButton = document.querySelector('.svg-map-zoom-in');
            var zoomOutButton = document.querySelector('.svg-map-zoom-out');
            var maxScale = 5; // Maximul de zoom-in
            var minScale = 1; // Minimul de zoom-out
            var scale = 1;
            var pan = { x: 0, y: 0 };
            var origin = { x: 0, y: 0 };
            var dragging = false;

            function transform() {
                window.requestAnimationFrame(() => {
                    svgContainer.style.transform = `translate(${pan.x}px, ${pan.y}px) scale(${scale})`;
                });
            }

            zoomInButton.addEventListener('click', function() {
                if (scale < maxScale) {
                    scale *= 1.5;
                    if (scale > maxScale) {
                        scale = maxScale;
                    }
                    transform();
                }
            });

            zoomOutButton.addEventListener('click', function() {
                if (scale > minScale) {
                    scale *= 0.75;
                    if (scale < minScale) {
                        scale = minScale;
                    }
                    pan.x = pan.y = 0; // Centrarea hărții
                    transform();
                }
            });

            svgContainer.addEventListener('mousedown', function(e) {
                if (scale > minScale) {
                    origin.x = e.clientX - pan.x;
                    origin.y = e.clientY - pan.y;
                    dragging = true;
                }
            });

            document.addEventListener('mousemove', function(e) {
                if (dragging && scale > minScale) {
                    pan.x = e.clientX - origin.x;
                    pan.y = e.clientY - origin.y;
                    transform();
                }
            });

            document.addEventListener('mouseup', function() {
                dragging = false;
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const sectorToZoneMapping = {
                'maparea-id-5508': 'root-id-2623804', // Tribuna I pentru Sectorul A
                'maparea-id-5509': 'root-id-2623804', // Tribuna I pentru Sectorul B
                'maparea-id-5510': 'root-id-2623804', // Tribuna I pentru Sectorul C
                'maparea-id-5511': 'root-id-2623804', // Tribuna I pentru Sectorul D
                'maparea-id-5512': 'root-id-2623804', // Tribuna I pentru Sectorul E
                'maparea-id-5513': 'root-id-2623804', // Tribuna I pentru Sectorul E1
                'maparea-id-5532': 'root-id-2623887', // Tribuna I VIP pentru VIP 1
                'maparea-id-5533': 'root-id-2623887', // Tribuna I VIP pentru VIP 2
                'maparea-id-5534': 'root-id-2623887', // Tribuna I VIP pentru VIP 3
                'maparea-id-5535': 'root-id-2623887', // Tribuna I VIP pentru VIP 4
                'maparea-id-5536': 'root-id-2623887', // Tribuna I VIP pentru VIP 5
                'maparea-id-5537': 'root-id-2623887', // Tribuna I VIP pentru VIP 6
                'maparea-id-5520': 'root-id-2623935', // Tribuna II pentru Sectorul J1
                'maparea-id-5521': 'root-id-2623935', // Tribuna II pentru Sectorul J
                'maparea-id-5522': 'root-id-2623935', // Tribuna II pentru Sectorul K
                'maparea-id-5523': 'root-id-2623935', // Tribuna II pentru Sectorul L
                'maparea-id-5524': 'root-id-2623935', // Tribuna II pentru Sectorul M
                'maparea-id-5525': 'root-id-2623935', // Tribuna II pentru Sectorul N
                'maparea-id-5526': 'root-id-2623935', // Tribuna II pentru Sectorul O
                'maparea-id-5527': 'root-id-2623935', // Tribuna II pentru Sectorul O1
                'maparea-id-5514': 'root-id-2624040', // Peluza Nord pentru Sectorul F1
                'maparea-id-5515': 'root-id-2624040', // Peluza Nord pentru Sectorul F
                'maparea-id-5516': 'root-id-2624040', // Peluza Nord pentru Sectorul G
                'maparea-id-5517': 'root-id-2624040', // Peluza Nord pentru Sectorul H
                'maparea-id-5518': 'root-id-2624040', // Peluza Nord pentru Sectorul I
                'maparea-id-5519': 'root-id-2624040', // Peluza Nord pentru Sectorul I1
            };

            $('#root_selector').change(function() {
                var selected = $(this).val();
                handleSelectionChange(selected);
            });

            $('.sectormap-root-clicker').click(function(e) {
                e.preventDefault();
                var rootId = $(this).data('root-identifier');
                $('#root_selector').val(rootId).change();
                $('.sectormap-root-clicker').removeClass('active');
                $(this).addClass('active');
            });

            $('[data-maparea-identifier]').click(function() {
                if ($(this).hasClass('no-ticket')) {
                    return;
                }
                var mapAreaId = $(this).data('maparea-identifier');
                var zoneId = sectorToZoneMapping[mapAreaId];
                $('#root_selector').val(zoneId).change();
                handleMapAreaClick(mapAreaId);
            });

            function handleSelectionChange(selected) {
                $('.ticket-offer-item').hide();
                $('.sectormap-root-clicker').removeClass('active'); // Linie adăugată pentru a înlătura clasa 'active'
                if (selected === 'afișează toate') {
                    $('.ticket-offer-item').show();
                    $('[data-maparea-identifier]').removeClass('greyed-out');
                } else {
                    $('.ticket-offer-item.' + selected).show();
                    highlightMapAreaByZone(selected);
                }
            }

            function handleMapAreaClick(mapAreaId) {
                var zoneId = sectorToZoneMapping[mapAreaId];
                $('#root_selector').val(zoneId).change();
                $('.ticket-offer-item').hide();
                $('.ticket-offer-item.' + mapAreaId).show();
                highlightMapAreaByZone(mapAreaId);
            }

            function highlightMapAreaByZone(zoneId) {
                $('[data-maparea-identifier]').addClass('greyed-out');
                if (zoneId === 'afișează toate') {
                    $('[data-maparea-identifier]').removeClass('greyed-out');
                } else {
                    if (sectorToZoneMapping.hasOwnProperty(zoneId)) {
                        $('[data-maparea-identifier="' + zoneId + '"]').removeClass('greyed-out');
                    } else {
                        $('[data-root-identifier="' + zoneId + '"]').removeClass('greyed-out');
                    }
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Deschide pop-up-ul când se apasă pe butonul "ALEGE LOCURILE"
            $(".open-popup").on("click", function() {
                $("#popupModal").css("display", "flex");
            });

            // Închide pop-up-ul când se apasă pe X
            $(".close").on("click", function() {
                $("#popupModal").css("display", "none");
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Funcție pentru a obține datele locurilor din baza de date
            function fetchSeatsData(sector) {
                console.log("Fetching data for sector: " + sector); // Verifică sectorul trimis
                return $.ajax({
                    url: 'fetch_seats.php', // Scriptul PHP care returnează datele locurilor
                    method: 'GET',
                    data: { sector: sector }, // Trimite sectorul selectat ca parametru
                    dataType: 'json'
                }).done(function(data) {
                    console.log("Data received: ", data); // Verifică răspunsul de la server
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Error fetching data: ", textStatus, errorThrown); // Verifică dacă există erori
                });
            }

            // Funcție pentru a genera locurile cu un parametru suplimentar pentru aliniere
            function generateSeats(container, locuri, alignment) {
                let previousMargin = 0; // Inițial, nu avem margin-left

                // Grupăm locurile după rânduri
                const groupedByRow = locuri.reduce((acc, loc) => {
                    if (!acc[loc.Rand]) {
                        acc[loc.Rand] = [];
                    }
                    acc[loc.Rand].push(loc);
                    return acc;
                }, {});

                // Sortăm rândurile
                const sortedRows = Object.keys(groupedByRow).sort((a, b) => b - a);

                sortedRows.forEach((rowNumber, index) => {
                    const rowData = groupedByRow[rowNumber];
                    const row = $('<div class="seat-row"></div>');
                    const rowNumberElement = $('<div class="row-number">' + rowNumber + '</div>');
                    row.append(rowNumberElement);

                    rowData.forEach(loc => {
                        const seatClass = loc.Disponibilitate === 'Disponibil' ? 'available' :
                                        loc.Disponibilitate === 'Ocupat' ? 'occupied' : 'unavailable';
                        const seat = $('<span class="dot seat-dot ' + seatClass + '">' + loc.Loc + '</span>');
                        seat.attr('data-detalii-loc-id', loc.DetaliiLocID); // Adaugă un atribut pentru ID-ul locului
                        seat.attr('data-categorie', loc.Nume_Categorie); // Adaugă un atribut pentru categoria locului
                        seat.attr('data-pret', loc.Pret); // Adaugă un atribut pentru prețul locului
                        row.append(seat);
                    });

                    // Calcularea margin-left pentru aliniere corectă
                    let marginLeft = 0;
                    if (alignment === 'sectorA') {
                        // Aliniere specifică pentru sectorul A
                        if (index === 10) {
                            marginLeft = 10 * 26;
                        } else if (index === 9) {
                            marginLeft = 10 * 26 + 26; 
                        } else if (index === 8) {
                            marginLeft = 10 * 26 + 2 * 26;
                        } else if (index === 7) {
                            marginLeft = 10 * 26 + 3 * 26;
                        } else if (index === 6) {
                            marginLeft = 10 * 26 + 4 * 26;
                        } else if (index === 5) {
                            marginLeft = 10 * 26 + 5 * 26;
                        } else if (index === 4) {
                            marginLeft = 10 * 26 + 6 * 26;
                        } else if (index === 3) {
                            marginLeft = 10 * 26 + 7 * 26;
                        } else if (index === 2) {
                            marginLeft = 10 * 26 + 8 * 26 - 12;
                        } else if (index === 1) {
                            marginLeft = 10 * 26 + 9 * 26 - 12;
                        } else if (index === 0) {
                            marginLeft = 10 * 26 + 10 * 26 - 12;
                        }
                    } else if (alignment === 'sectorB') {
                        if (index > 2) {
                            marginLeft = 0;    
                        } else if (index === 2 || index === 1 || index === 0) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorC') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        } else if (index === 9 || index === 10) {
                            marginLeft = 78;
                        } else if (index === 11) {
                            marginLeft = 443;
                        }
                    } else if (alignment === 'sectorD') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorE') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorE1') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorVIP2') {
                        if (index === 0) {
                            marginLeft = 260;
                        }
                    } else if (alignment === 'sectorVIP5') {
                        if (index === 0) {
                            marginLeft = 597;
                        }
                    } else if (alignment === 'sectorJ1') {
                        if (index === 1) {
                            marginLeft = 26;
                        } else if (index === 2) {
                            marginLeft = 2 * 26;
                        } else if (index === 3) {
                            marginLeft = 3 * 26 + 11;
                        } else if (index === 4) {
                            marginLeft = 4 * 26 + 11;
                        } else if (index === 5) {
                            marginLeft = 5 * 26 + 11;
                        } else if (index === 6) {
                            marginLeft = 6 * 26 + 11;
                        } else if (index === 7) {
                            marginLeft = 7 * 26 + 11;
                        } else if (index === 8) {
                            marginLeft = 8 * 26 + 11;
                        } else if (index === 9) {
                            marginLeft = 9 * 26 + 11;
                        } else if (index === 10) {
                            marginLeft = 10 * 26 + 11;
                        } else if (index === 11) {
                            marginLeft = 11 * 26 + 11;
                        }
                    } else if (alignment === 'sectorJ') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorK') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = 249;
                        } else if (index === 3 || index === 4 || index === 5 || index === 6) {
                            marginLeft = 10 * 26;
                        }
                    } else if (alignment === 'sectorL') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorM') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        } else if (index === 7 || index === 8 || index === 9 || index === 10 || index === 11) {
                            marginLeft = 78;
                        }
                    } else if (alignment === 'sectorN') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorO') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        } else if (index === 7 || index === 8 || index === 9 || index === 10 || index === 11) {
                            marginLeft = 15 * 26;
                        }
                    } else if (alignment === 'sectorO1') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorF1') {
                        if (index === 1) {
                            marginLeft = 26;
                        } else if (index === 2) {
                            marginLeft = 2 * 26;
                        } else if (index === 3) {
                            marginLeft = 3 * 26 + 11;
                        } else if (index === 4) {
                            marginLeft = 4 * 26 + 11;
                        } else if (index === 5) {
                            marginLeft = 5 * 26 + 11;
                        } else if (index === 6) {
                            marginLeft = 6 * 26 + 11;
                        } else if (index === 7) {
                            marginLeft = 7 * 26 + 11;
                        } else if (index === 8) {
                            marginLeft = 8 * 26 + 11;
                        } else if (index === 9) {
                            marginLeft = 9 * 26 + 11;
                        } else if (index === 10) {
                            marginLeft = 10 * 26 + 11;
                        } else if (index === 11) {
                            marginLeft = 11 * 26 + 11;
                        }
                    } else if (alignment === 'sectorF') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorG') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        } else if (index === 7 || index === 8 || index === 9 || index === 10 || index === 11) {
                            marginLeft = 5 * 26;
                        }
                    } else if (alignment === 'sectorH') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    } else if (alignment === 'sectorI') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        } else if (index === 7 || index === 8 || index === 9 || index === 10 || index === 11) {
                            marginLeft = 3 * 26;
                        }
                    } else if (alignment === 'sectorI1') {
                        if (index === 0 || index === 1 || index === 2) {
                            marginLeft = marginLeft - 23;
                        }
                    }

                    previousMargin = marginLeft; // Actualizează margin-left pentru următorul rând
                    row.css('margin-left', marginLeft + 'px'); // Aplică margin-left calculat
                    container.append(row);
                });

                // Adaugă evenimentul de click pentru a gestiona selecția locurilor
                $('.seat-dot').on('click', function() {
                    const selectedSeats = $('.seat-dot.selected').length;

                    // Verifică dacă numărul maxim de selecții este atins
                    if (!$(this).hasClass('selected') && selectedSeats >= 8) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Limită atinsă',
                            text: 'Puteți selecta până la maximum 8 locuri.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    $(this).toggleClass('selected');
                });
            }

            // Populează containerul cu locuri pentru un sector
            function loadSeatsForSector(sector) {
                fetchSeatsData(sector).done(function(locuri) {
                    if (!locuri || locuri.length === 0) {
                        console.log("No seats data received for sector: " + sector);
                        return;
                    }
                    const seatsContainer = $('.seats-container');
                    seatsContainer.empty(); // Golește containerul înainte de a adăuga locuri noi

                    // Determină alinierea în funcție de sector
                    let alignment = 'default';
                    if (sector === 'Sector A') {
                        alignment = 'sectorA';
                    } else if (sector === 'Sector B') {
                        alignment = 'sectorB';
                    } else if (sector === 'Sector C') {
                        alignment = 'sectorC';
                    } else if (sector === 'Sector D') {
                        alignment = 'sectorD';
                    } else if (sector === 'Sector E') {
                        alignment = 'sectorE';
                    } else if (sector === 'Sector E1') {
                        alignment = 'sectorE1';
                    } else if (sector === 'Sector VIP 2') {
                        alignment = 'sectorVIP2';
                    } else if (sector === 'Sector VIP 5') {
                        alignment = 'sectorVIP5';
                    } else if (sector === 'Sector J1') {
                        alignment = 'sectorJ1';
                    } else if (sector === 'Sector J') {
                        alignment = 'sectorJ';
                    } else if (sector === 'Sector K') {
                        alignment = 'sectorK';
                    } else if (sector === 'Sector L') {
                        alignment = 'sectorL';
                    } else if (sector === 'Sector M') {
                        alignment = 'sectorM';
                    } else if (sector === 'Sector N') {
                        alignment = 'sectorN';
                    } else if (sector === 'Sector O') {
                        alignment = 'sectorO';
                    } else if (sector === 'Sector O1') {
                        alignment = 'sectorO1';
                    } else if (sector === 'Sector F1') {
                        alignment = 'sectorF1';
                    } else if (sector === 'Sector F') {
                        alignment = 'sectorF';
                    } else if (sector === 'Sector G') {
                        alignment = 'sectorG';
                    } else if (sector === 'Sector H') {
                        alignment = 'sectorH';
                    } else if (sector === 'Sector I') {
                        alignment = 'sectorI';
                    } else if (sector === 'Sector I1') {
                        alignment = 'sectorI1';
                    }
                
                    generateSeats(seatsContainer, locuri, alignment);
                });
            }

            // Deschide pop-up-ul când se apasă pe butonul "ALEGE LOCURILE"
            $(".open-popup").on("click", function() {
                const sector = $(this).data('sector'); // Obține sectorul din atributul data-sector
                console.log("Sector selected: " + sector); // Log pentru a verifica sectorul selectat
                $("#popupModal").css("display", "flex");

                // Resetează selecția locurilor
                $('.seat-dot').removeClass('selected');

                // Încarcă locurile pentru sectorul selectat
                loadSeatsForSector(sector);
            });

            // Închide pop-up-ul când se apasă pe X
            $(".close").on("click", function() {
                $("#popupModal").css("display", "none");
            });

            // Verifică dacă sunt locuri selectate la click pe butonul "COMANDĂ"
            $(".custom-btn-order").on("click", function() {
                const selectedSeatsCounter = $('.seat-dot.selected').length;
                const selectedSeats = $('.seat-dot.selected');
                const selectedSeatsIds = selectedSeats.map(function() {
                    return $(this).data('detalii-loc-id');
                }).get();

                if (selectedSeatsCounter === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Niciun loc selectat',
                        text: 'Trebuie selectat cel puțin un loc.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Trimite locurile selectate la server pentru a fi stocate în sesiune
                $.ajax({
                    url: 'set_selected_seats.php',
                    method: 'POST',
                    data: { seats: selectedSeatsIds },
                    success: function(response) {
                        // Redirecționează către pagina de comanda
                        window.location.href = '../Comanda/comanda.php';
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error saving selected seats: ", textStatus, errorThrown);
                    }
                });
            });
        });
    </script>

</body>
</html>