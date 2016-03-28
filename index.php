<?php
//TODO Handle get requests to load previous diaries with a given password
//TODO Handle post request to save text into desired diary file
//TODO Handle get request that will display the main page template
$SALT = 'jazyisawesome';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['filename']) && isset($_POST['text'])) {
		$safeText = trim(htmlspecialchars($_POST['text']));
		//Check for empty or invalid strings
		if ($safeText === '' || $safeText === 'Incorrect key!') die('Invalid');

		$safeFilename = basename(htmlspecialchars($_POST['filename'])) . '.diary';
		$safeText = $SALT . $safeText; //My shitty salt

		if (isset($_POST['key'])) {
			//Use key to encrypt the diary text
			$diaryText = UnsafeCrypto::encrypt(
				$safeText,
				$_POST['key']);
		} else {
			$diaryText = $safeText; //Plain text
		}

		//If file exists make sure the key is correct to write to it
		if (file_exists($safeFilename)) {
			//Check if the key is the same one previously used to
			//prevent mischivious overwrites. My php is always disgusting...
			
			$file = fopen($safeFilename, 'r');
			$text = fread($file, filesize($safeFilename));
			fclose($file);
			if (isset($_POST['key'])) {
				//Decrypt first
				$text = UnsafeCrypto::decrypt($text, $_POST['key']);
			}
			//Now check for salt
			if (substr($text, 0, strlen($SALT)) === $SALT) {
				//Salt found, must be legit key carry on
			} else {
				//Salt not found therefore key most likely wrong
				die('Incorrect key!');
			}
		}

		//If all is good just write to the file
		//Saving a diary as $filename with $text as entry
		$file = fopen($safeFilename, 'w'); //OVERWRITE FILE!!!
		fwrite($file, $diaryText);
		fclose($file);
		die('OK');
	} else if (isset($_POST['delete'])) {
		//Delete the filename in $delete
		//TODO May be dangerous without passwords
	}
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_GET['filename'])) {
		$filename = basename($_GET['filename'] . '.diary');
		$file = fopen($filename, 'r');
		$txt = fread($file, filesize($filename));
		fclose($file);

		if (isset($_GET['key'])) {
			//Decrypt using key
			$plainText = UnsafeCrypto::decrypt($txt, $_GET['key']);
		} else {
			$plainText = $txt;
		}

		//Find salt and remove it or die
		if (substr($plainText, 0, strlen($SALT)) === $SALT) {
			//Remove the salt
			$plainText = substr($plainText, strlen($SALT));
		} else {
			//No salt means decrypt failed!
			die('Incorrect Key!');
		}

		die($plainText);
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
/*
 * UnsaveCrypto pull from stackoverflow
 * Author: Scott Arciszewski
 */

class UnsafeCrypto
{
    const METHOD = 'aes-256-ctr';

    /**
     * Encrypts (but does not authenticate) a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    public static function encrypt($message, $key, $encode = false)
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce.$ciphertext);
        }
        return $nonce.$ciphertext;
    }

    /**
     * Decrypts (but does not verify) a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public static function decrypt($message, $key, $encoded = false)
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
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
var key, autoSave;

function save() {
	var text = $('#textarea').val().trim();
	var filename = $('#filename').html().trim();
	if (filename === '' || text === '' || filename === 'No File Open') return;
	var request = $.ajax({
		type: 'POST',
		url: './',
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
	//TODO Get password
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
	if (toggle !== undefined && !disable)
		autoSave = setInterval(save, 30000);
}
$(document).ready(function() {
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
		<div class="panel-heading" id="filename">No File Open</div>
		<div class="panel-body">
			<textarea class="form-control" id="textarea">Click Options to open or create a diary file</textarea>
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

