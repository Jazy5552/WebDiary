<?php
//TODO Store diarys in folders with names as usernames
//TODO Handle get requests to load previous diaries with a given password
//TODO Handle post request to save text into desired diary file
//TODO Handle get request that will display the main page template
$SALT = 'jazyisawesome'; //TODO Randomize and store in db

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['filename']) && isset($_POST['text'])) {
		$safeText = trim(htmlspecialchars($_POST['text']));
		//Check for empty or invalid strings
		if ($safeText === '' || $safeText === 'Error!') die('Invalid');

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
				die('Error! Invalid');
			}
		}

		//If all is good just write to the file
		//Saving a diary as $filename with $text as entry
		$file = fopen($safeFilename, 'w'); //OVERWRITE FILE!!!
		if ($file === false) {
		    // error, most likely permissions
		    die('Error! Cant Open!');
		}
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
			die('Error!');
		}
		//Decode html characters back
		$plainText = htmlspecialchars_decode($plainText);

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
 * UnsaveCrypto pulled from stackoverflow
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

