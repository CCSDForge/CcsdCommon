<?php
// TODO déplacer les paramètres dans un fichier de conf externe par application
define('ROOTPATH', '/data/mails/'); // Chemin du dossier de stockage contenant les mails
define('MAX_RETRIES', 5); // Nombre max de tentatives d'envoi du mail avant de le déplacer dans /log
define('SENTMAILDIR', 'sent');
define('UNSENTMAILDIR', 'unsent');

ini_set('SMTP','srelay.in2p3.fr');
ini_set('sendmail_from', 'ccsd@ccsd.cnrs.fr');

class Ccsd_Mail_Sender
{
	public $mail;
	public $applicationName;
	private $path;

    /**
     *Procède à l'envoi de tous les mails d'un projet
     * @param $appName string
     * @return bool
     */
    public function sendAllFor($appName)
	{
		$this->applicationName = $appName;
		$this->path = ROOTPATH.$appName.'/';

		if (!is_dir($this->path)) {
		    echo $this->path." : Ce chemin n'existe pas\n";
		    return false;
		}

		$mail_list = $this->scan($this->path.'/' . UNSENTMAILDIR);

		if ($mail_list) {
			foreach ($mail_list as $mail_directory) {
				$message = $this->send($this->path, $mail_directory);
				$this->updateLog(date("d/m/Y - H:i:s").' - '.$message);
			}
		}
		// Message envoye
		return true;
	}

    /**
     * Procède à l'envoi de tous les mails de tous les projets
     */
    public function sendAll()
	{
		foreach ($this->scan(ROOTPATH) as $project) {

			$this->applicationName = $project;
			$this->path = ROOTPATH.$project.'/';

			$mail_list = $this->scan($this->path.'/' . UNSENTMAILDIR);

			if ($mail_list) {
				foreach ($mail_list as $mail_directory) {
					$message = $this->send($this->path, $mail_directory);
					$this->updateLog(date("d/m/Y - H:i:s").' - '.$message);
				}
			}
		}
	}

    /**
     * Procède à l'envoi du mail
     * Le paramètre est le chemin du dossier contenant le mail
     * @param $path
     * @param $mail_directory
     * @return string
     */
    public function send($path, $mail_directory)
	{
		/*	Status Codes
		 * 2.1 : Le chemin spécifié n'existe pas
		 * 2.2 : Le fichier XML n'existe pas
		 * 2.3 : Impossible d'ouvrir le fichier XML
		 * 2.4 : Le fichier est déjà verrouillé
		 */

		$mailPath = $path. UNSENTMAILDIR . '/'.$mail_directory;

		// Chargement du XML ****************************************************
		if (!is_dir($mailPath)) {
			return ("Le chemin spécifié n'existe pas : ".$mailPath);
		}

		$xmlfilename = $mailPath.'/mail.xml';
		if (is_file($xmlfilename)) {

			$fileStream = fopen($xmlfilename, 'r+');

			if (!$fileStream) {
				return $mailPath." : ERREUR - impossible d'ouvrir le fichier XML";
			}

			// Chargement du xml
			libxml_use_internal_errors(true);
			$this->mail = simplexml_load_file($xmlfilename);

			// Controle de la validité du XML
			try {
				if ($this->mail === false) {
				    $message = $mailPath.' : ERREUR - impossible de charger le XML';
				    foreach(libxml_get_errors() as $error) {
				        $message .= "\t";
				        $message .= $error->message.' (';
				        $message .= 'line '.$error->line;
				        $message .= ', column '.$error->column;
				        $message .= ')';
				    }
					throw new Exception($message);
				}

			} catch(Exception $e) {
			    $this->moveDirectory($mailPath, $this->path.'log/'.$mail_directory);
			    return $e->getMessage();
			}

			// Verrouillage du fichier
			if (!flock($fileStream, LOCK_EX | LOCK_NB)) {
				return $mailPath." : ERREUR - Le fichier est verrouillé (en cours d'utilisation par un autre processus)";
			}

		} else {
			rmdir($mailPath);
            return ($mailPath." : ERREUR - Le fichier n'existe pas");
		}

		// Initialisation *******************************************************
		$errorsCount = $this->mail['errors'];

		$subject = ($this->mail->subject) ? stripslashes($this->mail->subject) : '';

        $headersCharset = $this->mail['charset'];

		$bodyHtmlCharset = $this->mail->bodyHtml['charset'];
		$bodyHtml = ($this->mail->bodyHtml) ? htmlspecialchars_decode($this->mail->bodyHtml) : '';
		if ($bodyHtmlCharset != 'quoted-printable' && $bodyHtml) {
		    $bodyHtml = quoted_printable_encode($bodyHtml);
		}

		$bodyTextCharset = $this->mail->bodyText['charset'];
		$bodyText = ($this->mail->bodyText) ? $this->mail->bodyText : '';
		if ($bodyTextCharset != 'quoted-printable' && $bodyText) {
		    $bodyText = quoted_printable_encode($bodyText);
		}

		$to = $this->getAddressList('to');
		$cc = $this->getAddressList('cc');
		$bcc = $this->getAddressList('bcc');

		$from = $this->getAddress('from');
		$defaultFrom = ($this->mail->from) ? $this->getAddress('from', true) : 'ccsd@ccsd.cnrs.fr';

		$return_path = $this->getAddress('return-path');
		$reply_to = $this->getAddress('reply-to');
		$notification = $this->getAddress('disposition-notification-to');

		if (empty($to)) {
		    $this->moveDirectory($mailPath, $this->path.'log/'.$mail_directory);
		    return ($mailPath.' : ERREUR - Aucun destinataire');
		}

		// Boundaries ************************************************************
		$bound_text = md5(uniqid(rand()));
		$bound_att = md5(uniqid(rand()));

		// Headers ***************************************************************
		$headers = 'X-Mailer: CCSD (http://ccsd.cnrs.fr)'.PHP_EOL;

		if ($from) {
			$headers .= 'From: '.$from.PHP_EOL;
		} else {
			$headers .= 'From: ccsd@ccsd.cnrs.fr'.PHP_EOL;
		}

		if ($reply_to) {
			$headers .= 'Reply-To: '.$reply_to.PHP_EOL;
		}

		if ($notification) {
			$headers .= 'Disposition-Notification-To: '.$notification.PHP_EOL;
		}

		if ($return_path) {
			$headers .= 'Return-Path: '.$return_path.PHP_EOL;
		} else {
			$headers .= 'Return-Path: error@ccsd.cnrs.fr'.PHP_EOL;
		}

		if ($cc) {
			$headers .= 'Cc: '.$cc.PHP_EOL;
		}
		if ($bcc) {
			$headers .= 'Bcc: '.$bcc.PHP_EOL;
		}

		$headers .=	"MIME-Version: 1.0".PHP_EOL;
		$headers .= "Content-Type: multipart/mixed; boundary=\"$bound_text\"";

		// Construction du message ************************************************
		$message = '';
		$message .= "This is a multi-part message in MIME format.".PHP_EOL.PHP_EOL;
		$message .= "--".$bound_text.PHP_EOL;
		$message .= 'Content-Type: multipart/alternative;boundary="'.$bound_att.'"'.PHP_EOL.PHP_EOL;

		// Message Plain Text *****************************************************
		if ($bodyText) {
			$message .= '--'.$bound_att.PHP_EOL;
			// Fix charset...: $bodyHtmlCharset contient parfois quoted-printable....
            // Et on s'en sert avant pour savoir si le html est deja encode...
            // Difficile de mettre le bon encodage dedans, car si on change, le mail n'aura plus de transfert encoding...
            // Bon on sait qu'on a mis du quoted-printable, nous fonctionnons en utf-8
            // On force le charset a utf-8
            $mailCharset = ($bodyTextCharset == 'quoted-printable' ? 'utf-8' : $bodyTextCharset);
            $message .= 'Content-Type: text/plain; charset='.$mailCharset .PHP_EOL;
			$message .= 'Content-Transfer-Encoding: quoted-printable'.PHP_EOL.PHP_EOL;
			$message .= $bodyText.PHP_EOL;
		}

		// Message HTML ***********************************************************
		if ($bodyHtml) {
			$message .= '--'.$bound_att.PHP_EOL;
			// Fix charset...: $bodyHtmlCharset contient parfois quoted-printable....
            // Et on s'en sert avant pour savoir si le html est deja encode...
            // Difficile de mettre le bon encodage dedans, car si on change, le mail n'aura plus de transfert encoding...
            // Bon on sait qu'on a mis du quoted-printable, nous fonctionnons en utf-8
            // On force le charset a utf-8
            $mailCharset = ($bodyHtmlCharset == 'quoted-printable' ? 'utf-8' : $bodyHtmlCharset);
			$message .= 'Content-Type: text/html; charset='.$mailCharset.PHP_EOL;
			$message .= 'Content-Transfer-Encoding: quoted-printable'.PHP_EOL.PHP_EOL;
			$message .= $bodyHtml.PHP_EOL;
		}

		// Pièces jointes ********************************************************
		$filesList = $this->getAttachments($mailPath);

		if ($filesList) {

			$attachments = '';
			$attachments .= '--'.$bound_att.'--'.PHP_EOL.PHP_EOL;

			foreach($filesList as $attachment) {

				$name = $attachment['name'];
				$type = $attachment['type'];

				$file = file_get_contents($mailPath.'/'.$name);
				$file = chunk_split(base64_encode($file));

				$attachments .= '--'.$bound_text.PHP_EOL;

				$attachments .= 'Content-Type: '.$type.'; name="'.$name.'"'.PHP_EOL;
				$attachments .= 'Content-Transfer-Encoding: base64'.PHP_EOL;
				$attachments .= 'Content-Disposition: attachment; filename="'.$name.'"'.PHP_EOL.PHP_EOL;
				$attachments .= $file;
			}

			$attachments .= '--'.$bound_text.'--'.PHP_EOL;

			$message .= $attachments;
		}

		if (empty($subject) && empty($bodyText) && empty($bodyHtml) && empty($filesList)) {
		    $this->moveDirectory($mailPath, $this->path.'log/'.$mail_directory);
		    return ($mailPath.' : ERREUR - Message sans contenu');
		}

		// Envoi du mail **********************************************************
		if ( mail($to, $subject, $message, $headers, $defaultFrom) ) {

			// Fermeture du fichier et déverrouillage
			if($fileStream){
				flock($fileStream, LOCK_UN);
				fclose($fileStream);
			}

			// Transfert du mail dans le dossier "sent"
			if (! is_dir($this->path. SENTMAILDIR . '/')) {
				mkdir($this->path. SENTMAILDIR . '/', 0777);
			}

			$this->moveDirectory($mailPath, $this->path.SENTMAILDIR . '/'.$mail_directory);
			$message = $mailPath." : envoi réussi.";
		} else {

			// Fermeture du fichier et déverrouillage
			if($fileStream){
				flock($fileStream, LOCK_UN);
				fclose($fileStream);
			}

			$message = $mailPath." : ERREUR - échec de l'envoi (".($this->mail['errors']+1).'/'.MAX_RETRIES.')';

			if ($this->mail['errors'] < MAX_RETRIES) {
				$this->updateErrorsCount($mailPath.'/mail.xml');
			} else {
				$this->moveDirectory($mailPath, $this->path.'log/'.$mail_directory);
				$message = $mailPath." : ERREUR - Nombre maximum de tentatives atteint.";
			}
		}
		return $message;
	}

	/**
     * Met à jour le log
     * @param $message string
     */
    private function updateLog($message)
	{
		file_put_contents($this->path.'/log/log.txt', $message.PHP_EOL, FILE_APPEND);
	}

    /**
     * Scanne un dossier, et renvoie la liste des dossiers qu'il contient
     * @param $path
     * @return array
     */
    private function scan($path)
	{
	    if ( is_dir($path) ) {
			$directory = opendir($path);
			$dirList = array();
			while( $entry = readdir($directory) ) {
				if(is_dir($path.'/'.$entry) && $entry != '.' && $entry != '..' && $entry != '.svn') {
					$dirList[] = $entry;
				}
			}
			closedir($directory);
			return $dirList;
	    } else {
	    	return array();
	    }
	}
	/**
     * Met à jour le compteur d'erreurs du fichier XML
     * @param $file string
     */
	private function updateErrorsCount($file)
	{
	    $errorsCount = $this->mail['errors'];
		$headersCharset = ($this->mail['charset']) ? 'UTF-8' : $this->mail['charset'];

		$buffer = file($file);
		$buffer[1] = '<mail errors="'.($errorsCount+1).'" charset="'.$headersCharset.'">'.PHP_EOL;
		$buffer = implode('', $buffer);

		$fileStream = fopen($file, 'w');
		fwrite($fileStream, $buffer);
		fclose($fileStream);
	}

    /**
     * @param $oldPath string
     * @param $newPath string
     * @return bool
     */
    private function moveDirectory($oldPath, $newPath)
	{
		if (!is_dir($newPath)) {
			return rename($oldPath, $newPath);
		}
		return false;
	}

	/**
	 * Récupération d'adresse pour le header
     *Le paramètre est le nom du champ à récupérer (from, return-path, reply-to)
     *
     * @param $fieldname string
     * @param bool $option
     * @return bool|string
     */
    private function getAddress($fieldname, $option = false)
	{
		$string = false;
		if (!empty($this->mail->$fieldname)) {
			$item = $this->mail->$fieldname;
			if (!empty($item->mail)) {
				$string = ($item->name) ? $item->name.' <'.$item->mail.'>' : $item->mail;
				if ($option && $fieldname == 'from') {
					$string = '-f'.$item->mail;
				}
			}
		}
		return $string;
	}

	/**
     * Récupération d'une liste d'adresses pour le header
     * Le paramètre est le nom de la liste à récupérer (to, cc_, bcc)
     * @param $listname string
     * @return string
     */
	private function getAddressList($listname)
	{
	    $string = false;
		if (!empty($this->mail->{$listname.'_list'}->$listname)) {
			$string = array();
			foreach($this->mail->{$listname.'_list'}->$listname as $item) {
				if (!empty($item->mail)) {
					$string[] = ($item->name) ? $item->name.' <'.$item->mail.'>' : $item->mail;
				}
			}
			$string = implode(", ", $string);
		}

		return $string;
	}

	/**
     * Renvoie la liste des pièces jointes
     * @param $mailPath string
     * @return array|bool
     */
    private function getAttachments($mailPath)
	{
		$files = false;
		if ($this->mail->files_list->file) {
			foreach($this->mail->files_list->file as $file) {
				$tmp = array();
				$tmp['name'] = strval($file);
				// Si le type de fichier n'est pas spécifié en attribut, on va le lire nous-même
				$tmp['type'] = ($file['type']) ? strval($file['type']) : $this->getMimeType($mailPath.'/'.$file);

				$files[] = $tmp;
			}
		}
		return $files;
	}

    /**
     * Renvoie le type mime d'un fichier
     * @param $file string
     * @return string
     */
    private function getMimeType($file)
	{
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		return $finfo->file($file);
	}

}