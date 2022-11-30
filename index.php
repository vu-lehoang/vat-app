<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Vat Calculator</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="./image/logo_favicon.png>">
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
    $decimal     = ' phẩy ';
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
            $result_money = (($money) * 100) / ($vat + 100);
            $vat_money = ($money) - $result_money;
        }
    }
}

function format_Number($number)
{
    return number_format($number, 0, '.', ',');
}
?>

<body>
    <section class="wrapper">
        <div class="container">
            <h2 class="title">Công cụ tính thuế VAT</h2>
            <form action="" method="POST" id="form">
                <label>Nhập số tiền (đ): </label>
                <input type="text" name="money" value="<?php echo ($money) ? format_Number($money) : null ?>" placeholder="Nhập số tiền (đ)" class="input-number" onkeyup="javascript:this.value=Comma(this.value)">

                <label>Thuế VAT (%):</label>
                <input type="text" name="vat" value="<?php echo ($vat) ? format_Number($vat) : 10 ?>" placeholder="Thuế VAT (%)">

                <div class="form-group">
                    <input type="radio" name="optVat" value="optVat1" <?= ($optVat == 'optVat1') ? 'checked'  : '' ?>>
                    <label>Số tiền chưa có thuế VAT (tính thuế VAT xuôi)</label>
                </div>
                <div class="form-group">
                    <input type="radio" name="optVat" value="optVat2" <?= ($optVat != 'optVat1') ? 'checked'  : '' ?>>
                    <label>Số tiền đã có VAT (tính thuế VAT ngược)</label>
                </div>
                <button type="submit" class="btn-submit" name="submit">Thực hiện</button>
            </form>
            <?php if (isset($_POST["submit"]) && $optVat == 'optVat1') : ?>
                <div class="noti">
                    <p> Số tiền chưa thuế: <?= ($money != '') ? format_Number(round($money)) : null ?></p>
                    <p> Số tiền thuế: <?= ($vat_money != "") ? format_Number(round($vat_money)) : null ?></p>
                    <p> Số tiền sau thuế: <?= ($result_money != "") ? format_Number(round($result_money)) : null ?></p>
                    <span> Bằng chữ:
                        <p class="noti-money"><?= convert_number_to_words(round($result_money)); ?></p>
                    </span>
                </div>
            <?php elseif (isset($_POST["submit"]) && $optVat == 'optVat2') : ?>
                <div class="noti">
                    <p> Số tiền chưa thuế: <?= ($result_money != '') ? format_Number(round($result_money)) : null ?></p>
                    <p> Số tiền thuế: <?= ($vat_money != "") ? format_Number(round($vat_money)) : null ?></p>
                    <p> Số tiền sau thuế: <?= ($money != "") ? format_Number(round($money))  : null ?></p>
                    <span> Bằng chữ:
                        <p class="noti-money"><?= convert_number_to_words(round($money)); ?></p>
                    </span>
                </div>
            <?php endif ?>
        </div>
    </section>
    <footer class="footer">
        <div class="footer-info">Duy trì và thiết kế bởi <a href="https://minhduy.vn"> Minh Duy.vn</a></div>
    </footer>

    <script>
        function Comma(Num) { //function to add commas to textboxes
            Num += '';
            Num = Num.replace(',', '');
            Num = Num.replace('.', '');
            Num = Num.replace('.', '');
            Num = Num.replace(',', '');
            Num = Num.replace('.', '');
            Num = Num.replace('.', '');
            x = Num.split(',');
            x1 = x[0];
            x2 = x.length > 1 ? ',' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1))
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            return x1 + x2;
        }

        // 
    </script>
</body>



</html>
