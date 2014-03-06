<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>filetype icons</title>

    <style type="text/css">
        body {
            background-color: #ccc;
            font-family: Arial;
        }

        .box {
            width: 200px;
            float:left;
            padding: 0.5em;
            margin: 0;
        }

        .white {
            background-color: #fff;
        }

        .black {
            background-color: #000;
        }
    </style>

</head>
<body>

<div class="white box">
<?php
foreach (glob('*.png') as $img) {
    echo '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
?>
</div>

<div class="black box">
<?php
foreach (glob('*.png') as $img) {
    echo '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
?>
</div>

<br style="clear: left" />

<div class="white box">
    <?php
    foreach (glob('32x32/*.png') as $img) {
        echo '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
    }
    ?>
</div>

<div class="black box">
    <?php
    foreach (glob('32x32/*.png') as $img) {
        echo '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
    }
    ?>
</div>


</body>
</html>
