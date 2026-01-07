<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Insat | Terminar proceso</title>

<head>

<script src="js/jquery.min.js" type="text/javascript"></script> 

<!-- Browser Color - Chrome, Firefox OS, Opera -->
        <meta name="theme-color" content="#000000"> 
        <!-- Browser Color - Windows Phone --> 
        <meta name="msapplication-navbutton-color" content="#000000"> 
        <!-- Browser Color - iOS Safari --> 
        <meta name="apple-mobile-web-app-status-bar-style" content="#000000">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-1415649-20"></script>
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-1415649-20');
</script>

<!-- script que demora el boton de rellamada -->
<script type="text/javascript">
        setTimeout (function(){
        document.getElementById('submitButton').disabled = null;
        },40000);

        var countdownNum = 20;
        incTimer();

        function incTimer(){
        setTimeout (function(){
            if(countdownNum != 0){
            countdownNum--;
            document.getElementById('timeLeft').innerHTML = 'Podrás volver a solicitarla en: ' + countdownNum + ' segundos';
            incTimer();
            } else {
            document.getElementById('timeLeft').innerHTML = 'Clic para volver a solicitar';
            }
        },2000);
        }
</script>


<?php
$nombre = $_GET['nombre'];		
$telefono = $_GET['telefono'];
?>

<style type="text/css">
<!--

.Row {
  	margin: auto;
  	width: 100%;
	max-width: 900px;
    display: table;
    table-layout: fixed; /*Optional*/
    border-spacing: 5px; /*Optional*/
}
.Column {
    display: table-cell;
    background-color: white; /*Optional*/
}

input[type=submit] {
    background-color: #7116D1;
    color: white;
    padding: 6px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    float: center;
}

.Estilo4 {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
}

body {
	text-align: center;
}
p {
	font-size: 14px;
}
p {
	font-size: 24px;
}
p {
	font-family: Tahoma, Geneva, sans-serif;
}
.Estilo2 {
	font-size: 28px;
	font-weight: bold;
}
.Estilo3 {
	font-size: 14px;
	font-style: italic;
	font-family: Arial, Helvetica, sans-serif;
	color: #666666;
}

.Row {
  	margin: auto;
  	width: 100%;
	max-width: 900px;
    display: table;
    table-layout: fixed; /*Optional*/
    border-spacing: 5px; /*Optional*/
}
.Column {
    display: table-cell;
    background-color: white; /*Optional*/
}

input[type=submit] {
    background-color: #3368FF;
    color: white;
    padding: 6px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    float: center;
}

body {
	text-align: center;
}
p {
	font-size: 14px;
}
p {
	font-size: 24px;
}
p {
	font-family: Tahoma, Geneva, sans-serif;
}
.Estilo2 {
	font-size: 28px;
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
}
.Estilo3 {
	font-size: 14px;
	font-style: italic;
	font-family: Arial, Helvetica, sans-serif;
	color: #666666;
}
.Estilo4 {
	font-size: 18px;
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
}
.Estilo5 {
	font-size: 15px;
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
	color: #666666;
}	
.Estilo6 {
	font-family: Arial, Helvetica, sans-serif;
	color: #FF0000;
	font-size: 15px;
}
-->
</style>
</head>

<body>
<p><span class="Estilo2">¡Gracias <?php echo $nombre ?>! </span><br />
Tu teléfono sonará en un instante.</p>
<p class="Estilo5">¿No recibiste la llamada?<br />
  <span class="Estilo6">Puede ingresar como <strong>número privado, bloqueado o anonimo</strong>, si bloqueas este tipo de llamadas te sugerimos desactivarlo momentaneamente o contactarnos al </span><a href="tel:08103450728" target="_blank">08103450728</a><br />
</p>
<form>
  <p class="Estilo3" id="timeLeft">Podrás volver a solicitarla en 20 segundos</p>
     <!-- <input type="submit" disabled="disabled" id="submitButton" onclick="goBack()"/> -->
  <input type="button" disabled="disabled" id="submitButton" value="Volver a solicitar llamada" onclick="history.back()">
</form>

<div class="Row">
  <hr align="center" size="1" noshade="noshade" />
</div>

<div class="Row"><h4 class="Estilo4">También estamos atendiendo en</h4></div>
<div class="Row">
    <div class="Column"><a href="https://api.whatsapp.com/send?phone=5491132070654&amp;text=Hola, quiero mas información del servicio de INSAT!"><img src="../img/whatsapp.png" width="50" height="50" border="0" /></a></div>
    <div class="Column"><a href="https://m.me/insat.argentina"><img src="../img/social-facebook-button-blue-icon.png" width="40" height="40" border="0" /></a></div>
    <div class="Column"><a href="tel:08103450728"><img src="../img/phone-icon.png" width="40" height="40" border="0" /></a></div>
</div>
<div class="Row">
  <hr align="center" size="1" noshade="noshade" />
</div>
<div class="Row">
  <h4 class="Estilo4">¿Querés enterarte cuando haya nuevos servicios o promociones en tu zona?</h4>
<form name="form1" method="post" action="https://facebook.com/insat.argentina">
    <div align="center">
      <p>
        <input type="submit" name="VER" id="VER" value="¡Seguinos en Facebook!">
      </p>
  </div>
</form>
</div>

</p>
<script type="application/javascript" 
    src="https://sdk.truepush.com/v2.0.3/app.js" 
    async></script>
    <script>
var truepush = window.truepush || [];
truepush.push(function(){
    truepush.Init({
        id: "60494a52890fff398255c184"
        }, function(error){
            if(error) console.error(error);
        })
    })
</script>
</body>
</html>