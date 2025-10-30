<?php
// ----------------------
// CSS for EAN-13 barcode
// ----------------------
echo <<<EOT
<style>
.bar {
  display:inline-block;
  height:80px;
  margin:0;
  padding:0;
}
.black { background:black; }
.white { background:white; }
.ean-text {
  font-family: Arial, sans-serif;
  font-size: 14px;
  letter-spacing: 2px;
  margin-top: 5px;
}
form { margin-bottom:20px; }
</style>
EOT;

// ----------------------
// EAN-13 encoding tables
// ----------------------

// Left-hand odd/even encoding patterns
$L = [
    '0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
    '5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'
];
$G = [
    '0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
    '5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'
];
$R = [
    '0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
    '5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100'
];

// Parity table for first digit
$parity = [
    '0'=>['L','L','L','L','L','L'],
    '1'=>['L','L','G','L','G','G'],
    '2'=>['L','L','G','G','L','G'],
    '3'=>['L','L','G','G','G','L'],
    '4'=>['L','G','L','L','G','G'],
    '5'=>['L','G','G','L','L','G'],
    '6'=>['L','G','G','G','L','L'],
    '7'=>['L','G','L','G','L','G'],
    '8'=>['L','G','L','G','G','L'],
    '9'=>['L','G','G','L','G','L']
];

// ----------------------
// Function to draw EAN-13 barcode
// ----------------------
function drawEAN13($digits){
    global $L, $G, $R, $parity;

    if(strlen($digits) != 13 || !ctype_digit($digits)) return "Enter 13 digits only.";

    $html = '';

    // Start guard
    $html .= "<div class='bar black' style='width:2px;'></div>";
    $html .= "<div class='bar white' style='width:2px;'></div>";
    $html .= "<div class='bar black' style='width:2px;'></div>";

    $first = $digits[0];
    $leftDigits = substr($digits,1,6);
    $rightDigits = substr($digits,7,6);

    $parityPattern = $parity[$first];

    // Left-hand digits
    for($i=0;$i<6;$i++){
        $d = $leftDigits[$i];
        $code = $parityPattern[$i]=='L' ? $L[$d] : $G[$d];
        for($j=0;$j<strlen($code);$j++){
            $color = $code[$j]=='1' ? 'black':'white';
            $html .= "<div class='bar $color' style='width:2px;'></div>";
        }
    }

    // Center guard
    $html .= "<div class='bar white' style='width:2px;'></div>";
    $html .= "<div class='bar black' style='width:2px;'></div>";
    $html .= "<div class='bar white' style='width:2px;'></div>";
    $html .= "<div class='bar black' style='width:2px;'></div>";

    // Right-hand digits
    for($i=0;$i<6;$i++){
        $d = $rightDigits[$i];
        $code = $R[$d];
        for($j=0;$j<strlen($code);$j++){
            $color = $code[$j]=='1' ? 'black':'white';
            $html .= "<div class='bar $color' style='width:2px;'></div>";
        }
    }

    // End guard
    $html .= "<div class='bar black' style='width:2px;'></div>";
    $html .= "<div class='bar white' style='width:2px;'></div>";
    $html .= "<div class='bar black' style='width:2px;'></div>";

    // Human-readable text
    $html .= "<div class='ean-text'>{$digits[0]} ".substr($digits,1,6)." ".substr($digits,7,6)."</div>";

    return $html;
}
