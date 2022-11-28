<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php
/**
 * checked option 1 
 * Thêm dấu phẩy sau mỗi 4 chữ số
 * tính vat xuôi 
 * số tiền chưa thuế: số tiền nhập vào
 * số tiền thuế: số tiền nhập vào * số thuế
 * số tiền sau thuế : số tiền chưa thế + số tiền thuế
 * 
 * tinh vat nguoc
 * số tiền trước thuế : (số tiền nhập vào * 100 ) / 100 + vat
 * Số tiền thuế: Số tiền nhập vào - số tiền trước thuế
 */

function convert_number_to_words($number)
{

    $hyphen      = ' ';
    $conjunction = '  ';
    $separator   = ' ';
    $negative    = 'âm ';
    $decimal     = ' phẩy';
    $dictionary  = array(
        0                   => 'Không',
        1                   => 'Một',
        2                   => 'Hai',
        3                   => 'Ba',
        4                   => 'Bốn',
        5                   => 'Năm',
        6                   => 'Sáu',
        7                   => 'Bảy',
        8                   => 'Tám',
        9                   => 'Chín',
        10                  => 'Mười',
        11                  => 'Mười một',
        12                  => 'Mười hai',
        13                  => 'Mười ba',
        14                  => 'Mười bốn',
        15                  => 'Mười năm',
        16                  => 'Mười sáu',
        17                  => 'Mười bảy',
        18                  => 'Mười tám',
        19                  => 'Mười chín',
        20                  => 'Hai mươi',
        30                  => 'Ba mươi',
        40                  => 'Bốn mươi',
        50                  => 'Năm mươi',
        60                  => 'Sáu mươi',
        70                  => 'Bảy mươi',
        80                  => 'Tám mươi',
        90                  => 'Chín mươi',
        100                 => 'trăm',
        1000                => 'ngàn',
        1000000             => 'triệu',
        1000000000          => 'tỷ',
        1000000000000       => 'nghìn tỷ',
        1000000000000000    => 'ngàn triệu triệu',
        1000000000000000000 => 'tỷ tỷ'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

$vat = $vat_money = $money = $result_money = "";
$optVat = 'optVat1';
if (isset($_POST['submit'])) {
    $optVat = $_POST['optVat'];
    $vat = filter_var($_POST['vat'], FILTER_SANITIZE_NUMBER_INT);
    if ($optVat == 'optVat1') {

        $money = filter_var($_POST['money'], FILTER_SANITIZE_NUMBER_FLOAT);
        if (is_numeric($money) && is_numeric($vat)) {
            $vat_money = floatval($money) * $vat / 100;
            $result_money = floatval($money) + $vat_money;
        } else {
            $vat_money = 0;
            $result_money = 0;
            $vat = 0;
            $money = 0;
        }
    } else {
        $money = filter_var($_POST['money'], FILTER_SANITIZE_NUMBER_FLOAT);
        if (is_numeric($money) && is_numeric($vat)) {
            $result_money = (float($money) * 100) / ($vat + 100);
            $vat_money = float($money) - $result_money;
        }
    }
}

function format_Number($number)
{
    return number_format($number, 0, '.', ',');
}
?>


<body>
    <form action="" method="POST">
        <input type="text" name="money" value="<?php echo ($money) ? format_Number($money) : null ?>">
        <input type="text" name="vat" value="<?php echo ($vat) ? format_Number($vat) : null ?>">
        <br>
        <input type="radio" name="optVat" value="optVat1" <?= ($optVat == 'optVat1') ? 'checked'  : '' ?>>Số tiền chưa có thuế VAT (tính thuế VAT xuôi)<br>
        <input type="radio" name="optVat" value="optVat2" <?= ($optVat != 'optVat1') ? 'checked'  : '' ?>>Số tiền đã có VAT (tính thuế VAT ngược) <br>
        <button type="submit" name="submit">Thực hiện</button>
    </form>
    <?php if ($optVat == 'optVat1') : ?>
        <p> Số tiền chưa thuế: <?= ($money != '') ? $money : null ?></p>
        <p> Số tiền thuế: <?= ($vat_money != "") ? $vat_money : null ?></p>
        <p> Số tiền sau thuế: <?= ($result_money != "") ? $result_money : null ?></p>
        <p> Bằng chữ: <?= convert_number_to_words($result_money); ?></p>
    <?php elseif ($optVat == 'optVat2') : ?>
        <p> Số tiền chưa thuế: <?= ($result_money != '') ? round($result_money) : null ?></p>
        <p> Số tiền thuế: <?= ($vat_money != "") ? round($vat_money) : null ?></p>
        <p> Số tiền sau thuế: <?= ($money != "") ? round($money)  : null ?></p>
        <p> Bằng chữ: <span><?= convert_number_to_words($money); ?></span></p>
    <?php endif ?>
    <?php
    $abc = convert_number_to_words($money);
    var_dump($abc);
    ?>
</body>



</html>