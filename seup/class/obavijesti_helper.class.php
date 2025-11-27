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

class Obavijesti_Helper
{
    const CENTRAL_SERVER_URL = 'https://dokumentacija.8core.hr/api/notifications.json';
    const CACHE_DURATION = 300;

    public static function dohvatiSCentralnogServera($od_datuma = null)
    {
        $url = self::CENTRAL_SERVER_URL;

        if ($od_datuma) {
            $url .= '?since=' . urlencode($od_datuma);
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'SEUP-Notification-Client/1.0'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("SEUP: Neuspješno dohvaćanje obavijesti s centralnog servera: " . $url);
            return false;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("SEUP: JSON parse greška: " . json_last_error_msg());
            return false;
        }

        return isset($data['notifications']) ? $data['notifications'] : [];
    }

    public static function spremiUEvidenciju($db, $notification_uuid, $fk_user)
    {
        $sql = "INSERT IGNORE INTO " . MAIN_DB_PREFIX . "a_evidencija_obavijesti (
                    notification_uuid,
                    fk_user,
                    datum_prvog_prikaza
                ) VALUES (
                    '" . $db->escape($notification_uuid) . "',
                    " . (int)$fk_user . ",
                    NOW()
                )";

        $resql = $db->query($sql);
        return (bool)$resql;
    }

    public static function dohvatiBrojNeprocitanih($db, $fk_user)
    {
        $obavijesti = self::dohvatiSCentralnogServera();

        if ($obavijesti === false || empty($obavijesti)) {
            return 0;
        }

        $uuids = array_map(function($n) { return "'" . $n['id'] . "'"; }, $obavijesti);
        $uuids_str = implode(',', $uuids);

        $sql = "SELECT COUNT(*) as broj
                FROM (SELECT '" . implode("' UNION SELECT '", array_column($obavijesti, 'id')) . "') AS sve_obavijesti(uuid)
                LEFT JOIN " . MAIN_DB_PREFIX . "a_evidencija_obavijesti ev
                    ON ev.notification_uuid = sve_obavijesti.uuid
                    AND ev.fk_user = " . (int)$fk_user . "
                WHERE ev.procitano = 0 OR ev.rowid IS NULL";

        $resql = $db->query($sql);

        if ($resql) {
            $obj = $db->fetch_object($resql);
            return (int)$obj->broj;
        }

        return 0;
    }

    public static function oznaciKaoProcitano($db, $notification_uuid, $fk_user)
    {
        self::spremiUEvidenciju($db, $notification_uuid, $fk_user);

        $sql = "UPDATE " . MAIN_DB_PREFIX . "a_evidencija_obavijesti
                SET procitano = 1,
                    datum_citanja = NOW()
                WHERE notification_uuid = '" . $db->escape($notification_uuid) . "'
                AND fk_user = " . (int)$fk_user;

        $resql = $db->query($sql);
        return (bool)$resql;
    }

    public static function odbaci($db, $notification_uuid, $fk_user)
    {
        self::spremiUEvidenciju($db, $notification_uuid, $fk_user);

        $sql = "UPDATE " . MAIN_DB_PREFIX . "a_evidencija_obavijesti
                SET odbaceno = 1,
                    datum_odbacivanja = NOW()
                WHERE notification_uuid = '" . $db->escape($notification_uuid) . "'
                AND fk_user = " . (int)$fk_user;

        $resql = $db->query($sql);
        return (bool)$resql;
    }

    public static function dohvatiSveZaKorisnika($db, $fk_user, $limit = 20)
    {
        $obavijesti = self::dohvatiSCentralnogServera();

        if ($obavijesti === false) {
            return [];
        }

        $rezultat = [];

        foreach ($obavijesti as $obavijest) {
            $sql = "SELECT procitano, odbaceno, datum_citanja, datum_odbacivanja, datum_prvog_prikaza
                    FROM " . MAIN_DB_PREFIX . "a_evidencija_obavijesti
                    WHERE notification_uuid = '" . $db->escape($obavijest['id']) . "'
                    AND fk_user = " . (int)$fk_user;

            $resql = $db->query($sql);

            if ($resql && $db->num_rows($resql) > 0) {
                $tracking = $db->fetch_object($resql);

                if ($tracking->odbaceno == 1) {
                    continue;
                }

                $obavijest['procitano'] = (bool)$tracking->procitano;
                $obavijest['datum_citanja'] = $tracking->datum_citanja;
                $obavijest['datum_prvog_prikaza'] = $tracking->datum_prvog_prikaza;
            } else {
                $obavijest['procitano'] = false;
                $obavijest['datum_citanja'] = null;
                $obavijest['datum_prvog_prikaza'] = null;
            }

            $rezultat[] = $obavijest;

            if (count($rezultat) >= $limit) {
                break;
            }
        }

        return $rezultat;
    }

    public static function formatirajVrijeme($datetime)
    {
        if (empty($datetime)) {
            return '';
        }

        $now = new DateTime();
        $created = new DateTime($datetime);
        $diff = $now->diff($created);

        if ($diff->days == 0) {
            if ($diff->h == 0) {
                return 'prije ' . $diff->i . ' min';
            }
            return 'prije ' . $diff->h . 'h';
        } elseif ($diff->days == 1) {
            return 'jučer';
        } elseif ($diff->days < 7) {
            return 'prije ' . $diff->days . ' dana';
        } else {
            return $created->format('d.m.Y');
        }
    }

    public static function getTipIkona($tip)
    {
        $ikone = [
            'vazno' => '🔴',
            'upozorenje' => '⚠️',
            'info' => '🔵',
            'uspjeh' => '✅',
            'novost' => '📢'
        ];

        return isset($ikone[$tip]) ? $ikone[$tip] : '📬';
    }
}
