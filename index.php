<?php require __DIR__ . '/typeprotect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Example Password-protected Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		*, *::before, *::after { box-sizing: border-box; }

		html {
			height: 100%;
			background: #f5f5f5;
		}

		body {
			margin: 0;
			font: normal 14px/24px Helvetica, Arial, sans-serif;
			background: #f5f5f5;
			padding-top: 80px;
		}

		#box {
			width: 400px;
			max-width: 90vw;
			background: #fff;
			padding: 30px 50px 50px 50px;
			border: 1px solid #eee;
			margin: 0 auto;
		}

		h1 {
			color: #22eedd;
			font: bold 18px/28px Helvetica, Arial, sans-serif;
			padding-bottom: 15px;
			border-bottom: 1px solid #eee;
			margin: 0 0 10px 0;
		}

		p {
			color: #444;
			margin: 20px 0;
		}

		code {
			font: normal 15px/25px "Courier New", monospace;
			color: #22eedd;
		}

		a.button {
			color: #fff;
			background: #22eedd;
			font: bold 15px/25px Helvetica, Arial, sans-serif;
			text-transform: uppercase;
			letter-spacing: 2px;
			text-align: center;
			margin-top: 40px;
			display: block;
			width: 100%;
			padding: 25px 0;
			border: none;
			text-decoration: none;
			border-radius: 5px;
		}

		a.button:hover {
			color: #22eedd;
			background: #000;
		}
	</style>
</head>
<body>

	<div id="box">
		<h1>Success!</h1>

		<p>You can only view this page after entering the correct password.</p>

		<p>Want to create a sign-out link? It's as easy as copying and pasting the code below.</p>

		<code>&lt;a href="typeprotect.php?signout=1"&gt;Sign Out&lt;/a&gt;</code>

		<a href="typeprotect.php?signout=1" class="button">Sign Out</a>
	</div><!-- // #box -->

</body>
</html>
