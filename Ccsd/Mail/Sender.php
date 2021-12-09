<?php


namespace Ccsd\Mail;

/**
 *
 */
class SenderException extends \Exception { }

/**
 * Envoie des mails mis en queue
 */
class Sender
{
	public $mail;

    const MAX_RETRIES = 5;
    const SENTMAILDIR = 'sent';
    const UNSENTMAILDIR = 'unsent';

    /**

     */
    public function construct() {
        ini_set('SMTP','srelay.in2p3.fr');
        ini_set('sendmail_from', 'ccsd@ccsd.cnrs.fr');
    }

    /**
     * Procède à l'envoi de tous les mails d'un projet
     * @return bool
     */
    public function sendAllMails()
	{
        $mail_list = $this->scan(CCSD_MAIL_PATH.'/' . self::UNSENTMAILDIR);

		if ($mail_list) {
			foreach ($mail_list as $mail_directory) {
				$message = $this->send($mail_directory);
				$this->updateLog(date("d/m/Y - H:i:s").' - '.$message);
			}
		}
		// Message envoyé
		return true;
	}

    /**
     * @return string
     */
    private function getLogDir() {
        return CCSD_MAIL_PATH . '/log/';
    }

    /**
     * @return string
     */
    private function getSentDir() {
        return CCSD_MAIL_PATH . '/' . self::SENTMAILDIR . '/';
    }

    /**
     * @return string
     */
    private function getUnsentDir() {
        return CCSD_MAIL_PATH . '/'. self::UNSENTMAILDIR . '/';
    }

    /**
     * @param string $mailPath
     * @param string $mail_directory
     * @param string $xmlFilename
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    private function readXmlMail($mailPath, $mail_directory, $xmlFilename) {
        if (! is_file($xmlFilename)) {
            throw new SenderException($xmlFilename. " : ERREUR - Le fichier n'existe pas");
        }

        // Chargement du xml
        libxml_use_internal_errors(true);
        $xmlMail = simplexml_load_file($xmlFilename);

        // Contrôle de la validité du XML
        try {
            if ($xmlMail === false) {
                $message = $xmlFilename.' : ERREUR - impossible de charger le XML';
                foreach(libxml_get_errors() as $error) {
                    $message .= "\t";
                    $message .= $error->message.' (';
                    $message .= 'line '.$error->line;
                    $message .= ', column '.$error->column;
                    $message .= ')';
                }
                throw new SenderException($message);
            }

        } catch(\Exception $e) {
            $this->moveDirectory($mailPath, $this->getLogDir() . $mail_directory);
            throw  $e;
        }
        return $xmlMail;

    }

    /**
     * @param string $mailPath
     * @param \SimpleXMLElement $xmlMail
     * @return string
     */
    private function buildAttachement($mailPath, $xmlMail, $bound_att, $bound_text) {

        $filesList = $this->getAttachments($mailPath, $xmlMail);

        if (empty($filesList)) {
            return "";
        }

        $attachments = '--' . $bound_att . '--' . PHP_EOL . PHP_EOL;

        foreach ($filesList as $attachment) {

            $name = $attachment['name'];
            $type = $attachment['type'];

            $file = file_get_contents($mailPath . '/' . $name);
            $file = chunk_split(base64_encode($file));

            $attachments .= '--' . $bound_text . PHP_EOL;

            $attachments .= 'Content-Type: ' . $type . '; name="' . $name . '"' . PHP_EOL;
            $attachments .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
            $attachments .= 'Content-Disposition: attachment; filename="' . $name . '"' . PHP_EOL . PHP_EOL;
            $attachments .= $file;
        }

        $attachments .= '--' . $bound_text . '--' . PHP_EOL;

        return $attachments;
    }

    /**
     *
     */
    private function getBodyParts($xmlMail, $bound_att) {
        $bodyHtmlCharset = $xmlMail->bodyHtml['charset'];
        $bodyHtml = ($xmlMail->bodyHtml) ? htmlspecialchars_decode($xmlMail->bodyHtml) : '';
        if ($bodyHtmlCharset != 'quoted-printable' && $bodyHtml) {
            $bodyHtml = quoted_printable_encode($bodyHtml);
        }

        $bodyTextCharset = $xmlMail->bodyText['charset'];
        $bodyText = ($xmlMail->bodyText) ?: '';
        if ($bodyTextCharset != 'quoted-printable' && $bodyText) {
            $bodyText = quoted_printable_encode($bodyText);
        }

        $message = '';
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
        return $message;
    }
    /**
     * @param \SimpleXMLElement $xmlMail
     * @param string $mailPath
     * @return array
     * @throws SenderException
     */
    private function buildEmail($xmlMail, $mailPath, $mail_directory) {
        $subject = ($xmlMail->subject) ? stripslashes($xmlMail->subject) : '';

        $to = $this->getAddressList('to', $xmlMail);
        $cc = $this->getAddressList('cc', $xmlMail);
        $bcc = $this->getAddressList('bcc',$xmlMail);

        $from = $this->getAddress('from', $xmlMail);
        $defaultFrom = ($xmlMail->from) ? $this->getAddress('from', $xmlMail, true) : 'ccsd@ccsd.cnrs.fr';

        $return_path = $this->getAddress('return-path', $xmlMail);
        $reply_to = $this->getAddress('reply-to',$xmlMail);
        $notification = $this->getAddress('disposition-notification-to', $xmlMail);

        // Boundaries ************************************************************
        $bound_text = md5(uniqid(rand()));
        $bound_att = md5(uniqid(rand()));

        // Headers ***************************************************************
        $headers = 'X-Mailer: CCSD (https://ccsd.cnrs.fr)'.PHP_EOL;

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
        $message = "This is a multi-part message in MIME format." . PHP_EOL . PHP_EOL;
        $message .= "--".$bound_text.PHP_EOL;
        $message .= 'Content-Type: multipart/alternative;boundary="'.$bound_att.'"'.PHP_EOL.PHP_EOL;

        // Message Plain Text *****************************************************
        $bodyParts = $this->getBodyParts($xmlMail, $bound_att);
        $message .= $bodyParts;
        // Pièces jointes ********************************************************

        $attachment = $this->buildAttachement($mailPath, $xmlMail, $bound_att, $bound_text);
        $message .= $attachment;

        if (empty($to)) {
            $this->moveDirectory($mailPath, $this->getLogDir() . $mail_directory);
            throw new SenderException($mailPath.' : ERREUR - Aucun destinataire');
        }

        if (empty($subject) && empty($bodyParts) && empty($attachment)) {
            $this->moveDirectory($mailPath, $this->getLogDir() . $mail_directory);
            throw new SenderException($mailPath.' : ERREUR - Message sans contenu');
        }

        return  [ $to, $subject, $message, $headers, $defaultFrom ];
    }
    /**
     * Procède à l'envoi du mail
     * Le paramètre est le chemin du dossier contenant le mail
     * @param $mail_directory
     * @return string
     */
    public function send($mail_directory)
	{
		/*	Status Codes
		 * 2.1 : Le chemin spécifié n'existe pas
		 * 2.2 : Le fichier XML n'existe pas
		 * 2.3 : Impossible d'ouvrir le fichier XML
		 * 2.4 : Le fichier est déjà verrouillé
		 */

		$mailPath = $this->getUnsentDir() . $mail_directory;
		$xmlFilename = $mailPath.'/mail.xml';

        $fileStream = @fopen($xmlFilename, 'r+');
        if (!$fileStream) {
            return $xmlFilename ." : ERREUR - impossible d'ouvrir le fichier XML";
        }

        // Verrouillage du fichier
        if (!flock($fileStream, LOCK_EX | LOCK_NB)) {
            return $mailPath." : ERREUR - Le fichier est verrouillé (en cours d'utilisation par un autre processus)";
        }

        try {
            $xmlMail = $this->readXmlMail($mailPath, $mail_directory, $xmlFilename);
            list($to, $subject, $message, $headers, $defaultFrom) = $this->buildEmail($xmlMail, $mailPath, $mail_directory);

            // Envoi du mail **********************************************************
            if ( mail($to, $subject, $message, $headers, $defaultFrom) ) {
                // Transfert du mail dans le dossier "sent"
                if (! is_dir($sentMailDir = $this->getSentDir())) {
                    mkdir($sentMailDir, 0755);
                }

                $this->moveDirectory($mailPath, $sentMailDir . $mail_directory);
                $logMessage = $mailPath." : envoi réussi.";
            } else {
                if ($xmlMail['errors'] < self::MAX_RETRIES) {
                    $logMessage = $mailPath." : ERREUR - échec de l'envoi (".($xmlMail['errors']+1).'/'.self::MAX_RETRIES.')';
                    $this->updateErrorsCount($mailPath.'/mail.xml', $xmlMail);
                } else {
                    $this->moveDirectory($mailPath, $this->getLogDir() . $mail_directory);
                    $logMessage = $mailPath." : ERREUR - Nombre maximum de tentatives atteint.";
                }
            }
        } catch (\Exception $e) {
            $logMessage = $e->getMessage();
        }

        // Fermeture du fichier et déverrouillage
        flock($fileStream, LOCK_UN);
        fclose($fileStream);
        return $logMessage;
	}

	/**
     * Met à jour le log
     * @param $message string
     */
    private function updateLog($message)
	{
		file_put_contents(CCSD_MAIL_PATH.'/log/log.txt', $message.PHP_EOL, FILE_APPEND);
	}

    /**
     * Scanne un dossier et renvoie la liste des dossiers qu'il contient
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
     * @param \SimpleXMLElement $xmlMail
     */
	private function updateErrorsCount($file, $xmlMail)
	{
	    $errorsCount = $xmlMail['errors'];
		$headersCharset = ($xmlMail['charset']) ? 'UTF-8' : $xmlMail['charset'];

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
        // Target dir already exists...
		if (!is_dir($newPath)) {
			return rename($oldPath, $newPath);
		}
		return false;
	}

	/**
	 * Récupération d'adresse pour le header
     *Le paramètre est le nom du champ à récupérer (from, return-path, reply-to)
     *
     * @param $fieldName string
     * @param bool $option
     * @return bool|string
     */
    private function getAddress($fieldName, $xmlMail, $option = false)
	{
		$string = false;
		if (!empty($xmlMail->$fieldName)) {
			$item = $xmlMail->$fieldName;
			if (!empty($item->mail)) {
				$string = ($item->name) ? $item->name.' <'.$item->mail.'>' : $item->mail;
				if ($option && $fieldName == 'from') {
					$string = '-f'.$item->mail;
				}
			}
		}
		return $string;
	}

	/**
     * Récupération d'une liste d'adresses pour le header
     * Le paramètre est le nom de la liste à récupérer (to, cc_, bcc)
     * @param $listName string
     * @return string
     */
	private function getAddressList($listName, $xmlMail)
	{
	    $string = false;
		if (!empty($xmlMail->{$listName.'_list'}->$listName)) {
			$string = array();
			foreach($xmlMail->{$listName.'_list'}->$listName as $item) {
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
     * @return array
     */
    private function getAttachments($mailPath, $xmlMail)
	{
		$files = [];
		if ($xmlMail->files_list->file) {
			foreach($xmlMail->files_list->file as $file) {
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
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		return $finfo->file($file);
	}

}
