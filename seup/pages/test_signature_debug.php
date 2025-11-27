<?php
/**
 * Debug page for testing signature detection
 * Temporary file for debugging FINA signatures
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/seup/class/digital_signature_detector.class.php';

$pdf_path = '/tmp/cc-agent/60731960/project/E-potpis-11 (1).pdf';

llxHeader('', 'Signature Detection Debug', '');

print '<div class="seup-container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">';
print '<h1>🔍 FINA Signature Detection Debug</h1>';

if (!file_exists($pdf_path)) {
    print '<div class="error">PDF file not found: ' . $pdf_path . '</div>';
    llxFooter();
    exit;
}

print '<div class="info-box" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">';
print '<strong>Testing file:</strong> ' . $pdf_path . '<br>';
print '<strong>File size:</strong> ' . number_format(filesize($pdf_path)) . ' bytes<br>';
print '</div>';

// Read PDF content
$pdfContent = file_get_contents($pdf_path);

print '<h2>Step 1: Check for /Name field</h2>';
print '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">';

$namePos = strpos($pdfContent, '/Name');
if ($namePos !== false) {
    print '✅ /Name found at position: ' . $namePos . '<br>';
    
    $openParen = strpos($pdfContent, '(', $namePos);
    $closeParen = strpos($pdfContent, ')', $openParen);
    
    if ($openParen !== false && $closeParen !== false) {
        $nameData = substr($pdfContent, $openParen + 1, $closeParen - $openParen - 1);
        print '✅ Name data length: ' . strlen($nameData) . ' bytes<br>';
        print '✅ First 4 bytes (hex): ' . bin2hex(substr($nameData, 0, 4)) . '<br>';
        
        $firstByte = ord($nameData[0]);
        $secondByte = ord($nameData[1]);
        print '✅ First byte: 0x' . dechex($firstByte) . '<br>';
        print '✅ Second byte: 0x' . dechex($secondByte) . '<br>';
        
        if ($firstByte === 0xFE && $secondByte === 0xFF) {
            print '✅ UTF-16BE with BOM detected<br>';
            $decoded = mb_convert_encoding($nameData, 'UTF-8', 'UTF-16BE');
            print '<strong style="color: green; font-size: 16px;">✅ Decoded name: ' . htmlspecialchars($decoded) . '</strong><br>';
        } elseif ($firstByte === 0xFF && $secondByte === 0xFE) {
            print '✅ UTF-16LE with BOM detected<br>';
            $decoded = mb_convert_encoding($nameData, 'UTF-8', 'UTF-16LE');
            print '<strong style="color: green; font-size: 16px;">✅ Decoded name: ' . htmlspecialchars($decoded) . '</strong><br>';
        } else {
            print '⚠️ No BOM detected, trying UTF-16BE<br>';
            $decoded = @iconv('UTF-16BE', 'UTF-8//IGNORE', $nameData);
            print '<strong style="color: orange; font-size: 16px;">⚠️ Decoded name: ' . htmlspecialchars($decoded) . '</strong><br>';
        }
    }
} else {
    print '❌ /Name not found<br>';
}
print '</div>';

print '<h2>Step 2: Check for /Contents (Certificate)</h2>';
print '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">';

$contentsPos = strpos($pdfContent, '/Contents');
if ($contentsPos !== false) {
    print '✅ /Contents found at position: ' . $contentsPos . '<br>';
    
    $openBracket = strpos($pdfContent, '<', $contentsPos);
    $closeBracket = strpos($pdfContent, '>', $openBracket);
    
    if ($openBracket !== false && $closeBracket !== false) {
        $sigHex = substr($pdfContent, $openBracket + 1, $closeBracket - $openBracket - 1);
        print '✅ Signature hex length: ' . strlen($sigHex) . ' chars<br>';
        
        $sigBinary = @hex2bin($sigHex);
        if ($sigBinary !== false) {
            print '✅ Binary size: ' . strlen($sigBinary) . ' bytes<br>';
            
            if (strpos($sigBinary, 'Financijska agencija') !== false) {
                print '<strong style="color: green; font-size: 16px;">✅ FINA Issuer: Financijska agencija</strong><br>';
            }
            
            if (strpos($sigBinary, 'Fina RDC 2020') !== false) {
                print '<strong style="color: green; font-size: 16px;">✅ FINA Unit: Fina RDC 2020</strong><br>';
            }
            
            // Serial
            $serialMarker = "\x06\x03\x55\x04\x05";
            $serialPos = strpos($sigBinary, $serialMarker);
            if ($serialPos !== false) {
                $length = ord($sigBinary[$serialPos + 5]);
                $serialData = substr($sigBinary, $serialPos + 6, $length);
                $serial = trim($serialData);
                if (ctype_print($serial)) {
                    print '<strong style="color: green; font-size: 16px;">✅ Serial: ' . htmlspecialchars($serial) . '</strong><br>';
                }
            }
            
            // Country
            $countryMarker = "\x06\x03\x55\x04\x06";
            $countryPos = strpos($sigBinary, $countryMarker);
            if ($countryPos !== false) {
                $length = ord($sigBinary[$countryPos + 5]);
                $countryData = substr($sigBinary, $countryPos + 6, $length);
                $country = preg_replace('/[^A-Z]/i', '', $countryData);
                if (strlen($country) == 2) {
                    print '<strong style="color: green; font-size: 16px;">✅ Country: ' . htmlspecialchars($country) . '</strong><br>';
                }
            }
        }
    }
} else {
    print '❌ /Contents not found<br>';
}
print '</div>';

print '<h2>Step 3: Test Digital_Signature_Detector class</h2>';
print '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">';

$result = Digital_Signature_Detector::detectPDFSignature($pdf_path);

print '<strong>Has signature:</strong> ' . ($result['has_signature'] ? '✅ YES' : '❌ NO') . '<br><br>';

if ($result['has_signature'] && isset($result['signature_info'])) {
    print '<strong>Signature Info:</strong><br>';
    print '<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto;">';
    print_r($result['signature_info']);
    print '</pre>';
    
    print '<br><h3>Badge Preview:</h3>';
    $badge = Digital_Signature_Detector::getSignatureBadge(
        true,
        'valid',
        $result['signature_info']['signer_name'] ?? null,
        $result['signature_info']['signature_date'] ?? null,
        $result['signature_info']
    );
    print $badge;
}

print '</div>';

print '</div>';

llxFooter();
