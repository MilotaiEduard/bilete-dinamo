<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

// Verifică rolul utilizatorului și redirecționează adminii către dashboard
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
    header('Location: ../Dashboard/acasa.php');
    exit();
}

// Verifică dacă locurile selectate sunt setate în sesiune
if (!isset($_SESSION['selected_seats']) || empty($_SESSION['selected_seats'])) {
    header('Location: ../MeniuPrincipal/meniu_principal.php');
    exit();
}

include '../db_connect.php';

// Obține datele de facturare pentru utilizatorul autentificat
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM DateFacturare WHERE UtilizatorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$contactNume = htmlspecialchars($data['PersoanaContact_Nume']);
$contactPrenume = htmlspecialchars($data['PersoanaContact_Prenume']);
$contactTelefon = htmlspecialchars($data['Telefon']);
$adresaStrada = htmlspecialchars($data['Adresa_Strada']);
$adresaNumar = htmlspecialchars($data['Adresa_Numar']);
$adresaBloc = htmlspecialchars($data['Adresa_Bloc']);
$adresaScara = htmlspecialchars($data['Adresa_Scara']);
$adresaEtaj = htmlspecialchars($data['Adresa_Etaj']);
$adresaApartament = htmlspecialchars($data['Adresa_Apartament']);
$adresaJudet = htmlspecialchars($data['Judet']);
$adresaLocalitate = htmlspecialchars($data['Localitate']);

$stmt->close();

// Afișează locurile selectate
$selectedSeats = $_SESSION['selected_seats'];

// PreluareA ultimului eveniment adăugat
$sql = "SELECT Nume_Eveniment, Data_Eveniment, Locatie_Eveniment FROM Evenimente ORDER BY EvenimentID DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Preia datele evenimentului
    $row = $result->fetch_assoc();
    $numeEveniment = $row['Nume_Eveniment'];
    $dataEveniment = $row['Data_Eveniment'];
    $locatieEveniment = $row['Locatie_Eveniment'];
} else {
    $numeEveniment = "Nu există evenimente disponibile.";
    $dataEveniment = "";
    $locatieEveniment = "";
}

// Preluare detalii bilete selectate
$detaliiBilete = [];
if (!empty($selectedSeats)) {
    $placeholders = implode(',', array_fill(0, count($selectedSeats), '?'));
    $types = str_repeat('i', count($selectedSeats));
    
    $sqlBilete = "SELECT DetaliiLocuri.Sector, DetaliiLocuri.Rand, DetaliiLocuri.Loc, CategoriiLocuri.Nume_Categorie, CategoriiLocuri.Pret
                FROM DetaliiLocuri
                JOIN CategoriiLocuri ON DetaliiLocuri.CategorieID = CategoriiLocuri.CategorieID
                WHERE DetaliiLocuri.DetaliiLocID IN ($placeholders)";
    
    $stmt = $conn->prepare($sqlBilete);
    $stmt->bind_param($types, ...$selectedSeats);
    $stmt->execute();
    $resultBilete = $stmt->get_result();
    
    while ($row = $resultBilete->fetch_assoc()) {
        $detaliiBilete[] = $row;
    }

    // Calculul totalului prețurilor biletelor
    $totalPret = 0;
    foreach ($detaliiBilete as $bilet) {
        $totalPret += $bilet['Pret'];
    }
    
    $stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="/Comanda/comanda.css">
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
            <div class="step-circle done"><span><i class="fas fa-check"></i></span></div>
            <div class="step-label">SELECTARE LOCURI</div>
        </div>
        <div class="step">
            <div class="step-circle active"><span>2</span></div>
            <div class="step-label">COMANDA</div>
        </div>
        <div class="step">
            <div class="step-circle"><span>3</span></div>
            <div class="step-label">FINALIZARE</div>
        </div>
    </div>

    <h2 class="mt-5 mb-5">Comanda ta</h2>

    <h5 class="mt-4"><?php echo htmlspecialchars($numeEveniment); ?></h5>
    <div class="d-flex date-and-place">
        <p><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($dataEveniment)); ?></p>
        <p><i class="fas fa-map-marker-alt ml-4"></i> <?php echo htmlspecialchars($locatieEveniment); ?></p>
    </div>

    <div class="table-responsive mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>BILETE</th>
                    <th>LOC</th>
                    <th class="last-th">VALOARE BILETE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detaliiBilete as $bilet): ?>
                    <tr>
                        <td><?php echo "1 x " . htmlspecialchars($bilet['Nume_Categorie']) . " - " . htmlspecialchars($bilet['Pret']) . " RON"; ?></td>
                        <td><?php echo htmlspecialchars($bilet['Nume_Categorie']) . ", " . htmlspecialchars($bilet['Sector']) . ", Rand " . htmlspecialchars($bilet['Rand']) . ", Loc " . htmlspecialchars($bilet['Loc']); ?></td>
                        <td class="text-right"><?php echo htmlspecialchars($bilet['Pret']) . " RON"; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 float-right total-price">
        <h4>Total: <?php echo number_format($totalPret, 2); ?> RON</h4>
    </div>

    <div class="date-facturare">
        <h5 class="date-facturare-header">Date facturare</h5>
    </div>

    <form id="formular-facturare" action="procesare_facturare.php" method="post" class="mt-3">
        <div class="form-group">
            <label for="contact-name" class="mb-3">Persoana de contact</label>
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="contact-nume" name="contact_nume" autocomplete="off" placeholder="Nume" value="<?php echo $contactNume; ?>">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="contact-prenume" name="contact_prenume" autocomplete="off" placeholder="Prenume" value="<?php echo $contactPrenume; ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="contact-telefon">Telefon</label>
            <input type="tel" class="form-control" id="contact-telefon" name="contact_telefon" autocomplete="off" placeholder="Număr de telefon" value="<?php echo $contactTelefon; ?>">
        </div>
        <div class="form-group">
            <label for="adresa-facturare" class="mb-3">Adresa de facturare</label>
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="adresa-strada" name="adresa_strada" autocomplete="off" placeholder="Strada" value="<?php echo $adresaStrada; ?>">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-numar" name="adresa_numar" autocomplete="off" placeholder="Număr" value="<?php echo $adresaNumar; ?>">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-bloc" name="adresa_bloc" autocomplete="off" placeholder="Bloc" value="<?php echo $adresaBloc; ?>">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-scara" name="adresa_scara" autocomplete="off" placeholder="Scara" value="<?php echo $adresaScara; ?>">
                </div>
            </div>
            <div class="form-row mt-2">
                <div class="col">
                    <input type="text" class="form-control" id="adresa-etaj" name="adresa_etaj" autocomplete="off" placeholder="Etaj" value="<?php echo $adresaEtaj; ?>">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-apartament" name="adresa_apartament" autocomplete="off" placeholder="Apartament" value="<?php echo $adresaApartament; ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="adresa-judet">Județ</label>
            <select class="form-control" id="adresa-judet" name="adresa_judet">
                <option value="" disabled>Selectează...</option>
                <!-- Adăugați aici opțiunile județelor -->
                <option value="Alba">Alba</option>
                <option value="Arad">Arad</option>
                <option value="Arges">Arges</option>
                <option value="Bacau">Bacau</option>
                <option value="Bihor">Bihor</option>
                <option value="Bistrita-Nasaud">Bistrita-Nasaud</option>
                <option value="Botosani">Botosani</option>
                <option value="Braila">Braila</option>
                <option value="Brasov">Brasov</option>
                <option value="Bucuresti">Bucuresti</option>
                <option value="Buzau">Buzau</option>
                <option value="Calarasi">Calarasi</option>
                <option value="Caras-Severin">Caras-Severin</option>
                <option value="Cluj">Cluj</option>
                <option value="Constanta">Constanta</option>
                <option value="Covasna">Covasna</option>
                <option value="Dambovita">Dambovita</option>
                <option value="Dolj">Dolj</option>
                <option value="Galati">Galati</option>
                <option value="Giurgiu">Giurgiu</option>
                <option value="Gorj">Gorj</option>
                <option value="Harghita">Harghita</option>
                <option value="Hunedoara">Hunedoara</option>
                <option value="Ialomita">Ialomita</option>
                <option value="Iasi">Iasi</option>
                <option value="Ilfov">Ilfov</option>
                <option value="Maramures">Maramures</option>
                <option value="Mehedinti">Mehedinti</option>
                <option value="Mures">Mures</option>
                <option value="Neamt">Neamt</option>
                <option value="Olt">Olt</option>
                <option value="Prahova">Prahova</option>
                <option value="Salaj">Salaj</option>
                <option value="Satu Mare">Satu Mare</option>
                <option value="Sibiu">Sibiu</option>
                <option value="Suceava">Suceava</option>
                <option value="Teleorman">Teleorman</option>
                <option value="Timis">Timis</option>
                <option value="Tulcea">Tulcea</option>
                <option value="Valcea">Valcea</option>
                <option value="Vaslui">Vaslui</option>
                <option value="Vrancea">Vrancea</option>
            </select>
        </div>
        <div class="form-group">
            <label for="adresa-localitate">Localitate</label>
            <select class="form-control" id="adresa-localitate" name="adresa_localitate">
                <option value="" disabled>Selectează...</option>
            </select>
        </div>

        <div class="modalitate-de-plata">
            <h5 class="modalitate-de-plata-header">Modalitate de plată</h5>
        </div>

        <div class="form-group">
            <div class="form-check">
                <div class="form-check-text">
                    <input class="form-check-input custom-radio" type="radio" name="modalitate_plata" id="card_online" value="card_online">
                    <label class="form-check-label" for="card_online">
                        Card online
                    </label>
                    <p class="text-muted">Plătești imediat, fără costuri suplimentare.</p>
                </div>
                <div class="card-logos ml-3">
                    <img src="../Imagini/Carduri/Visa.png" alt="Visa" height="50">
                    <img src="../Imagini/Carduri/MasterCard.png" alt="MasterCard" height="50">
                    <img src="../Imagini/Carduri/American-Express.png" alt="American Express" height="50">
                    <img src="../Imagini/Carduri/UnionPay.png" alt="UnionPay" height="50">
                </div>
            </div>
        </div>

        <button type="submit" class="btn custom-btn mt-5 pt-2 pb-2">CONTINUĂ</button>
    </form>

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
        $(document).ready(function() {
            // Obiect care conține localitățile pentru fiecare județ
            const localitati = {
                "Alba": ["Abrud", "Aiud", "Alba Iulia", "Baia de Aries", "Blandiana", "Blaj", "Bucerdea Granoasa", "Campeni", "Cenade", "Cergau", "Cugir", "Cut", "Farau", "Garbova", "Hoparta", "Ighiu", "Intregalde", "Jidvei", "Lunca Muresului", "Lupsa", "Metes", "Miraslau", "Mogos", "Noslac", "Ocna Mures", "Ocolis", "Pianu", "Posaga", "Radesti", "Ramet", "Rosia Montana", "Salciua", "Salistea", "Santimbru", "Sebes", "Sibot", "Sona", "Sohodol", "Stremt", "Sugag", "Teius", "Unirea", "Vadu Motilor", "Valea Lunga", "Vidra", "Zlatna"],
                "Arad": ["Arad", "Chisineu-Cris", "Curtici", "Ineu", "Lipova", "Nadlac", "Pancota", "Pecica", "Santana", "Sebis", "Iermata Neagra", "Chisindia", "Archis", "Sicula", "Sagu", "Barzava", "Birchis", "Buteni", "Carand", "Cermei", "Conop", "Covasant", "Craiva", "Dezna", "Dieci", "Dorolt", "Dudestii Vechi", "Fantanele", "Felnac", "Ghioroc", "Graniceri", "Gurahont", "Halmagel", "Halmagiu", "Hasmas", "Livada", "Macea", "Moneasa", "Nadlac", "Olari", "Paulis", "Peregu Mare", "Petris", "Plescuta", "Secusigiu", "Seleus", "Sepreus", "Simand", "Siria", "Sistarovat", "Soimos", "Soimus", "Sofronea", "Tarnova", "Taut", "Ususau", "Vinga", "Zabrani", "Zadareni", "Zarand", "Zerind"],
                "Arges": ["Babana", "Baiculesti", "Bascov", "Berevoiesti", "Birla", "Barla", "Boteni", "Bradu", "Budeasa", "Bughea de Jos", "Bughea de Sus", "Caldararu", "Calinesti", "Campulung", "Cepari", "Cetateni", "Cicanesti", "Ciofrangeni", "Ciomagesti", "Cocu", "Corbeni", "Corbi", "Costesti", "Cotmeana", "Cuca", "Curtea de Arges", "Dambovicioara", "Davidesti", "Dobresti", "Domnesti", "Dragoslavele", "Godeni", "Harsesti", "Hartiesti", "Izvoru", "Leresti", "Lunca Corbului", "Malureni", "Maracineni", "Merisani", "Micesti", "Mihaesti", "Mioveni", "Mosoaia", "Muzau", "Nucsoara", "Oarja", "Pietrosani", "Pitesti", "Poiana Lacului", "Popesti", "Raca", "Ratesti", "Rociu", "stefan cel Mare", "Schitu Golesti", "Slobozia", "Stalpeni", "Stefanesti", "Stoenesti", "Stolnici", "Suseni", "Titesti", "Tigveni", "Topoloveni", "Uda", "Ungheni", "Valea Danului", "Valea Iasului", "Valea Mare-Pravat", "Vedea", "Vladesti"],
                "Bacau": ["Bacau", "Barsanesti", "Beresti", "Beresti-Bistrita", "Beresti-Tazlau", "Blagesti", "Blajel", "Blascovici", "Blagesti", "Blajeni", "Bogdana", "Bogdan", "Botosana", "Botosanesti", "Bradesti", "Bratila", "Brateni", "Buciumi", "Buhoci", "Buhusi", "Caiuti", "Cleja", "Colonesti", "Comanesti", "Caiuti", "Cotofanesti", "Cuca", "Cucova", "Damienesti", "Damascani", "Darmanesti", "Dofteana", "Filipeni", "Filipesti", "Gaiceana", "Ghimes-Faget", "Gioseni", "Gura Vaii", "Helegiu", "Horgesti", "Itesti", "Izvoare", "Letea Veche", "Livezi", "Luizi-Calugara", "Magiresti", "Maxineni", "Manastirea Casin", "Margineni", "Maruntei", "Marasesti", "Moinesti", "Motoseni", "Nicolae Balcescu", "Oituz", "Onesti", "Oteleni", "Palanca", "Parava", "Pargaresti", "Parincea", "Parjol", "Pancesti", "Podu Turcului", "Poduri", "Prajesti", "Racaciuni", "Rachitoasa", "Radesti", "Radoaia", "Raducaneni", "Raduceni", "Raduiti", "Rastoaca", "Sanduleni", "Sascut", "Scorteni", "Secuieni", "Siculeni", "Sinca", "Slobozia", "Solont", "Stanisesti", "Stiuca", "Strugari", "Sturzeni", "Suceveni", "Targu Ocna", "Tamas", "Tamaseni", "Tarcau", "Tasca", "Tarata", "Tartasesti", "Tintesti", "Traian", "Urechesti", "Valea Seaca", "Valea Ursului", "Varciorova", "Viforeni", "Vulcanesti", "Zemes"],
                "Bihor": ["Abram", "Abramut", "Adea", "Astileu", "Avram Iancu", "Auseu", "Balc", "Batar", "Beius", "Biharia", "Boianu Mare", "Borod", "Bratca", "Brusturi", "Budureasa", "Buduslau", "Bulz", "Cabesti", "Cadea", "Cefa", "Ceica", "Cetariu", "Cherechiu", "Chislaz", "Ciumeghiu", "Cociuba Mare", "Copacel", "Cristioru de Jos", "Curatele", "Dobresti", "Draganesti", "Dumbravita de Codru", "Finis", "Gepiu", "Girisu de Cris", "Hidiselu de Sus", "Holod", "Husasau de Tinca", "Ineu", "Lazareni", "Lunca", "Lunca Visagului", "Margita", "Madaras", "Magesti", "Nojorid", "Olcea", "Osorhei", "Paleu", "Paulis", "Pietroasa", "Pocola", "Pomezeu", "Rieni", "Rosia", "Sacadat", "Salard", "Sarbi", "Sambata", "Sanmartin", "Santandrei", "Sannicolau Roman", "Simian", "Sinteu", "Stei", "Soimi", "Suplacu de Barcau", "Tarcea", "Tarcaia", "Tileagd", "Tinca", "Tulca", "Uileacu de Beius", "Valea lui Mihai", "Varciorog", "Viisoara", "Vintere", "Voivozi", "Zerind"],
                "Bistrita-Nasaud": ["Beclean", "Bistrita", "Budesti", "Bistrita Bargaului", "Branistea", "Budacu de Jos", "Caianu Mic", "Chiochis", "Chiuza", "Ciceu-Giurgesti", "Ciceu-Mihaiesti", "Cociu", "Dumitrita", "Feldru", "Ilva Mare", "Ilva Mica", "Josenii Bargaului", "Lechinta", "Lunca Ilvei", "Maieru", "Magura Ilvei", "Mariselu", "Maierus", "Matei", "Micestii de Campie", "Milas", "Monor", "Nasaud", "Negrilesti", "Nimigea", "Parva", "Petru Rares", "Poiana Ilvei", "Rebra", "Rebrisoara", "Rodna", "Romuli", "Runcu Salvei", "Sangeorz-Bai", "Sieu", "Sieu-Magherus", "Sieu-Odorhei", "Sieut", "Silivasu de Campie", "Spermezeu", "Teaca", "Telciu", "Tiha Bargaului", "Uriu", "Urmenis", "Zagra"],
                "Botosani": ["Adaseni", "Albesti", "Avrameni", "Baluseni", "Blandesti", "Botosani", "Braesti", "Bucecea", "Buzeni", "Calarasi", "Candesti", "Concesti", "Corlateni", "Corni", "Cosula", "Cotusca", "Cristesti", "Curtesti", "Dangeni", "Darabani", "Darmanesti", "Dealu Mare", "Dersca", "Dimacheni", "Dobarceni", "Draguseni", "Durnesti", "Flamanzi", "Frumusica", "Gorbanesti", "Hanesti", "Havarna", "Hiliseu-Horia", "Hlipiceni", "Hudesti", "Ibanesti", "Leorda", "Lozna", "Manoleasa", "Mihaileni", "Mihalaseni", "Mileanca", "Mitoc", "Nicseni", "Paltinis", "Pomarla", "Prajeni", "Rachiti", "Radauti-Prut", "Ripiceni", "Roma", "Romanesti", "Saveni", "Santa Mare", "Stauceni", "Suharau", "Sendriceni", "Stiubieni", "Todireni", "Truşeşti", "Tudora", "Unteni", "Ungureni", "Vaculesti", "Viisoara", "Vladeni", "Vorona"],
                "Braila": ["Bertestii de Jos", "Bordei Verde", "Braila", "Cazasu", "Chiscani", "Ciocile", "Ciresu", "Dudesti", "Faurei", "Frecatei", "Galbenu", "Gemenele", "Gradistea", "Gropeni", "Ianca", "Insuratei", "Jirlau", "Marasu", "Maxineni", "Mircea Voda", "Movila Miresii", "Racovita", "Romanu", "Salcia Tudor", "Scortaru Nou", "Silistea", "Stancuta", "Surdila-Greci", "Surdila-Gaiseanca", "Tichilesti", "Traian", "Tudor Vladimirescu", "Ulmu", "Unirea", "Vadeni", "Victoria", "Visani", "Viziru", "Zavoaia"],
                "Brasov": ["Apata", "Augustin", "Beclean", "Bod", "Bran", "Brasov", "Bunesti", "Cata", "Cristian", "Crizbav", "Dumbravita", "Feldioara", "Fagaras", "Ghimbav", "Halchiu", "Harman", "Harseni", "Hoghiz", "Holbav", "Jibert", "Lisa", "Lovrin", "Mandra", "Moieciu", "Ormenis", "Parau", "Poiana Marului", "Predeal", "Racos", "Rasnov", "Recea", "Rupea", "Sambata de Sus", "Sanpetru", "Sacele", "Sercaia", "Sinca", "Sinca Noua", "Soars", "Tarlungeni", "Teliu", "Ucea", "Ungra", "Victoria", "Vistea", "Voila", "Vulcan"],
                "Bucuresti": ["Bucuresti (Sector 1)", "Bucuresti (Sector 2)", "Bucuresti (Sector 3)", "Bucuresti (Sector 4)", "Bucuresti (Sector 5)", "Bucuresti (Sector 6)"],
                "Buzau": ["Amaru", "Balaceanu", "Barbulesti", "Beceni", "Berca", "Bisoca", "Blajani", "Boldu", "Bozioru", "Bradeanu", "Braesti", "Buda", "Buzau", "C.A. Rosetti", "Catina", "Cerna", "Chiliile", "Cilibia", "Cislau", "Clondiru de Jos", "Cochirleanca", "Colti", "Costesti", "Cozieni", "Florica", "Frecatei", "Furduiesti", "Galbinasi", "Ghergheasa", "Gheraseni", "Girbovi", "Glodeanu Sarat", "Glodeanu-Silistea", "Golu Gruiu", "Grebanu", "Hales", "Istria", "Largu", "Lopatari", "Luciu", "Magura", "Margaritesti", "Merei", "Mihailesti", "Movila Banului", "Murgulesti", "Nehoiu", "Nenciulesti", "Odaia Banului", "Padina", "Panatau", "Parscov", "Patarlagele", "Pietroasele", "Pogoanele", "Podgoria", "Posta Calnau", "Puiesti", "Racoviteni", "Ramnicu Sarat", "Robeasca", "Rusetu", "Sageata", "Sarata Monteoru", "Sarulesti", "Sapoca", "Scortoasa", "Siriu", "Smeeni", "Stalpu", "Tisau", "Topliceni", "Ulmeni", "Unguriu", "Vadu Pasii", "Valea Salciei", "Valea Ursului", "Valcele", "Vernesti", "Vintila Voda", "Viperesti", "Zarnesti", "Ziduri"],
                "Calarasi": ["Balaceanca", "Belciugatele", "Borcea", "Budesti", "Calarasi", "Cascioarele", "Cauzati", "Coconi", "Coslogeni", "Curcani", "Cuza Voda", "Dichiseni", "Dor Marunt", "Dragalina", "Dragos Voda", "Draganesti", "Fetesti", "Florica", "Frasinet", "Frumusani", "Fundulea", "Galbinasi", "Galesti", "Gheorghe Doja", "Gradistea", "Ileana", "Independenta", "Jegalia", "Lehliu Gara", "Lehliu Sat", "Luica", "Manastirea", "Modelu", "Nana", "Nicolae Balcescu", "Oltenita", "Perisoru", "Plataresti", "Radovanu", "Roseti", "Sarulesti", "Soldanu", "Stefan cel Mare", "Tamadau Mare", "Ulmu", "Unirea", "Vasilati", "Vilcelele"],
                "Caras-Severin": ["Anina", "Armenis", "Baile Herculane", "Bania", "Bautar", "Berzasca", "Berzovia", "Bocsa", "Bozovici", "Brebu Nou", "Brebu", "Buchin", "Bucosnita", "Caransebes", "Carasova", "Ciclova Romana", "Ciudanovita", "Ciuchici", "Constantin Daicoviciu", "Cornea", "Cornereva", "Coronini", "Dalboset", "Doclin", "Domasnea", "Eftimie Murgu", "Ezeris", "Farliug", "Forotic", "Garnic", "Glimboca", "Gradinari", "Iablanita", "Lapusnicu Mare", "Lapusnicel", "Maureni", "Mehadia", "Mehadica", "Moldova Noua", "Naidas", "Obreja", "Ocna de Fier", "Oravita", "Otelu Rosu", "Paltinis", "Pojejena", "Prigor", "Ramna", "Resita", "Rusca Montana", "Sasca Montana", "Sichevita", "Slatina-Timis", "Socol", "Sopotu Nou", "Teregova", "Ticvaniu Mare", "Toplet", "Turnu Ruieni", "Valiug", "Varadia", "Verendin", "Vrani", "Zavoi", "Zorlentu Mare"],
                "Cluj": ["Aghiresu", "Aghiresu-Fabrici", "Aiton", "Alunis", "Apahida", "Ardeova", "Arghis", "Aschileu Mare", "Aschileu Mic", "Baciu", "Badeni", "Baisoara", "Balcesti", "Bagara", "Baita", "Barai", "Bedeciu", "Belis", "Berindu", "Berindu Nou", "Bica", "Bobalna", "Bociu", "Bodrog", "Bogata", "Bontida", "Borsa", "Borzesti", "Boteni", "Bucea", "Buza", "Buzas", "Cacova Ierii", "Caianu", "Calarasi", "Camasoaia", "Calatele", "Camarasu", "Capusu Mare", "Capusu Mic", "Campia Turzii", "Catina", "Ceaba", "Ceanu Mare", "Cernuc", "Chiuiesti", "Chiuis", "Chisau", "Cioara de Sus", "Cioroboreni", "Ciucea", "Ciurila", "Catcau", "Cluj-Napoca", "Coc", "Cojocna", "Coldau", "Comsesti", "Coplean", "Cornesti", "Corpadea", "Catunel", "Cristorel", "Cristur", "Dabaca", "Dealu Mare", "Deus", "Dorolt", "Dragu", "Dumbrava", "Feleacu", "Fizesu Gherlii", "Frata", "Fundatura", "Gadalin", "Garbau", "Gherla", "Gilau", "Giula", "Gura Rasca", "Hadareni", "Hasdate", "Hasmasu Catusii", "Hasmasu Gheorgheni", "Hendreni", "Hodisu", "Horlacea", "Huedin", "Iacobeni", "Iara", "Iclod", "Izvoru Crisului", "Jucu", "Lacu", "Lapus", "Leghia", "Lelese", "Liteni", "Livada", "Lobodea", "Luna", "Maguri-Racatau", "Manasturel", "Maguri", "Marisel", "Mariselu", "Marin", "Margau", "Margautel", "Mera", "Mociu", "Morlaca", "Muntele Bocului", "Muntele Baisorii", "Muntele Rece", "Muresenii de Campie", "Nasal", "Negreni", "Nima", "Nires", "Olcea", "Osorhei", "Padina", "Palatca", "Pascani", "Petrestii de Jos", "Piatra Craiului", "Piatra Cristis", "Piatra Harabace", "Piatra Neamt", "Piatra Prisacina", "Pintestii de Jos", "Poiana", "Poieni", "Poienita", "Popesti", "Popteleac", "Rachitele", "Radaia", "Recea-Cristur", "Risca", "Rogojel", "Romanesti", "Rasca", "Sacel", "Salatruc", "Salistea Noua", "Salistea Veche", "Sancraiu", "Sanmartin", "Sanmihaiu de Campie", "Sanpaul", "Santejude", "Santioana", "Savadisla", "Silivasu de Campie", "Sopor", "Somesu Cald", "Somesu Rece", "Straja", "Suatu", "Suceagu", "Sumurducu", "Surduc", "Sandulesti", "Savadisla", "Sacuieu", "Tauseni", "Taga", "Topa Mica", "Turda", "Tureni", "Turea", "Unguras", "Uria", "Valea Calda", "Valea Dragului", "Valea Florilor", "Valea Ierii", "Valea lui Catis", "Valea lui Mihai", "Valea Mare", "Valea Vadului", "Varciorog", "Vechea", "Viisoara", "Vima Mare", "Visea", "Vlaha", "Zoreni"],
                "Constanta": ["Adamclisi", "Agigea", "Albesti", "Aliman", "Amzacea", "Baneasa", "Baraganu", "Castelu", "Cernavoda", "Cerchezu", "Chirnogeni", "Ciobanu", "Ciocarlia", "Cobadin", "Constanta", "Corbu", "Costinesti", "Cumpana", "Cuza Voda", "Deleni", "Dobromir", "Eforie", "Fantanele", "Garvan", "Ghinda", "Ghindaresti", "Gura Dobrogei", "Harsova", "Independenta", "Ion Corvin", "Istria", "Limanu", "Lipnita", "Lumina", "Mereni", "Mangalia", "Medgidia", "Mihai Viteazu", "Mircea Voda", "Murfatlar", "Negru Voda", "Navodari", "Oltina", "Ostrov", "Pantelimon", "Pecineaga", "Piatra", "Poarta Alba", "Rasova", "Saraiu", "Seimeni", "Silistra", "Silistea", "Targusor", "Techirghiol", "Topalu", "Topraisar", "Tortoman", "Tuzla", "Valu lui Traian", "Vulturu"],
                "Covasna": ["Aita Mare", "Aita Seaca", "Baraolt", "Barcani", "Batanii Mari", "Bodoc", "Borosneu Mare", "Bradut", "Bretcu", "Borosneul Mic", "Catalina", "Chichis", "Cozmeni", "Covasna", "Dalnic", "Dobarlau", "Ghelinta", "Ghidfalau", "Haghig", "Halaucesti", "Harseni", "Ilieni", "Lemnia", "Marcusa", "Malnas", "Mereni", "Micfalau", "Moacsa", "Ojdula", "Ozun", "Poian", "Reci", "Sfantu Gheorghe", "Sita Buzaului", "Turia", "Valea Crisului", "Varghis", "Zagon", "Zabala"],
                "Dambovita": ["Aninoasa", "Bezdead", "Bilciuresti", "Branesti", "Baleni", "Barbuletu", "Balteni", "Bratesti", "Branistea", "Brezoaele", "Buciumeni", "Butimanu", "Candesti", "Ciocanesti", "Cobia", "Cojasca", "Comisani", "Corbii Mari", "Costestii din Vale", "Crangurile", "Crevedia", "Darmanesti", "Dobra", "Doicesti", "Dragodana", "Dragomiresti", "Finta", "Fieni", "Gaesti", "Ghergani", "Glodeni", "Gura Foii", "Gura Ocnitei", "Hulubesti", "I.L. Caragiale", "Iedera", "Lucieni", "Ludesti", "Lunguletu", "Malu cu Flori", "Matasaru", "Mogosani", "Moroeni", "Morteni", "Manesti", "Niculesti", "Nucet", "Ocnita", "Odobasca", "Pacureti", "Persinari", "Petresti", "Pietrari", "Poiana", "Potlogi", "Produlesti", "Pucheni", "Pucioasa", "Racari", "Rascaeti", "Rau Alb", "Razvad", "Romanesti", "Slobozia Moara", "Selaru", "Sotanga", "Stefanesti", "Tartasesti", "Tatarani", "Tauti-Magheraus", "Uliesti", "Ulmi", "Valea Lunga", "Valea Mare", "Vacaresti", "Visina", "Vladeni", "Voinesti", "Vulcana Bai", "Vulcana-Pandele"],
                "Dolj": ["Afumati", "Amarastii de Jos", "Amarastii de Sus", "Apele Vii", "Babiciu", "Bailesti", "Barca", "Bechet", "Bistret", "Brabova", "Bradesti", "Botosesti-Paia", "Boureni", "Bistretu Nou", "Breasta", "Bucovat", "Bulzesti", "Bunoiu", "Calopar", "Caraula", "Calarasi", "Carcea", "Carna", "Castra Nova", "Castronu Nou", "Caciulatesti", "Celaru", "Cerat", "Cernatesti", "Cetate", "Cioroiasi", "Cioroiu", "Cotofeni din Dos", "Cotofenii din Fata", "Craiova", "Dabuleni", "Daneti", "Desa", "Diosti", "Dobresti", "Dragotesti", "Dranic", "Dumitresti", "Farcas", "Galiciuica", "Gangiova", "Ghercesti", "Gighera", "Goicea", "Gogosari", "Gogosu", "Gogosita", "Gura Vaii", "Isalnita", "Isverna", "Intorsura", "Isaras", "Leu", "Lipovu", "Luhulet", "Lunca Banului", "Lunca Jiului", "Malu Mare", "Macesu de Sus", "Macesu de Jos", "Magura", "Marsani", "Melinesti", "Melinestii de Sus", "Mihaita", "Motatei", "Motru", "Murgulesti", "Murgasi", "Murgoci", "Neamtu", "Negoi", "Negulesti", "Nedeia", "Nedeia din Deal", "Osica de Sus", "Osica de Jos", "Orodel", "Perisor", "Pielesti", "Piscu Lung", "Piscu Vechi", "Piscu", "Plenita", "Plopsoru", "Podari", "Poiana Mare", "Popova", "Predisor", "Predesti", "Radovan", "Racarii de Jos", "Racarii de Sus", "Raducaneni", "Racovita", "Robanesti", "Salcuta", "Salciile", "Sardan", "Segarcea", "Seaca de Camp", "Seaca de Padure", "Secu", "Simnicu de Sus", "Sopot", "Simnic", "Simnic de Jos", "Teasc", "Teisor", "Tescani", "Tuglui", "Unirea", "Urdari", "Valea Stanciului", "Vela", "Verbita", "Vartop", "Visina", "Vladimirescu", "Zanoaga", "Zaval"],
                "Galati": ["Balabanesti", "Balasesti", "Barcea", "Beresti", "Beresti-Meria", "Branistea", "Bucesti", "Cavadinesti", "Certesti", "Corni", "Cosmesti", "Costache Negri", "Cuza Voda", "Draganesti", "Draguseni", "Fartanesti", "Foltesti", "Frumusita", "Fundeni", "Galati", "Ghidigeni", "Gohor", "Grivita", "Independenta", "Ivesti", "Jorasti", "Liesti", "Mastacani", "Matca", "Munteni", "Nicoresti", "Namoloasa", "Pechea", "Priponesti", "Rediu", "Scanteiesti", "Schela", "Slobozia Conachi", "Smulti", "Sendreni", "Tecuci", "Tudor Vladimirescu", "Tulucesti", "Umbraresti", "Vanatori", "Vladesti"],
                "Giurgiu": ["Adunatii-Copaceni", "Bolintin-Deal", "Bolintin-Vale", "Bucsani", "Buturugeni", "Calugareni", "Clejani", "Colibasi", "Comana", "Crevedia Mare", "Daia", "Dobreni", "Floresti-Stoenesti", "Fratesti", "Gaiseni", "Ghimpati", "Giurgiu", "Gogosari", "Gostesti", "Gostinu", "Gradinari", "Greaca", "Herasti", "Hotarele", "Isvoarele", "Izvoarele", "Joita", "Letca Noua", "Malu", "Marsa", "Mihai Bravu", "Mihailesti", "Mirauti", "Naipu", "Novaci", "Obedeni", "Ogrezeni", "Oinacu", "Prundu", "Putineiu", "Rasuceni", "Roata de Jos", "Sabareni", "Sabarenii Noi", "Salcioara", "Schitu", "Slobozia", "Singureni", "Stanesti", "Stoenesti", "Toporu", "Ulmi", "Vanatorii Mici", "Vedea"],
                "Gorj": ["Albeni", "Alimpesti", "Aninoasa", "Arcani", "Baia de Fier", "Balanesti", "Balesti", "Balteni", "Barbatesti", "Berlesti", "Bolbosi", "Borascu", "Branesti", "Bratuia", "Brosteni", "Bumbesti-Jiu", "Bumbesti-Pitic", "Bustuchin", "Capreni", "Carbunesti", "Catunele", "Calnic", "Ciuperceni", "Cruset", "Crasna", "Danciulesti", "Danesti", "Dragotesti", "Dragutesti", "Farcasesti", "Glogova", "Godinesti", "Hurezani", "Ionesti", "Lelesti", "Licurici", "Logresti", "Matasari", "Mihaesti", "Motru", "Musetesti", "Negomir", "Novaci", "Pades", "Pestisani", "Plopsoru", "Polovragi", "Prigoria", "Rovinari", "Rosia de Amaradia", "Runcu", "Sacelu", "Samarinesti", "Schela", "Scoarta", "Slivilesti", "Stanesti", "Stejari", "Stoina", "Targu Carbunesti", "Targu Jiu", "Telesti", "Tismana", "Turceni", "Turcinesti", "Urdari", "Vagiulesti", "Vladimir"],
                "Harghita": ["Atid", "Avramesti", "Balan", "Bilbor", "Borsec", "Bradesti", "Capalnita", "Ciceu", "Ciumani", "Ciucsangeorgiu", "Corbu", "Corund", "Cristuru Secuiesc", "Danesti", "Dealu", "Ditrau", "Feliceni", "Frumoasa", "Galautas", "Gheorgheni", "Joseni", "Lazarea", "Leliceni", "Lueta", "Madaras", "Martinis", "Miercurea Ciuc", "Mihaileni", "Mogoseni", "Mugeni", "Ocland", "Plaiesii de Jos", "Praid", "Racu", "Remetea", "Sandominic", "Sarmas", "Sansimion", "Santimbru", "Sancraieni", "Secuieni", "Siculeni", "Simonesti", "Subcetate", "Suseni", "Tulghes", "Tuşnad", "Ulies", "Varsag", "Vlahita", "Voslabeni", "Zetea"],
                "Hunedoara": ["Baia de Cris", "Bacia", "Baita", "Balsa", "Banita", "Baru", "Batrana", "Beriu", "Blajeni", "Bosorod", "Branisca", "Bretea Romana", "Bucuresci", "Bucosnita", "Bunila", "Calan", "Carjiti", "Certeju de Sus", "Criscior", "Dobra", "General Berthelot", "Geoagiu", "Ghelari", "Gurasada", "Hateg", "Hondol", "Horea", "Hunedoara", "Ilia", "Lapugiu de Jos", "Lelese", "Lunca Cernii de Jos", "Luncoiu de Jos", "Martinesti", "Orastie", "Petrila", "Petrosani", "Pui", "Rachitova", "Ribita", "Romos", "Salasu de Sus", "Santamaria-Orlea", "Sarmizegetusa", "Salasu de Sus", "Soimus", "Stei", "Teliucu Inferior", "Toplita", "Turdas", "Uricani", "Vata de Jos", "Vulcan", "Zam"],
                "Ialomita": ["Adancata", "Alexeni", "Albesti", "Amara", "Andrasesti", "Armesti", "Axintele", "Balaciu", "Barcanesti", "Barbulesti", "Boranesti", "Bordusani", "Brosteni", "Bucu", "Ciochina", "Cocora", "Cosereni", "Cosambesti", "Dridu", "Facaeni", "Fierbinti-Targ", "Garbovi", "Gheorghe Doja", "Gura Ialomitei", "Grivita", "Ion Roata", "Jilavele", "Mihail Kogalniceanu", "Milosesti", "Movila", "Munteni Buzau", "Perieti", "Platonesti", "Radulesti", "Reviga", "Rosiori", "Salcioara", "Sarateni", "Scanteia", "Sfantu Gheorghe", "Slobozia", "Smirna", "Stelnica", "Suditi", "Traian", "Urziceni", "Valea Ciorii", "Valea Macrisului", "Vladeni"],
                "Iasi": ["Alexandru Ioan Cuza", "Andrieseni", "Aroneanu", "Baltati", "Barbatesti", "Belcesti", "Bivolari", "Braesti", "Butea", "Ciortesti", "Ciurea", "Coarnele Caprei", "Comarna", "Costesti", "Costuleni", "Cozmesti", "Cristesti", "Deleni", "Dobrovat", "Dolhesti", "Draguseni", "Erbiceni", "Fantanele", "Focuri", "Golaiesti", "Grajduri", "Grozesti", "Gropnita", "Halaucesti", "Harmanesti", "Helesteni", "Holboca", "Horlesti", "Ipatele", "Letcani", "Lungani", "Madarjac", "Mircesti", "Mironeasa", "Miroslava", "Mogosesti", "Mogosesti-Siret", "Mosna", "Movileni", "Oteleni", "Plugari", "Popricani", "Prisacani", "Probota", "Raducaneni", "Rediu", "Romanesti", "Ruginoasa", "Scanteia", "Schitu Duca", "Scobinti", "Sinesti", "Scheia", "Sipote", "Strunga", "Tibana", "Tibanesti", "Tiganasi", "Tomesti", "Trifesti", "Tudor Vladimirescu", "Tutora", "Ungheni", "Valea Lupului", "Victoria", "Vladeni", "Voinesti"],
                "Ilfov": ["1 Decembrie", "Afumati", "Balotesti", "Berceni", "Branesti", "Bragadiru", "Buftea", "Chiajna", "Chitila", "Ciolpani", "Ciorogarla", "Clinceni", "Copaceni", "Corbeanca", "Cornetu", "Darasti-Ilfov", "Dascalu", "Dobresti", "Domnesti", "Dragomiresti-Vale", "Ganeasa", "Glina", "Gradistea", "Gruiu", "Jilava", "Moara Vlasiei", "Magurele", "Nuci", "Otopeni", "Pantelimon", "Petrachioaia", "Popesti-Leordeni", "Peris", "Snagov", "Stefanestii de Jos", "Tunari", "Vidra", "Voluntari"],
                "Maramures": ["Ardusat", "Arinis", "Asuaju de Sus", "Baita de sub Codru", "Baiut", "Basesti", "Barsana", "Baia Mare", "Baia Sprie", "Baita", "Basesti", "Bicaz", "Bistra", "Bocicoiu Mare", "Bocicoiu", "Bogdan Voda", "Boiu Mare", "Borsa", "Botiza", "Breb", "Budesti", "Calinesti", "Cavnic", "Cernesti", "Cicarlau", "Cicarlau", "Coas", "Copalnic-Manastur", "Coroieni", "Cupseni", "Desesti", "Dumbravita", "Farcasa", "Feresti", "Gardani", "Garda de Sus", "Giulesti", "Grosii tiblesului", "Iadara", "Ieud", "Lapus", "Leordina", "Libotin", "Miresu Mare", "Moisei", "Nanesti", "Negreia", "Ocna sugatag", "Oncesti", "Petrova", "Poienile Izei", "Poienile de sub Munte", "Remeti", "Remetea Chioarului", "Repedea", "Rozavlea", "Ruscova", "Sacalaseni", "Sacel", "Salsig", "Sapanta", "Satulung", "Seini", "Sighetu Marmatiei", "Sieu", "Sisesti", "Stramtura", "Suciu de Sus", "Targu Lapus", "Tamaia", "Ulmeni", "Valea Chioarului", "Vima Mica", "Viseu de Sus", "Viseu de Jos"],
                "Mehedinti": ["Baia de Arama", "Balta", "Balacita", "Bacles", "Bala", "Balacita", "Balotesti", "Bacles", "Baltaretu", "Bicles", "Branistea", "Brebita", "Brebu", "Brosteni", "Burila Mare", "Burila Mica", "Butoiesti", "Cazanesti", "Ciochiuta", "Cioroboreni", "Ciresu", "Corcova", "Corlatel", "Corlatel", "Cujmir", "Danceu", "Devesel", "Dobresti", "Eibenthal", "Eselnita", "Floresti", "Garla Mare", "Ghelmegioaia", "Godeanu", "Gogosu", "Gornea", "Grozesti", "Gruia", "Higiu", "Hinova", "Husnicioara", "Ilovat", "Isverna", "Izvoarele", "Jiana", "Livezile", "Malarisca", "Malovat", "Marga", "Marmanu", "Mosteni", "Obirsia de Camp", "Obirsia Closani", "Orsova", "Padina Mare", "Patulele", "Patulele de Jos", "Plenita", "Podeni", "Ponoarele", "Pojejena", "Poroinita", "Portile de Fier", "Pristol", "Putinei", "Rogova", "Rosiori", "Rudina", "Salcia", "Simian", "Sisesti", "Slatinoara", "Stangaceaua", "Svinta", "Tamna", "Turtaba", "Valea Boiereasca", "Vanju Mare", "Vanjulet", "Vrata"],
                "Mures": ["Acatari", "Adamus", "Albesti", "Alunis", "Apold", "Atintis", "Bahnea", "Bagaciu", "Bala", "Band", "Batos", "Beica de Jos", "Bereni", "Bichis", "Biertan", "Bogata", "Brancovenesti", "Breaza", "Ceuasu de Campie", "Chiheru de Jos", "Coroisanmartin", "Corunca", "Craciunesti", "Cristesti", "Cuci", "Cozma", "Cucerdea", "Curteni", "Danes", "Deda", "Eremitu", "Ernei", "Faragau", "Ganesti", "Gheorghe Doja", "Ghindari", "Gornesti", "Grebenisu de Campie", "Gurghiu", "Hodac", "Hodosa", "Iclanzel", "Ideciu de Jos", "Iernut", "Lunca", "Ludus", "Lunca Bradului", "Lunca Muresului", "Magherani", "Madaras", "Manastireni", "Mihesu de Campie", "Nades", "Nazna", "Neaua", "Ogra", "Papiu Ilarian", "Petelea", "Pogaceaua", "Raciu", "Rastolita", "Reghin", "Rusii Munti", "Sancraiu de Mures", "Sangeorgiu de Mures", "Sanpaul", "Santana de Mures", "Sanvasii", "Sarmasu", "Saschiz", "Sighişoara", "Singeorgiu de Padure", "Sinsimion", "Solovastru", "Sovata", "Stanceni", "Taureni", "Tarnaveni", "Targu Mures", "Ungheni", "Valea Larga", "Valenii de Mures", "Vatava", "Viisoara", "Voivodeni", "Zagar", "Zau de Campie"],
                "Neamt": ["Agapia", "Bahna", "Bara", "Bicaz", "Bicaz-Chei", "Bicazu Ardelean", "Bodesti", "Boghicea", "Borca", "Botesti", "Bozieni", "Brusturi", "Candesti", "Ceahlau", "Cordun", "Costisa", "Cracaoani", "Damuc", "Dobreni", "Doljesti", "Draganesti", "Dulcesti", "Farcasa", "Faurei", "Garcina", "Ghindaoani", "Grinties", "Grozavesti", "Hangu", "Harsesti", "Icusesti", "Ion Creanga", "Margineni", "Moldoveni", "Pangarati", "Petricani", "Piatra Neamt", "Piatra soimului", "Pipirig", "Podoleni", "Poiana Teiului", "Raucesti", "Rediu", "Romani", "Ruginoasa", "Sabaoani", "Sagna", "Secuieni", "Stanita", "Tarcau", "Tamaseni", "Targu Neamt", "Tasca", "Tazlau", "Timisesti", "Trifesti", "Tupilati", "Urecheni", "Valea Ursului", "Valeni", "Vanatori-Neamt", "Zanesti"],
                "Olt": ["Babiciu", "Baldovinesti", "Balteni", "Barasti", "Barbatesti", "Barza", "Barbulesti", "Barbatesti", "Bobicesti", "Borcea", "Bostanesti", "Brastavatu", "Brancoveni", "Bucin", "Budesti", "Bujoreni", "Calui", "Campu Mare", "Cezieni", "Cilieni", "Colonesti", "Corabia", "Corbu", "Corlatele", "Craciuneni", "Cucuieti", "Curtisoara", "Daneasa", "Deveselu", "Dobretu", "Dobroteasa", "Dobrosloveni", "Draghiceni", "Draganesti-Olt", "Fagetelu", "Falcoiu", "Farcasele", "Frasinet-Gara", "Ganeasa", "Ghimpeteni", "Garcov", "Giuvarasti", "Grozavesti", "Gradinari", "Gradinile", "Gura Padinii", "Iancu Jianu", "Ipotesti", "Icoana", "Izbiceni", "Izvoarele", "Leleasca", "Leu", "Lunca", "Maruntei", "Milcov", "Morunglav", "Movileni", "Nicolae Titulescu", "Obarsia", "Oporelu", "Optasi-Magura", "Orlea", "Osica de Sus", "Osica de Jos", "Parceni", "Parscoveni", "Perieti", "Piatra-Olt", "Pleşoiu", "Plopii-Slavitesti", "Potcoava", "Radesti", "Redea", "Rotunda", "Rusanesti", "Salcia", "Salciile", "Samburesti", "Scarisoara", "Schitu", "Seaca", "Slatina", "Slatioara", "Soparlita", "Spineni", "Sprancenata", "Strejesti", "Studina", "Samburesti", "Serbanesti", "Tatulesti", "Teslui", "Traian", "Tufeni", "Uda-Clocociov", "Urlati", "Vadastrita", "Valeni", "Valcele", "Verguleasa", "Visina", "Vitomiresti", "Vladila", "Vulpeni"],
                "Prahova": ["Adunati", "Albesti-Paleologu", "Alunis", "Apostolache", "Aricestii Rahtivani", "Aricestii Zeletin", "Azuga", "Baicoi", "Baltesti", "Banesti", "Barcanesti", "Batrani", "Bertea", "Baicoi", "Boldesti-Gradistea", "Boldesti-Scaeni", "Breaza", "Bucov", "Calugareni", "Calugarenii Vechi", "Calugarenii Noi", "Carbunari", "Cerasu", "Chiojdeanca", "Cocorastii Colt", "Cocorastii Mislii", "Colceag", "Comarnic", "Cornu", "Cosminele", "Draganesti", "Drajna", "Filipestii de Padure", "Filipestii de Targ", "Finta", "Floresti", "Fulga", "Gherghita", "Gorgota", "Gornet", "Gornet-Cricov", "Grosani", "Iordacheanu", "Izvoarele", "Jugureni", "Lapos", "Lipanesti", "Magurele", "Magureni", "Maneciu", "Manesti", "Marginenii de Jos", "Marginenii de Sus", "Olari", "Pacureti", "Paulesti", "Plopeni", "Plopu", "Podenii Noi", "Prahova", "Predeal-Sarari", "Provita de Jos", "Provita de Sus", "Puchenii Mari", "Rafov", "Salcia", "Scorteni", "Secaria", "Sinaia", "Sinoe", "Soimari", "Sotrile", "Starchiojd", "Surani", "Talea", "Targsoru Vechi", "Teisani", "Telega", "Tomsani", "Tufeni", "Valea Calugareasca", "Valea Doftanei", "Valea Lunga", "Valenii de Munte", "Varbilau"],
                "Salaj": ["Agrij", "Almasu", "Babeni", "Balan", "Banisor", "Benesat", "Berveni", "Bobota", "Boghis", "Boiu Mare", "Bozna", "Buciumi", "Camar", "Carastelec", "Cehul Silvaniei", "Cehu Silvaniei", "Chiesd", "Chilioara", "Ciocmani", "Ciumarna", "Coseiu", "Crasna", "Creaca", "Cristolt", "Cubulcut", "Cuzaplac", "Dabaceni", "Dobrin", "Dragu", "Fildu de Jos", "Garbou", "Gilgau", "Girbou", "Hereclean", "Hida", "Horoatu Crasnei", "Ileanda", "Ip", "Jibou", "Letca", "Lozna", "Maeriste", "Marca", "Mirsid", "Napradea", "Nusfalau", "Pericei", "Plopis", "Poiana Blenchii", "Romanasi", "Rus", "Salatig", "Sanmihaiu Almasului", "Samsud", "Simisna", "Simleu Silvaniei", "Somes-Odorhei", "Surduc", "Treznea", "Turbuta", "Valcau de Jos", "Zalau", "Zimbor"],
                "Satu Mare": ["Acas", "Agris", "Andrid", "Apa", "Ardud", "Barsau", "Batarci", "Beltiug", "Berveni", "Bixad", "Bogdand", "Botiz", "Calinesti-Oas", "Camin", "Carei", "Cauas", "Cehal", "Certeze", "Ciumesti", "Craidorolt", "Crucisor", "Dorolt", "Doba", "Gherta Mica", "Halmeu", "Hodod", "Homoroade", "Lazuri", "Livada", "Mediesu Aurit", "Micula", "Moftin", "Negresti-Oas", "Odoreu", "Orasu Nou", "Paulesti", "Petresti", "Piscolt", "Pir", "Pomi", "Porumbesti", "Ratesti", "Rasinari", "Sanislau", "Santau", "Sarauad", "Sacaseni", "Satmarel", "Satu Mare", "Sauca", "Seini", "Socond", "Tasnad", "Tarna Mare", "Terebesti", "Terebestii Noi", "Tasnad", "Turt", "Turulung", "Turt Bai", "Turulung", "Urziceni", "Valea Vinului", "Vama", "Viile Satu Mare", "Viile Satu Mare", "Vetis"],
                "Sibiu": ["Agnita", "Alma", "Apoldu de Jos", "Atel", "Avrig", "Axente Sever", "Bazna", "Biertan", "Blajel", "Boian", "Bradeni", "Brateiu", "Bruiu", "Carta", "Cartisoara", "Chirpar", "Copsa Mica", "Cristian", "Darlos", "Dumbraveni", "Gura Raului", "Hoghilag", "Iacobeni", "Jina", "Laslea", "Loamnes", "Marsa", "Medias", "Merghindeal", "Micasasa", "Miercurea Sibiului", "Mosna", "Nocrich", "Ocna Sibiului", "Orlat", "Pauca", "Poiana Sibiului", "Porumbacu de Jos", "Rasinari", "Rau Sadului", "Rosia", "Sadu", "Sacadate", "Saliste", "Seica Mare", "Seica Mica", "Selimbar", "Slimnic", "Sibiu", "Sura Mare", "Sura Mica", "Talmaciu", "Tilisca", "Turnu Rosu", "Valea Viilor", "Vurpar"],
                "Suceava": ["Adancata", "Arbore", "Baia", "Balcauti", "Balaceana", "Berchisesti", "Bilca", "Bogdanesti", "Boroaia", "Bosanci", "Botosana", "Brodina", "Brosteni", "Bunesti", "Burla", "Cacica", "Cajvana", "Calafindesti", "Capu Campului", "Campulung Moldovenesc", "Ciocanesti", "Ciprian Porumbescu", "Comanesti", "Cornu Luncii", "Crucea", "Darmanesti", "Dolhasca", "Dolhesti", "Dorna Arini", "Dorna Candrenilor", "Dornesti", "Draguseni", "Dumbraveni", "Falticeni", "Fantana Mare", "Fantanele", "Fratautii Noi", "Fratautii Vechi", "Frumosu", "Fundu Moldovei", "Galanesti", "Gramesti", "Granicesti", "Hantesti", "Horodnic de Jos", "Horodnic de Sus", "Horodniceni", "Iacobeni", "Ilisesti", "Ipotesti", "Izvoarele Sucevei", "Liteni", "Marginea", "Malini", "Manastirea Humorului", "Mitocu Dragomirnei", "Moara", "Moldovita", "Ostra", "Paltinoasa", "Patrauti", "Panaci", "Poiana Stampei", "Preutesti", "Putna", "Radaseni", "Radauti", "Rasca", "Sadova", "Salcea", "Satu Mare", "Saru Dornei", "Scheia", "Serbauti", "Siminicea", "Siret", "Slatina", "Straja", "Stulpicani", "Suceava", "Sucevita", "Udesti", "Ulma", "Vadu Moldovei", "Vama", "Vatra Dornei", "Vatra Moldovitei", "Vicovu de Jos", "Vicovu de Sus", "Voitinel", "Volovat", "Vulturesti", "Zamostea", "Zvoristea"],
                "Teleorman": ["Alexandria", "Babanesti", "Babaita", "Baduleasa", "Balaci", "Beuca", "Blejesti", "Bogdana", "Botoroaga", "Bragadiru", "Branceni", "Buzescu", "Calinesti", "Calmatuiu", "Calmatuiu de Sus", "Cernetu", "Ciolanesti", "Ciuperceni", "Contesti", "Cosmesti", "Crangu", "Crangeni", "Crevenicu", "Didesti", "Dobrotesti", "Dracea", "Dracsenei", "Draghiceni", "Draganesti de Vede", "Draganesti-Vlasca", "Fantanele", "Frasinet", "Frumoasa", "Furculesti", "Galateni", "Gardesti", "Gavanesti", "Gheorghe Doja", "Giuvarasti", "Gogosari", "Gratia", "Grozavesti", "Izvoarele", "Laceni", "Lita", "Lunca", "Magura", "Mavrodin", "Maldaeni", "Merisani", "Mosteni", "Navodari", "Necsesti", "Nenciulesti", "Olteni", "Orbeasca", "Piatra", "Pietrosani", "Plosca", "Poroschia", "Purani", "Radoiesti", "Rasmiresti", "Rosiori de Vede", "Salcia", "Saceni", "Sardanu", "Scrioastea", "Sfintesti", "Seaca", "Segarcea Vale", "Silistea", "Sarbeni", "Saveni", "Smardioasa", "Stejaru", "Suhaia", "Storobaneasa", "Talpa", "Tiganesti", "Tatarastii de Jos", "Tatarastii de Sus", "Teleormanu", "Troianul", "Trivalea-Mosteni", "Turnu Magurele", "Uda-Clocociov", "Ungheni", "Vartoapele", "Vedea", "Vitanesti", "Zimnicea", "Zambreasca"],
                "Timis": ["Bara", "Beba Veche", "Becicherecu Mic", "Belint", "Bethausen", "Biled", "Birda", "Barna", "Bocsa", "Boldur", "Brestovat", "Buzias", "Carpinis", "Cenad", "Checea", "Cheveresu Mare", "Ciacova", "Cenei", "Cenad", "Ciacova", "Cheveresu Mare", "Comlosu Mare", "Costeiu", "Crai Nou", "Crivobara", "Curtea", "Darova", "Denta", "Deta", "Dudestii Vechi", "Dumbrava", "Dumbravita", "Faget", "Fibis", "Foeni", "Gavojdia", "Ghilad", "Ghiroda", "Giarmata", "Giera", "Giulvaz", "Gottlob", "Gataia", "Iecea Mare", "Jamu Mare", "Jebel", "Jimbolia", "Lenauheim", "Liebling", "Livezile", "Lovrin", "Margina", "Masloc", "Moravita", "Mosnita Noua", "Nadrag", "Nitchidorf", "Ortisoara", "Otelec", "Peciu Nou", "Periam", "Pesac", "Pischia", "Racovita", "Remetea Mare", "Recas", "Sacalaz", "Sacospetru Mare", "Sag", "Sanandrei", "Sannicolau Mare", "Sanpetru Mare", "Sanmihaiu Roman", "Sannicolau Mare", "Satchinez", "Sampetru Mare", "Sanpetru Mic", "Sacosu Turcesc", "Stamora Moravita", "Teremia Mare", "Timisoara", "Tomesti", "Tomnatic", "Topolovatu Mare", "Tormac", "Traian Vuia", "Uivar", "Valcani", "Varias", "Victor Vlad Delamarina"],
                "Tulcea": ["Babadag", "Baia", "Bestepe", "C.A. Rosetti", "Casla", "Carlogani", "Calarasi", "Ceamurlia de Jos", "Ceatalchioi", "Cernesti", "Chilia Veche", "Ciucurova", "Crisan", "Dorobantu", "Frecatesti", "Greci", "Hamcearca", "Horia", "I.C. Bratianu", "Izvoarele", "Jijila", "Jurilovca", "Luncavita", "Macin", "Magurele", "Mahmudia", "Maliuc", "Mihai Bravu", "Mihail Kogalniceanu", "Mineri", "Murighiol", "Nalbant", "Niculitel", "Nufaru", "Ostrov", "Pardina", "Peceneaga", "Peregreni", "Plopu", "Sarichioi", "Sfantu Gheorghe", "Slava Cercheza", "Slava Rusa", "Smardan", "Sulina", "Turcoaia", "Valea Nucarilor", "Valea Teilor", "Vacareni", "Valeni"],
                "Valcea": ["Alunu", "Amarasti", "Babeni", "Balcesti", "Baile Govora", "Baile Olanesti", "Barbatesti", "Berislavesti", "Boisoara", "Brezoi", "Budesti", "Bujoreni", "Bunesti", "Calimanesti", "Cernisoara", "Copaceni", "Costesti", "Creteni", "Daesti", "Danicei", "Dragasani", "Fartatesti", "Francesti", "Galicea", "Golesti", "Gradistea", "Gusoeni", "Horezu", "Ionesti", "Lacusteni", "Lapusata", "Ladesti", "Lalosu", "Livezi", "Lungesti", "Maciuca", "Madulari", "Malaia", "Mateesti", "Mihaesti", "Milostea", "Muereasca", "Nicolae Balcescu", "Ocnele Mari", "Olanu", "Orlesti", "Pausesti", "Pausesti-Maglasi", "Perisani", "Pesceana", "Pietrari", "Popesti", "Prundeni", "Racovita", "Rosiile", "Runcu", "Salatrucel", "Scundu", "Sirineasa", "Stefanesti", "Stoenesti", "Stroesti", "Sutesti", "Tetoiu", "Titesti", "Tomsani", "Vaideeni", "Valcea", "Valea Mare", "Vladesti", "Voineasa", "Zatreni"],
                "Vaslui": ["Albesti", "Alexandru Vlahuta", "Arsura", "Bacani", "Bacesti", "Balteni", "Baltatesti", "Banca", "Baltilesti", "Berezeni", "Blagesti", "Bogdanesti", "Bogdanita", "Botesti", "Bradesti", "Brahasoaia", "Brodoc", "Buda", "Calarasi", "Calugareni", "Cantalaresti", "Ciocani", "Codaesti", "Coroiesti", "Costesti", "Cosesti", "Cretesti", "Danesti", "Deleni", "Delesti", "Dodesti", "Dragomiresti", "Dranceni", "Dumesti", "Epuresti", "Falciu", "Feresti", "Gagesti", "Gherghesti", "Gherghestii Noi", "Ghireasca", "Grivita", "Gulioaia", "Gura Alba", "Hoceni", "Horcani", "Hreasca", "Iana", "Ibanesti", "Igesti", "Ivanesti", "Laza", "Lipovat", "Malusteni", "Miclesti", "Murgeni", "Negresti", "Odaia Bogdana", "Oltenesti", "Orgoiesti", "Osesti", "Padureni", "Perieni", "Pochidia", "Pogana", "Poienesti", "Poienestii de Sus", "Pogonesti", "Puiesti", "Puscasi", "Rafaila", "Rebricea", "Rosiesti", "Stefan cel Mare", "Stanilesti", "Suletea", "Tanacu", "Tatarani", "Tutova", "Vaslui", "Vetrisoaia", "Vinderei", "Voinesti", "Vulturesti", "Zapodeni", "Zorleni"],
                "Vrancea": ["Andreiasu de Jos", "Balesti", "Barsesti", "Biliesti", "Birsesti", "Bolotesti", "Bordesti", "Bordestii de Jos", "Bradanesti", "Brosteni", "Campuri", "Chiojdeni", "Chiojdenii Mari", "Chiojdenii Mici", "Ciorasti", "Cotesti", "Dumbraveni", "Dumitresti", "Fitionesti", "Focsani", "Garoafa", "Golesti", "Gugesti", "Homocea", "Jaristea", "Jitia", "Jitia de Jos", "Jitia de Sus", "Lepsa", "Marasesti", "Milcovul", "Movilita", "Naruja", "Narujesti", "Negrilesti", "Nereju", "Nistoresti", "Obrejita", "Panciu", "Paulesti", "Paunesti", "Ploscuteni", "Poiana Cristei", "Popesti", "Reghiu", "Ruginesti", "Sihlea", "Slobozia Bradului", "Slobozia Ciorasti", "Spulber", "Straoane", "Suraia", "Tanasoaia", "Tataranu", "Tifesti", "Tulnici", "Urechesti", "Valea Sarii", "Vidra", "Vintileasca", "Vizantea-Livezi", "Vizantea-Manastireasca", "Vrancioaia"]
            };

            // Funcție pentru a popula dropdown-ul de localități în funcție de județul selectat
            $('#adresa-judet').change(function() {
                const selectedJudet = $(this).val();
                const localitateDropdown = $('#adresa-localitate');

                // Golește dropdown-ul de localități
                localitateDropdown.empty();
                localitateDropdown.append('<option value="" disabled selected>Selectează...</option>');

                // Populează dropdown-ul cu localitățile corespunzătoare
                if (localitati[selectedJudet]) {
                    localitati[selectedJudet].forEach(function(localitate) {
                        localitateDropdown.append('<option value="' + localitate + '">' + localitate + '</option>');
                    });

                    const currentLocalitate = "<?php echo $adresaLocalitate; ?>";
                    localitateDropdown.val(currentLocalitate);
                }
            });

            // Selectează județul și localitatea din baza de date
            const defaultJudet = "<?php echo $adresaJudet; ?>";
            const defaultLocalitate = "<?php echo $adresaLocalitate; ?>";

            $('#adresa-judet').val(defaultJudet).change();
        });
    </script>

    <script>
        document.getElementById('formular-facturare').addEventListener('submit', function(event) {
            event.preventDefault(); // Previne trimiterea formularului

            // Obține valorile din formular
            const contactNume = document.getElementById('contact-nume').value.trim();
            const contactPrenume = document.getElementById('contact-prenume').value.trim();
            const contactTelefon = document.getElementById('contact-telefon').value.trim();
            const adresaStrada = document.getElementById('adresa-strada').value.trim();
            const adresaNumar = document.getElementById('adresa-numar').value.trim();
            const adresaBloc = document.getElementById('adresa-bloc').value.trim();
            const adresaScara = document.getElementById('adresa-scara').value.trim();
            const adresaEtaj = document.getElementById('adresa-etaj').value.trim();
            const adresaApartament = document.getElementById('adresa-apartament').value.trim();
            const adresaJudet = document.getElementById('adresa-judet').value;
            const adresaLocalitate = document.getElementById('adresa-localitate').value;
            const modalitatePlata = document.querySelector('input[name="modalitate_plata"]:checked');

            // Validarea campurilor
            if (!contactNume || !contactPrenume || !contactTelefon || !adresaStrada || !adresaNumar || !adresaBloc || !adresaScara || !adresaEtaj || !adresaApartament || !adresaJudet || !adresaLocalitate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare',
                    text: 'Toate câmpurile sunt obligatorii.'
                });
                return;
            }

            // Validarea numărului de telefon
            if (contactTelefon.length < 7 || contactTelefon.length > 15 || !/^\d+$/.test(contactTelefon)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare',
                    text: 'Formatul numărului de telefon este incorect.'
                });
                return;
            }

            // Validarea modalității de plată
            if (!modalitatePlata) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare',
                    text: 'Trebuie selectată modalitatea de plată.'
                });
                return;
            }

            // Trimiterea formularului
            this.submit();
        });
    </script>

</body>
</html>