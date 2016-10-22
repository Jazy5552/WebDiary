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
.disabled {
	pointer-events: none;
	background-color: #EAEAEA;
}

</style>
<script>
var key, autoSave;
var disable = false; //Disable auto save (auto save is bad)
var dOpen = false; //Holder for when a diary is open

function close() {
	//Close open file (Disable auto save) and clear the text
	if (dOpen) {
		dOpen = false;
		if (autoSave !== undefined) clearInterval(autoSave);
		$('#textarea').val('');
		$('#filename').html('No File Open');
		$('button.save').removeClass('btn-primary');
	}
}
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
		url: './diary.php',
		data: {
			filename: name,
			key: key
		}
	})
		.done(function(data) {
			if (data === 'Error!') {
				close();
				console.log('Failed to open ' + name);
				$('#textarea').val($('#textarea').val() + '\n' + 'Failed to open ' + name);
			} else {
				$('#filename').html(name);
				$('#textarea').val(data);
				setAutoSave();
			}
		})
		.fail(function() {
			close();
			console.log('Failed to open ' + name);
			$('#textarea').val($('#textarea').val() + '\n' +
				'Failed to open ' + name);

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
	$('button.save').addClass('btn-primary');
	dOpen = true;
	//Set save interval. This func is shit
	if (autoSave !== undefined) clearInterval(autoSave);
	if (disable === undefined || !disable)
		autoSave = setInterval(save, 30000);
}
function toggleKey() {
	$('#togglekey').toggleClass('active');
	$('#togglekey span').toggleClass('glyphicon-eye-open');
	$('#togglekey span').toggleClass('glyphicon-eye-close');
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
function prepCreateNewModal(e) {
	//Set a default name
	var date = new Date();
	var def = date.getFullYear() + '-' + (date.getMonth()+1) + '-'
		+ date.getDate();
	$('#createNewModal input').val(def);
}
function modalCreateNew() {
	var filename = $('#createNewFilename').val();
	$('#createNewModal').modal('hide');
	createNew(filename);
}
function modalOpen() {
	var filename = $('#openFilename').val();
	$('#openModal').modal('hide');
	open(filename);
}

$(document).ready(function() {
	$('#createNewModal').on('shown.bs.modal', prepCreateNewModal);
	$('#createNewButton').on('click', modalCreateNew);
	$('#openButton').on('click', modalOpen);
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
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Options <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a data-toggle="modal" data-target="#createNewModal" href="#">New Diary</a></li>
						<li><a data-toggle="modal" data-target="#openModal" href="#">Open Diary File</a></li>
						<li role="separator" class="divider"></li>
						<li class="dropdown-header">Current Diary</li>
						<li><a class="save" href="#">Save</a></li>
						<li><a class="disabled" href="#">Save As</a></li>
						<li><a class="disabled" href="#">Change key</a></li>
						<li><a class="disabled" href="#">Delete</a></li>
					</ul>
				</li>
				<li class="navbar-form">
					<button type="button" class="save btn" data-toggle="tooltip" title="Save">
						<span class="glyphicon glyphicon-floppy-disk"></span>
					</button>
				</li>
			</ul>
			<div class="pull-right">
			<form class="navbar-form navbar-left" id="secretkey">
				<span class="glyphicon glyphicon-lock" data-toggle="tooltip" title="Secret Key"></span>
				<div class="form-group">
					<input type="password" data-toggle="tooltip" title="Secret Key" class="form-control" placeholder="Secret Key" name="key" />
				</div>
				<button type="button" class="btn btn-default btn-sm" id="togglekey">
					<span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" title="Show Key"></span>
				</button>
			</form>
			</div>
		</div><!--/.nav-collapse -->
	</div>
</nav>

<!--modal to Create New-->
<div id="createNewModal" class="modal fade bs-modal-sm">
  <div class="modal-dialog bs-modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Create New Diary</h4>
      </div>
			<div class="modal-body">
				<label for="createNewFilname">Diary Name</label>
				<input id="createNewFilename" data-toggle="tooltip" title="Diary Name" class="form-control" placeholder="Diary Name" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button id="createNewButton" type="button" class="btn btn-primary">Create</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!--modal to Open-->
<div id="openModal" class="modal fade bs-modal-sm">
  <div class="modal-dialog bs-modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Open Diary</h4>
      </div>
			<div class="modal-body">
				<label for="openFilename">Diary Name</label>
				<input id="openFilename" data-toggle="tooltip" title="Diary Name" class="form-control" placeholder="Diary Name" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button id="openButton" type="button" class="btn btn-primary">Open</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

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

