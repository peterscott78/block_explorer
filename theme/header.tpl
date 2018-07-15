<!doctype html>
<html lang="en">
<head>
	<title>Block Explorer</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="{$site_uri}/theme/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="{$site_uri}/theme/css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div class="wrapper">

<div class="page-header">

	<div class="navbar">
		<ul class="nav">
			<li><a href="{$site_uri}/index">HOME</a></li>
			<li><a href="{$site_uri}/about">About</a></li>
			<li><a href="{$site_uri}/contact">Contact Us</a></li>
		</ul>
	</div>

	<h2><a href="{$site_uri}/index">Block Explorer</a></h2>
</div>

<div class="container" style="margin-top: 10px; padding-bottom: 120px;">

	{$user_message}

	<center><div class="panel">
		<table border="0" cellpadding="5"><tr>
			<td valign="top"><h4>Search</h4></td>
			<td valign="top">
				<form action="{$sote_uri}/search" method="POST">
				<input type="text" name="search" size="30" class="form-control">
				<input type="submit" name="submit" value="Search" class="btn btn-md btn-primary"><br />
				<p>Enter any txid, payment address, block num / hash, or HASH160.</p>
				</form>
			</td>
		</tr></table>
		</form>

	</div></center>




