<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Code Igniter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style typ="text/css">

    body {
        background-color: #fff;
        margin: 40px;
        font-family: Lucida Grande, Verdana, Sans-serif;
        font-size: 14px;
        color: #4F5155;
    }

    a{
        color: #003399;
        background-color: transparent;
        font-weight: normal;
    }

    h1{
        color: #444;
        background-color: transparent;
        border-bottom: 1px solid #D0D0D0;
        font-size: 16px;
        font-weight: bold;
        margin: 24px 0 2px 0;
        padding: 5px 0 6px 0;
    }
    </style>
</head>
<body>
    <h1>Perkalian!</h1>

    <p>Silahkan masukan data berikut!1!</p>
    <?php echo form_open('hitung/perkalian');?>
    <?php echo form_input('v1', $v1);?> x 
    <?php echo form_open('v2', $v2);?> <br/>

    <?php echo form_submit('submit', 'Hitung!!');?>
    <?php echo form_close();?><br>
    Hasil : <?php echo $hasil; ?>

    <p><br/>Page rendered in {elapsed_time} seconds</p>
</body>
</html>