<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"
 lang="en" dir="ltr">
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

</body>
</html>
