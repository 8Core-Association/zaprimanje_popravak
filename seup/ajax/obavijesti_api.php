<?php
/**
 * Plaćena licenca
 * (c) 2025 Tomislav Galić <tomislav@8core.hr>
 * Suradnik: Marko Šimunović <marko@8core.hr>
 * Web: https://8core.hr
 * Kontakt: info@8core.hr | Tel: +385 099 851 0717
 * Sva prava pridržana. Ovaj softver je vlasnički i zabranjeno ga je
 * distribuirati ili mijenjati bez izričitog dopuštenja autora.
 */

if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');

$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once __DIR__ . '/../class/obavijesti_helper.class.php';

header('Content-Type: application/json');

$action = GETPOST('action', 'aZ09');

if (!$user->rights->user->user->lire) {
    http_response_code(403);
    echo json_encode(['error' => 'Nemate dozvolu za pristup']);
    exit;
}

switch ($action) {
    case 'dohvati':
        $obavijesti = Obavijesti_Helper::dohvatiSveZaKorisnika($db, $user->id);
        echo json_encode([
            'success' => true,
            'obavijesti' => $obavijesti,
            'ukupno' => count($obavijesti)
        ]);
        break;

    case 'broj_neprocitanih':
        $broj = Obavijesti_Helper::dohvatiBrojNeprocitanih($db, $user->id);
        echo json_encode([
            'success' => true,
            'broj' => $broj
        ]);
        break;

    case 'oznaci_procitano':
        $uuid = GETPOST('uuid', 'alpha');
        if (empty($uuid)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nedostaje UUID']);
            exit;
        }
        $result = Obavijesti_Helper::oznaciKaoProcitano($db, $uuid, $user->id);
        echo json_encode([
            'success' => $result
        ]);
        break;

    case 'odbaci':
        $uuid = GETPOST('uuid', 'alpha');
        if (empty($uuid)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nedostaje UUID']);
            exit;
        }
        $result = Obavijesti_Helper::odbaci($db, $uuid, $user->id);
        echo json_encode([
            'success' => $result
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Nepoznata akcija']);
}
