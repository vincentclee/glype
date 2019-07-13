<html>
<head>
	<title>Security Warning</title>
	<style type="text/css">
html, body {
	background: #0b1933;
	text-align: center;
}
body {
	font: 80% Tahoma;
}
#wrapper {
	margin: 100px auto;
	width: 500px;
	text-align: left;
	background: #fff;
	padding: 10px;
   border: 5px solid #ccc;
}
form { 
   text-align: center;
}
	</style>
   <base href="<?php echo GLYPE_URL; ?>/">
</head>
<body>
	<div id="wrapper">
		<h1>Warning!</h1>
		<p>The site you are attempting to browse is on a secure connection. This proxy is not on a secure connection.</p>
      <p>The target site may send sensitive data, which may be intercepted when the proxy sends it back to you.</p>
      <form action="includes/process.php" method="get">
         <input type="hidden" name="action" value="sslagree">
			<input type="submit" value="Continue anyway...">
         <input type="button" value="Return to index" onclick="window.location='.';">
		</form>
      <p><b>Note:</b> this warning will not appear again.</p>
	</div>
</body>
</html>