<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Filetype icons</title>

    <style>
        body {
            background-color: #fff;
            font-family: Arial;
        }
    </style>

</head>

<body>
<?php
foreach (glob('*.svg') as $img) {
    echo '<img src="'.$img.'" alt="'.$img.'" width="32" height="32" title="'.$img.'" /> ';
}
?>
</body>
</html>
