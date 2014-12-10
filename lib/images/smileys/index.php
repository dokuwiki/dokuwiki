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

<div class="white box">
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
echo $list;
?>
</div>

<div class="black box">
<?php
echo $list;
?>
</div>

</body>
</html>
