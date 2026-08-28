<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "verkkokauppa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Yhteys epäonnistui: " . $conn->connect_error);
}

$allowed = '/^[A-Za-z0-9 åäöÅÄÖ+\-_]+$/u';

foreach ($_POST as $key => $value) {
    if ($key === 'show' || $key === 'kassa' || $key === 'osto') continue;
    if (!preg_match($allowed, $value)) {
        die("Yritä uudelleen.");
    }
}

$osto = $_POST['osto'] ?? null;
$kassa = $_POST['kassa'] ?? null;
$toiminto = $_POST['toiminto'] ?? null;
$muutto = $_POST['muutto'] ?? null;
$show = $_POST['show'] ?? null;
$haku = $_POST['haku'] ?? null;
$vero = $_POST['vero'] ?? null;
$koti = $_POST['koti'] ?? null;
$hinta = $_POST['hinta'] ?? null;
$tr_id = $_POST['tr_id'] ?? null;
$l_tr = $_POST['l_tr'] ?? null;
$m_tr =$_POST['m_tr'] ?? null;
$l_t = $_POST['l_t'] ?? null;
$m_t = $_POST['m_t'] ?? null;
$ean = $_POST['ean'] ?? null;
$tr = $_POST['tr'] ?? null;
$t = $_POST['t'] ?? null;
$trs = array();
$verot = array();

$results = $conn->query("SELECT * FROM tuoteryhmat;");
foreach ($results as $row) {
    $trs[$row['id']] = $row['nimi'];
}

$results = $conn->query("SELECT * FROM vero;");
foreach ($results as $row) {
    $verot[$row['vero_id']] = $row['prosentti'];
}

if ($toiminto == 'l_t') {
    echo "<form id='lT' method='post'>";
    label("l_t", "Tuotteen nimi");
    label("ean", "Tuotteen EAN-koodi", "", "EAN...");
    label("hinta", "Tuotteen hinta:", "", "Hinta...");
    select("tr", "Valitse tuoteryhmä:", "SELECT * FROM tuoteryhmat;", $conn);
    echo "<button type='button' onclick='sendForm(\"lT\")'>Lisää</button>
    </form>";
    takaisin();
} else if ($toiminto == 'l_tr') {
    echo "<form id='lTR' method='post'>";
    label("l_tr", "Tuoteryhmän nimi:");
    select("vero", "Valitse verokanta", "SELECT * FROM vero;", $conn);
    echo "<button type='button' onclick='sendForm(\"lTR\")'>Lisää</button>
    </form>";
    takaisin();
} else if ($toiminto == 'm_t') {
    echo "<form id='mT' method='post'>";
    select("m_t", "Mitä tuotetta haluat muuttaa:", "SELECT * FROM tuotteet;", $conn);
    label("t", "Uusi nimi:");
    label("ean", "Uusi EAN-koodi:", "", "EAN...");
    label("hinta", "Uusi hinta:", "", "Hinta...");
    select("tr", "Uusi tuoteryhmä:", "SELECT * FROM tuoteryhmat;", $conn);
    echo "<button type='button' onclick='sendForm(\"mT\")'>Muuta</button>
    </form>";
    takaisin();
} else if ($toiminto == 'm_tr') {
    echo "<form id='mTR' method='post'";
    select("tr", "Mitä tuoteryhmää haluat muuttaa", "SELECT * FROM tuoteryhmat;", $conn);
    label("m_tr", "Uusi nimi:");
    select("vero", "Valitse uusi verokanta:", "SELECT * FROM vero;", $conn);
    echo "<button type='button' onclick='sendForm(\"mTR\")'>Muuta</button>
    </form>";
    takaisin();
} else if ($toiminto == 't_t') {
    lista($trs, $conn, "SELECT * FROM tuotteet LEFT JOIN varasto ON tuotteet.id = varasto.tuotteet_id ORDER BY tuotteet.id ASC");
    echo "<br>";
    takaisin();
} else if ($toiminto == 't_tr') {
    $results = $conn->query("SELECT * FROM tuoteryhmat;");
    foreach ($results as $row) {
        $id = $row['id'];
        $nimi = $row['nimi'];
        echo $nimi;
        echo "<button onclick='showData(\"tr_id=$id\")'>Näytä $nimi</button>";
    }
    takaisin();
}

if ($l_tr) {
    $conn->query("INSERT INTO tuoteryhmat(`nimi`,`vero_id`) VALUES ('$l_tr', $vero);");
    echo "Uusi tuoteryhmä: " . $l_tr . " lisätty!";
    takaisin();
}
if ($l_t) {
    $conn->query("INSERT INTO tuotteet(`tuoteryhma_id`,`nimi`, `hinta`, `ean`) VALUES ($tr,'$l_t',$hinta,$ean);");
    echo "Uusi tuote: " . $l_t . " lisätty tuoteryhmään: " . $trs[$tr] . "!";
    takaisin();
}
if ($m_t) {
    $conn->query("UPDATE tuotteet SET tuoteryhma_id = $tr, nimi = '$t', hinta = $hinta, ean = $ean WHERE id = $m_t;");
    echo "Tuotteen uusi nimi on: " . $t . " ja uusi tuoteryhmä on: " . $trs[$tr] . "!";
    takaisin();
}
if ($m_tr) {
    $conn->query("UPDATE tuoteryhmat SET nimi = '$m_tr', vero_id = $vero WHERE id = $tr;");
    echo "Tuoteryhmän uusi nimi on: " . $m_tr . "!";
    takaisin();
}
if ($tr_id) {
    lista($trs, $conn, "SELECT * FROM tuotteet LEFT JOIN varasto ON tuotteet.id = varasto.tuotteet_id WHERE tuotteet.tuoteryhma_id = $tr_id ORDER BY tuotteet.id ASC");
    echo "<br>";
    takaisin();
}

if ($muutto) {
    $muutto = explode("_", $muutto);
    echo "<form id='muuta' method='post'>";
    echo "<input type='hidden' name='m_t' value='$muutto[1]'";
    label("t", "Uusi nimi:", $muutto[0]);
    label("ean", "Uusi EAN-koodi", $muutto[3], "EAN...");
    label("hinta", "Uusi hinta:", $muutto[4], "Hinta...");
    select("tr", "Uusi tuoteryhmä:", "SELECT * FROM tuoteryhmat;", $conn, $muutto[2]);
    echo "<button type='button' onclick='sendForm(\"muuta\")'>Muuta tuotetta</button>
    </form>";
    takaisin();
}

if ($haku) {
    $haku_sana = strtolower($haku);
    $tuotteet = array();
    $hinta = array();
    $tuoteryhma = array();
    $kuva = array();
    $varastossa = array();
    $ids = array();
    $results = $conn->query("SELECT * FROM tuotteet LEFT JOIN varasto ON tuotteet.id = varasto.tuotteet_id;");
    foreach ($results as $row) {
        $nimi = strtolower($row['nimi']);
        $ean = $row['ean'];
        if (str_contains($nimi, $haku_sana) || str_contains($ean, $haku_sana)) {
            $tuotteet[] = $row['nimi'];
            $hinta[] = $row['hinta'];
            $tuoteryhma[] = $row['tuoteryhma_id'];
            $kuva[] = $row['kuva'];
            $varastossa[] = $row['varastossa'];
            $ids[] = $row['id'];
        }
    }
    if (count($tuotteet) < 1) {
        echo "Tuotteita ei löytynyt hakusanalla: " . $haku;
    } else {
        tuotteet($tuotteet, $hinta, $tuoteryhma, $kuva, $verot, $varastossa, $ids, $conn);
    }
}

if ($koti) {
    $tuotteet = array();
    $hinta = array();
    $tuoteryhma = array();
    $kuva = array();
    $varastossa = array();
    $ids = array();
    $results = $conn->query("SELECT * FROM tuotteet LEFT JOIN varasto ON tuotteet.id = varasto.tuotteet_id;");
    foreach ($results as $row) {
        $tuotteet[] = $row['nimi'];
        $hinta[] = $row['hinta'];
        $tuoteryhma[] = $row['tuoteryhma_id'];
        $kuva[] = $row['kuva'];
        $varastossa[] = $row['varastossa'];
        $ids[] = $row['id'];
    }
    tuotteet($tuotteet, $hinta, $tuoteryhma, $kuva, $verot, $varastossa, $ids, $conn);
}

if ($show) {
    lista($trs, $conn, $show);
    echo "<br>";
    takaisin();
}

if ($kassa) {
    header('Content-Type: application/json');
    $kassa = json_decode($kassa, true);

    $id = $kassa[0];
    $maara = $kassa[1];
    
    if (is_numeric($maara)) {
        $results = $conn->query("SELECT * FROM tuotteet LEFT JOIN varasto ON tuotteet.id = varasto.tuotteet_id WHERE tuotteet.id = $id;");
        foreach ($results as $row) {
            $varastossa = $row['varastossa'];
            $nimi = $row['nimi'];
            $hinta = $row['hinta'];
            $tr_id = $row['tuoteryhma_id'];
        }
    }

    if ($varastossa < $maara) {
        echo json_encode([
            'nimi' => $nimi,
            'varastossa' => false
        ]);
    } else {
        $results = $conn->query("SELECT * FROM tuoteryhmat LEFT JOIN vero ON tuoteryhmat.vero_id = vero.vero_id WHERE tuoteryhmat.id = $tr_id;");
        foreach ($results as $row) {
            $prosentti = $row['prosentti'];
        }

        $results = $conn->query("SELECT * FROM tuoteryhmat LEFT JOIN myynti ON tuoteryhmat.myynti_id = myynti.myynti_id WHERE tuoteryhmat.id = $tr_id;");
        foreach ($results as $row) {
            $myynti = $row['myynti'];
        }
        
        $hinta = round($hinta + ($hinta * ($prosentti / 100)), 2);
        if ($myynti === 'kg') {
            $loppu_hinta = round($hinta * $maara / 1000, 2);
        } else {
            $loppu_hinta = round($hinta * $maara, 2);
        }

        echo json_encode([
            'id' => $id,
            'nimi' => $nimi,
            'maara' => $maara,
            'varastossa' => true,
            'myynti' => $myynti,
            'hinta' => $hinta,
            'loppu_hinta' => $loppu_hinta
        ]);
    }
}

if ($osto) {
    header('Content-Type: application/json');
    $osto = json_decode($osto, true);

    $id = $osto[0];
    $maara = $osto[1];

    $conn->query("UPDATE varasto SET varastossa = (SELECT varastossa FROM varasto WHERE tuotteet_id = $id) - $maara WHERE tuotteet_id = $id;");
}

function select($name, $label, $sql, $conn, $value = "") {
    $results = $conn->query($sql);
    echo "<label for='$name'>$label</label>
        <select name='$name'>";
    foreach ($results as $row) {
        $id = $row['id'];
        $nimi = $row['nimi'];
        $selected = ($id == $value) ? "selected" : "";
        echo "<option value='$id' $selected>$nimi</option>";
    }
    echo "</select><br>";
}

function label($name, $teksti, $value = "", $placeholder = "Nimi...") {
    echo "<label for='$name'>$teksti</label>
    <input type='text' name='$name' value='$value' placeholder='$placeholder' pattern='[A-Za-z0-9 åäöÅÄÖ+\-]+'><br>";
}

function takaisin() {
    echo "<button onclick='hallinta()'>Takaisin</button>";
}

function muuta($tuote, $nimi, $id, $tr_id, $ean, $hinta) {
    $value = $tuote . "_" . $id . "_" . $tr_id . "_" . $ean . "_" . $hinta;
    echo "<button onclick='showData(\"$nimi=$value\")'>Muuta tuotetta</button>";
}

function tuotteet($tuotteet, $hinta, $tuoteryhma, $kuva, $verot, $varastossa, $id, $conn) {
    $count = 0;
    $prosentti = array();
    $myynti = array();

    $results = $conn->query("SELECT * FROM tuoteryhmat LEFT JOIN myynti ON tuoteryhmat.myynti_id = myynti.myynti_id;");
    foreach ($results as $row) {
        $prosentti[$row['id']] = $verot[$row['vero_id']];
        $myynti[$row['id']] = $row['myynti'];
    }

    echo "<div class='row'>";
    foreach ($tuotteet as $tuote) {
        $loppu_hinta = round($hinta[$count] + ($hinta[$count] * ($prosentti[$tuoteryhma[$count]] / 100)),2);
        $myyntitapa = $myynti[$tuoteryhma[$count]];
        $vero = $prosentti[$tuoteryhma[$count]];
        echo "<div class='box' onclick='showModal(\"$kuva[$count]\", \"$id[$count]\", \"$tuote\", \"$loppu_hinta\", \"$hinta[$count]\", \"$myyntitapa\", \"$vero\", \"$varastossa[$count]\")'>" . "<img src='$kuva[$count]'>" . "<br>" . "Tuotteen nimi: " . $tuote . "<br>" . "Tuotteen hinta: " . $loppu_hinta . "€/" . $myyntitapa . "<br>";
        echo "</div>";
        $count++;
        if ($count % 3 === 0) {
            echo "</div><div class='row'>";
        }
    }
    echo "</div>";
}

function lista($trs, $conn, $sql) {
    $order = "";
    $results = $conn->query($sql);
    $id = array();
    $nimi = array();
    $tr_ids = array();
    $ean  = array();
    $hinta = array();
    $varastossa = array();
    foreach ($results as $row) {
        $id[] = $row['id'];
        $nimi[] = $row['nimi'];
        $tr_ids[] = $row['tuoteryhma_id'];
        $ean[] = $row['ean'];
        $hinta[] = $row['hinta'];
        $varastossa[] = $row['varastossa'];
    }
    $parts = explode(" ORDER", $sql);
    $sql = $parts[0];
    $parts = explode(" ", $parts[1]);
    foreach ($parts as $part) {
        if ($part == 'ASC') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
    }
    
    echo "<table>
        <tr>
            <th onclick='showData(\"show=$sql ORDER BY tuotteet.id $order\")'>ID</th>
            <th onclick='showData(\"show=$sql ORDER BY nimi $order\")'>Nimi</th>
            <th onclick='showData(\"show=$sql ORDER BY tuoteryhma_id $order\")'>Tuoteryhmä</th>
            <th onclick='showData(\"show=$sql ORDER BY ean $order\")'>EAN-koodi</th>
            <th onclick='showData(\"show=$sql ORDER BY hinta $order\")'>Hinta</th>
            <th onclick='showData(\"show=$sql ORDER BY varastossa $order\")'>Varastossa</th>
            <th>Muuta</th>
        </td>";
    for ($a = 0; $a < count($id); $a++) {
        echo "<tr>
            <td>" . $id[$a] . "</td>
            <td>" . $nimi[$a] . "</td>
            <td>" . $trs[$tr_ids[$a]] . "</td>
            <td>" . $ean[$a] . "</td>
            <td>" . $hinta[$a] . "</td>
            <td>" . $varastossa[$a] . "</td>
            <td>"; 
        muuta($nimi[$a], "muutto", $id[$a], $tr_ids[$a], $ean[$a], $hinta[$a]);
        echo "</td>
        </tr>";
    }
    echo "</table>";
}
?>