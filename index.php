<?php
//TODO Handle get requests to load previous diaries with a given password
//TODO Handle post request to save text into desired diary file
//TODO Handle get request that will display the main page template
$errNoFileOpenHeader = 'No file open';
$errNoFileOpenText = 'Click Options and then select to open an existing file or create a new one';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['filename']) && isset($_POST['text'])) {
		//TODO Include password
		//Saving a diary as $filename with $text as entry
		$file = fopen(basename($_POST['filename'] . '.diary'), 'w');
		//TODO Do some sort of encryption using password maybe?
		fwrite($file, htmlspecialchars($_POST['text']));
		fclose($file);
		die('OK');
	} else if (isset($_POST['delete'])) {
		//Delete the filename in $delete
		//TODO May be dangerous without passwords
	}
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['filename'])) {
		//TODO Include password
		$filename = basename($_GET['filename'] . '.diary');
		$file = fopen($filename, 'r');
		//TODO Do some sort of decryption
		$txt = fread($file, filesize($filename));
		die($txt);
	} else if (isset($_GET['list'])) {
		$files = scandir(__DIR__);
		$rFiles = [];
		foreach ($files as $file) {
			if (strrpos($file, '.diary') !== false) {
				$rFiles[] =  $file;
			}
		}
		die(json_encode($rFiles));
	} else {
		//Print out the template
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Web Diary</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="author" content="Jazy Llerena" />
<!--Lets try bootstrap stuff-->
<script   src="https://code.jquery.com/jquery-2.2.2.min.js"   integrity="sha256-36cp2Co+/62rEAAYHLmRCPIych47CvdM+uTBJwSzWjI="   crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous" />
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<!--End CDN stuff-->
</head>
<style>
html {
/*
  position: relative;
	min-height: 100%;
*/
	height: 100%;
}
body {
	height: 100%;
}
body .container {
	/*Calc determined by nav and footer height*/
	height: calc(100% - 72px - 60px);
}
.panel-default {
	height: calc(100% - 21px);
}
.panel-body {
	height: 100%;
}
.footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  /* Set the fixed height of the footer here */
  height: 60px;
  background-color: #f5f5f5;
}

#textarea {
	height: calc(100% - 42px);
	resize: none;
}

/* For sticky footer */
.footer > .container {
  padding-right: 15px;
  padding-left: 15px;
}
.container .text-muted {
  margin: 20px 0;
}

</style>
<script>
function save() {
	var text = $('#textarea').val();
	var filename = $('#filename').html(name);
}

function createNew(name) {
	$('#filename').html(name);
	$('#textarea').val('');
}
function notifySave(success) {
	//TODO Display notification somewhere temporarily of save
}
</script>
<body>
<!-- Fixed navbar -->
<nav class="navbar navbar-default navbar-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Web Diary</a>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li><a href="/">Home</a></li>
<!--
				<li><a href="#about">About</a></li>
				<li><a href="#contact">Contact</a></li>
-->
				<li class="dropdown disabled">
					<a href="#" class="dropdown-toggle disabled" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Options <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="#">New Diary</a></li>
						<li><a href="#">Open Diary File</a></li>
						<li role="separator" class="divider"></li>
						<li class="dropdown-header">Current Diary</li>
						<li><a href="#">Save</a></li>
						<li><a href="#">Save As</a></li>
						<li><a href="#">Change password</a></li>
						<li><a href="#">Delete</a></li>
					</ul>
				</li>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>

<!--Main page content-->
<div class="container">
<!--
	<div id="header" class="page-header">
	I am header
	</div>
-->
	<div class="panel panel-default">
		<div class="panel-heading" id="filename">
			No File Open
		</div>
		<div class="panel-body">
			<textarea class="form-control" id="textarea">
				Click Options to open or create a diary file
			</textarea>
		</div>
	</div>
</div>

<footer class="footer">
	<div class="container">
		<p class="text-muted">I am footer</p>
	</div>
</footer>
</body>
</html>

