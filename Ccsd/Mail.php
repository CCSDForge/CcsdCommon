<?php

// Ecriture du mail au format XML pour envoi différé par un serveur dédié
abstract class Ccsd_Mail extends Zend_Mail
{
    const DIR = __DIR__;
    protected $path = null;
    protected $attachments = array();

    protected $_templatePath;
    protected $_templateName;

    protected $_lang = 'fr';
    protected $tags = array();

    public function __construct($charset = null)
    {
        if (isset($charset)) {
            parent::__construct($charset);
        }
    }

    public function setPath($path = '')
    {
        if ($path) {
            $this->path = $path;
            $this->checkAppDirectory();
        }
    }

    public function addTo($email, $name = '')
    {
        if (empty($email)) {
            return false;
        }
        parent::addTo($email, $name);
    }

    public function addCc($email, $name = '')
    {
        if (empty($email)) {
            return false;
        }
        parent::addCc($email, $name);
    }

    public function addBcc($email)
    {
        if (empty($email)) {
            return false;
        }
        parent::addBcc($email);
    }

    public function setLang($lang)
    {
        $this->_lang = strtolower($lang);
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function setSubject($subject, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $subject = htmlspecialchars($subject);
        $subject = $this->replaceTags($subject);
        parent::setSubject($subject, $charset, $encoding);
    }

    // Définit le contenu brut du message, avant encodage et traitements (pour les mails sans template)
    public function setRawBody($body)
    {
        $this->_rawBody = $body;
    }

    // Renvoie le contenu brut du message, avant encodage et traitements (pour les mails sans template)
    public function getRawBody()
    {
        if (isset($this->_rawBody)) {
            return $this->_rawBody;
        } else {
            return null;
        }
    }

    public function getDecodedSubject()
    {
        return iconv_mime_decode($this->getSubject(), 0, 'UTF-8');
    }

    /** create an email as an xml file
     * if debug is false, it will be located in path/unsent
     * if debug is true, it will be located in path/debug
     * path/unsent will be processed later by a script, which will send the mail
     * path/debug won't be processed, so the mail won't be really sent
     * @param bool $debug
     * @return bool
     * @throws Exception
     */
    public function write($debug = false)
    {
        /*  Status Codes
        *   1: success
        *   2: error: invalid working directory
        *   3: error: no recipients
        *   4: error: mail storage folder creation failed
        *   5: error: xml file creation failed
        *   6: error: xml file could not be written
        */

        if (null == $this->path) {
            $statusCode = 2;
            throw new Exception('Invalid working directory', $statusCode);
        }

        $headers = $this->getHeaders();

        if (!isset($headers['To']) && !isset($headers['Cc']) && !isset($headers['Bcc'])) {
            $statusCode = 3;
            throw new Zend_Mail_Exception('No recipient', $statusCode);
        }

        // create mail storage folder
        $storage_path = ($debug) ? $this->path . 'debug/' : $this->path . 'unsent/';
        $mailDirectory = $this->createMailDirectory($storage_path);

        if (!$mailDirectory) {
            $statusCode = 4;
            throw new Exception('Storage folder creation failed in: ' . $storage_path, $statusCode);
        }

        // init XML
        $xmlString = '<?xml version="1.0"?>' . PHP_EOL;

        $xmlString .= '<mail errors="0" charset="' . $this->getHeaderEncoding() . '">' . PHP_EOL;

        if (isset($headers['From'])) {
            $xmlString .= $this->extractSingle($headers, 'From');
        }
        if (isset($headers['Reply-To'])) {
            $xmlString .= $this->extractSingle($headers, 'Reply-To');
        }
        if (isset($headers['Return-Path'])) {
            $xmlString .= $this->extractSingle($headers, 'Return-Path');
        }
        if (isset($headers['To'])) {
            $xmlString .= $this->extractList($headers, 'To');
        }
        if (isset($headers['Cc'])) {
            $xmlString .= $this->extractList($headers, 'Cc');
        }
        if (isset($headers['Bcc'])) {
            $xmlString .= $this->extractList($headers, 'Bcc');
        }
        if (isset($headers['Disposition-Notification-To'])) {
            $xmlString .= $this->extractSingle($headers, 'Disposition-Notification-To');
        }
        $subject = $this->getSubject();
        if ($subject) {
            $xmlString .= "\t" . '<subject>' . $subject . '</subject>' . PHP_EOL;
        }

        // message body
        if ($this->hasATemplate()) {
            $this->setBodyHtml($this->renderTemplate($this->getTemplatePath(), $this->getTemplateName()));
        } else {
            $this->setBodyHtml($this->replaceTags($this->getRawBody()));
        }
        $bodyText = $this->getBodyText(true);
        $bodyHtml = $this->getBodyHtml(true);

        // plain text version
        if ($bodyText) {
            $charset = $this->getBodyText()->encoding;
            $xmlString .= "\t" . '<bodyText charset="' . $charset . '">' . $bodyText . '</bodyText>' . PHP_EOL;
        }
        // when there is no plain text version, but there is an html version,
        // create a plain text version from html version
        elseif ($bodyHtml) {
            $charset = $this->getBodyHtml()->encoding;
            $xmlString .= "\t" . '<bodyText charset="' . $charset . '">' . htmlspecialchars($this->htmlToText($bodyHtml)) . '</bodyText>' . PHP_EOL;
        }

        // HTML version
        if ($bodyHtml) {
            $charset = $this->getBodyHtml()->encoding;
            $bodyHtml = htmlspecialchars($bodyHtml);
            $xmlString .= "\t" . '<bodyHtml charset="' . $charset . '">' . $bodyHtml . '</bodyHtml>' . PHP_EOL;
        }

        // attachments
        if (count($this->attachments)) {
            $xmlString .= "\t" . '<files_list>' . PHP_EOL;
            foreach ($this->attachments as $attachment) {
                if (is_array($attachment)) {
                    $filepath = (array_key_exists('path', $attachment)) ? $attachment['path'] : null;
                    $filename = (array_key_exists('name', $attachment)) ? $attachment['name'] : null;
                } else {
                    $filepath = $attachment;
                    $filename = pathinfo($filepath)['basename'];
                }
                if (is_file($filepath)) {
                    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                    $type = $fileInfo->file($filepath);

                    copy($filepath, $storage_path . $mailDirectory . '/' . $filename);
                    $xmlString .= "\t\t" . '<file type="' . $type . '">' . $filename . '</file>' . PHP_EOL;
                }
            }
            $xmlString .= "\t" . '</files_list>' . PHP_EOL;
        }

        $xmlString .= '</mail>' . PHP_EOL;

        // create xml file
        $xmlFile = fopen($storage_path . $mailDirectory . '/mail.xml', 'w');
        if ($xmlFile) {
            if (fwrite($xmlFile, $xmlString)) {
                fclose($xmlFile);
                return true;
            } else {
                fclose($xmlFile);
                rmdir($storage_path . $mailDirectory);
                $statusCode = 6;
                throw new Exception("Failed to write XML file.", $statusCode);
            }
        } else {
            rmdir($storage_path . $mailDirectory);
            $statusCode = 5;
            throw new Exception("Failed to create XML file.", $statusCode);
        }
    }

    public function clearTags()
    {
        $this->tags = array();
    }

    public function addTag($name, $value)
    {
        $this->tags[$name] = $value;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTemplatePath()
    {
        return $this->_templatePath;
    }

    public function getTemplateName()
    {
        return $this->_templateName;
    }

    public function setTemplate($templatePath, $templateName)
    {
        $this->_templatePath = $templatePath;
        $this->_templateName = $templateName;
    }

    protected function hasATemplate()
    {
        $templatePath = $this->getTemplatePath();
        $templateName = $this->getTemplateName();

        if (isset($templatePath) && isset($templateName) && is_file($templatePath . '/' . $templateName)) {
            return true;
        }

        return false;
    }

    /**
     * Retourne le contenu du mail (pour le débug en développement)
     * @return string
     */
    public function getBody()
    {

        if ($this->hasATemplate()) {
            $body = $this->renderTemplate($this->getTemplatePath(), $this->getTemplateName());
        } else {
            $body = $this->replaceTags($this->getRawBody());
        }

        return htmlspecialchars($body);
    }

    public function getDecodedBody()
    {
        return htmlspecialchars_decode($this->getBody());
    }

    public function renderTemplate($templatePath, $templateName)
    {
        $templateContent = file_get_contents($templatePath . '/' . $templateName);

        if (!$templateContent) {
            return null;
        }

        $renderedTemplate = $this->replaceTags($templateContent);

        return $renderedTemplate;
    }

    public function replaceTags($text)
    {
        $tags = $this->getTags();
        $text = str_replace(array_keys($tags), array_values($tags), $text);
        $text = nl2br($text);
        $text = Ccsd_Tools::clear_nl($text);
        return $this->cleanText($text);
    }

    /**
     * Suppression des tags non remplacés
     * @param $text
     * @return mixed
     */
    public function cleanText($text)
    {
        return preg_replace('/%%[[:alnum:]_]+%%/', "", $text);
    }

    /**
     * Ajout d'un fichier joint au mail
     * @param $attachment string || array
     * $attachment peut être soit un string (chemin du fichier),
     * soit un array : ['name'=>$name, 'path'=>$path]
     */
    public function addAttachedFile($attachment)
    {
        $this->attachments[] = $attachment;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    protected function htmlToText($htmlText)
    {
        $textVersion = strip_tags(str_replace('</li>', '<br />', $htmlText), '<br>');
        $textVersion = preg_replace('#\s\s+#', ' ', $textVersion);
        $textVersion = preg_replace('#<br\s*/?\s*>#i', PHP_EOL, $textVersion);
        $textVersion = html_entity_decode($textVersion, ENT_QUOTES, 'UTF-8');
        return $textVersion;
    }

    private function extractList($array, $fieldname)
    {
        $xmlString = "\t<" . strtolower($fieldname) . '_list>' . PHP_EOL;
        $tmpString = '';

        foreach ($array[$fieldname] as $key => $value) {
            if (is_numeric($key)) {
                $tmpString .= "\t\t";
                preg_match('#(.*)\s\s*<(.*)>#', $value, $result);
                if ($result) {
                    $tmpString .= '<' . strtolower($fieldname) . '><name>' . trim($result[1]) . '</name><mail>' . trim($result[2]) . '</mail></' . strtolower($fieldname) . '>';
                } else {
                    $tmpString .= '<' . strtolower($fieldname) . '><mail>' . trim($value) . '</mail></' . strtolower($fieldname) . '>';
                }
                $tmpString .= PHP_EOL;
            }
        }
        $xmlString .= $tmpString;
        $xmlString .= "\t</" . strtolower($fieldname) . '_list>' . PHP_EOL;

        return $xmlString;
    }

    private function extractSingle($value, $fieldname)
    {
        $value = $value[$fieldname][0];
        $xmlString = "\t";

        preg_match('#(.*)\s\s*<(.*)>#', $value, $result);
        if ($result) {
            $xmlString .= '<' . strtolower($fieldname) . '><name>' . trim($result[1]) . '</name><mail>' . trim($result[2]) . '</mail></' . strtolower($fieldname) . '>';
        } else {
            $xmlString .= '<' . strtolower($fieldname) . '><mail>' . trim($value) . '</mail></' . strtolower($fieldname) . '>';
        }
        $xmlString .= PHP_EOL;

        return $xmlString;
    }

    /**
     * create a storage folder for this e-mail, as a subfolder of given path
     * @param $path
     * @return bool|string
     */
    private function createMailDirectory($path)
    {
        $mailDirectory = uniqid();
        if (mkdir($path . $mailDirectory, 0777, true)) {
            return $mailDirectory;
        }
        return false;
    }

    /**
     * check if application folders exist, and create them if they don't
     * @throws Exception
     */
    private function checkAppDirectory()
    {
        $folders = [
            $this->path,
            $this->path . 'unsent/',
            $this->path . 'sent/',
            $this->path . 'log/',
            $this->path . 'debug/'
        ];

        foreach ($folders as $folder) {
            if (!is_dir($folder) && !mkdir($folder)) {
                $statusCode = 4;
                throw new Exception('Storage folder creation failed in: ' . $folder, $statusCode);
            }
        }
    }

    /**
     * check e-mail address via SMTP
     * @param $email
     * @param bool $short_response
     * @return array|bool
     */
    public static function validateMail($email, $short_response = true)
    {
        $result = array("valid" => false);
        $errors = array();

        // Email address (format) validation
        if (empty($email)) {
            $errors = array("Email address is required.");
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors = array("Invalid email address.");
        } else {
            list($username, $hostname) = split('@', $email);
            if (function_exists('getmxrr')) {
                if (getmxrr($hostname, $mxhosts, $mxweights)) {
                    $result['mx_records'] = array_combine($mxhosts, $mxweights);
                    asort($result['mx_records']);
                } else {
                    $errors = array("No MX record found.");
                }
            }
            foreach ($mxhosts as $host) {
                $fp = @fsockopen($host, "25", $errno, $errstr, 1);
                if ($fp) {
                    $data = fgets($fp);
                    $code = substr($data, 0, 3);
                    if ($code == '220') {
                        //$sender_domain = split('@', $this->options['sender']);
                        fwrite($fp, "HELO ccsd.cnrs.fr\r\n");
                        fread($fp, 4096);
                        fwrite($fp, "MAIL FROM: <abuse@ccsd.cnrs.fr>\r\n");
                        fgets($fp);
                        fwrite($fp, "RCPT TO:<{$email}>\r\n");
                        $data = fgets($fp);
                        $code = substr($data, 0, 3);
                        $result['response'] = array("code" => $code, "data" => $data);
                        fwrite($fp, "QUIT\r\n");
                        fclose($fp);
                        switch ($code) {
                            case "250":  // We're good, so exit out of foreach loop
                            case "421":  // Too many SMTP connections
                            case "450":
                            case "451":  // Graylisted
                            case "452":
                                $result['valid'] = true;
                                break 2;  // Assume 4xx return code is valid.
                            default:
                                $errors[] = "({$host}) RCPT TO: {$code}: {$data}\n";
                        }
                    } else {
                        $errors[] = "MTA Error: (Stream: {$data})";
                    }
                } else {
                    $errors[] = "{$errno}: $errstr";
                }
            }
        }
        if (!empty($errors)) {
            $result['errors'] = $errors;
        }
        return ($short_response) ? $result['valid'] : $result;
    }

}
