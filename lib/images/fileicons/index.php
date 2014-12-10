<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Filetype icons</title>

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

<?php
foreach (glob('*.png') as $img) {
    $list .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
foreach (glob('32x32/*.png') as $img) {
    $list32 .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
echo '<div class="white box">
'.$list.'
</div>

<div class="black box">
'.$list.'
</div>

<br style="clear: left" />

<div class="white box">
'.$list32.'
</div>

<div class="black box">
'.$list32.'
</div>';?>


</body>
</html>
