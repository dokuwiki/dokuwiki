<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Smileys</title>

    <style>
        body {
            background-color: #ccc;
            font-family: Arial;
        }

        .box {
            width: 200px;
            float: left;
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
$smi_list = '';
foreach (glob('*.gif') as $img) {
    $smi_list .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
}
if(is_dir('local')) {
    $smi_list .= '<hr />';
    foreach (glob('local/*.gif') as $img) {
        $smi_list .= '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" /> ';
    }
}

echo '<div class="white box">
'.$smi_list.'
</div>

<div class="black box">
'.$smi_list;
?>
</div>

</body>
</html>
