<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Smileys</title>

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
foreach (glob('*.gif') as $img) {
    $list .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
if(is_dir('local')) {
    $list .= '<hr />';
    foreach (glob('local/*.gif') as $img) {
        $list .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
    }
}
echo '<div class="white box">
'.$list.'
</div>

<div class="black box">
'.$list.'
</div>';?>

</body>
</html>
