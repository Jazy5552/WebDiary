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
var key, autoSave;

function save() {
	var text = $('#textarea').val().trim();
	var filename = $('#filename').html().trim();
	key = $('#secretkey').find('input[name="key"]').val();
	if (filename === '' || text === '' || filename === 'No File Open') return;
	var request = $.ajax({
		type: 'POST',
		url: './diary.php',
		data: {
			filename: filename,
			text: text,
			key: key
		}
	})
		.done(function(data) {
			console.log('Post: ' + data);
			if (data === 'OK') {
				notifySave(true);
			} else {
				notifySave(false);
			}
		})
		.fail(function(data) {
			notifySave(false);
			console.log('Post: ' + data);
		});
}

function open(name) {
	key = $('#secretkey').find('input[name="key"]').val();
	$.ajax({
		type: 'GET',
		url: './',
		data: {
			filename: name,
			key: key
		}
	})
		.done(function(data) {
			$('#filename').html(name);
			$('#textarea').val(data);
			setAutoSave();
		})
		.fail(function() {
			console.log('Failed to open ' + name);
		});
}
function createNew(name) {
	$('#filename').html(name);
	$('#textarea').val('');
	setAutoSave();
}
function notifySave(success) {
	//TODO Display notification somewhere temporarily of save
	if (success) {
		console.log('Save complete');
	} else {
		console.log('Save failed!');
	}
}
function setAutoSave(disable) {
	//Set save interval. This func is shit
	if (autoSave !== undefined) clearInterval(autoSave);
	if (disable === undefined || disable)
		autoSave = setInterval(save, 30000);
}
function toggleKey() {
	$('#togglekey').toggleClass('active');
	var kInput = $('#secretkey').find('input[name="key"]');
	if (kInput.prop('type') !== 'password')
		kInput.prop('type', 'password');
	else
		kInput.prop('type', 'text');
}
function saveKeyLocally() {
	var kInput = $('#secretkey').find('input[name="key"]').val();
	localStorage.setItem('secretkey', kInput);
}
function getLocalKey() {
	var kInput = $('#secretkey').find('input[name="key"]');
	var lKey = localStorage.getItem('secretkey');
	if (lKey !== undefined)
		kInput.val(lKey);
}
$(document).ready(function() {
	$('#secretkey').submit(function() {
		save();
		return false; //Dont refresh page
	});
	$('#togglekey').on('click', toggleKey);
	$('#secretkey').find('input[name="key"]').on('change', saveKeyLocally);
	$('.save').on('click', save);

	getLocalKey();
});
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
						<li><a class="save" href="#">Save</a></li>
						<li><a href="#">Save As</a></li>
						<li><a href="#">Change key</a></li>
						<li><a href="#">Delete</a></li>
					</ul>
				</li>
				<li class="navbar-form">
					<button type="button" class="save btn btn-default" data-toggle"tooltip" title="Save">
						<span class="glyphicon glyphicon-floppy-disk"></span>
					</button>
				</li>
			</ul>
			<div class="pull-right">
			<form class="navbar-form navbar-left" id="secretkey">
				<button type="button" class="btn btn-default" id="togglekey">
					<span class="glyphicon glyphicon-lock" data-toggle="tooltip" title="Secret Key"></span>
				</button>
				<div class="form-group">
					<input type="password" class="form-control" placeholder="Secret Key" name="key" />
				</div>
			</form>
			</div>
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
		<div class="panel-heading" id="filename">No File Open</div>
		<div class="panel-body">
			<textarea class="form-control" id="textarea">Click Options to open or create a diary file</textarea>
		</div>
	</div>
</div>

<footer class="footer">
	<div class="container">
		<p class="text-muted">Made by Jazy Llerena using BootStrap and a PHP backend.</p>
	</div>
</footer>
</body>
</html>

