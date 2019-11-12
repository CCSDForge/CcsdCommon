<?php

/**
 * Class Ccsd_Archive
 *
 * Class trop tournee HAL:
 * DEPOT doit etre un Hal_Document
 * A re-ecrire en utilisant une interface:
 *     interface Archive_Document_Interface
 *     Hal_Document devra implementer l'interface
 *
 * cela enlevera tout specificite sur Hal.
 */
class Ccsd_Archive
{
    /*
     * Identifiant du lot au CCSD
     */

    const TABLE_DONNEES = "DOC_ARCHIVE";
    const TABLE_FICHIERS = "DOC_FILE";

    /*
     * Instance de HAL_Document
     */
    const VUE_DONNEES = "VUE_ARCHIVE";

    /*
     * Statut du lot en cours
     */
    const ARCHIVE_PRISE_EN_CHARGE = 0;
    /**
     * ARCHIVE dont les donnees sont a mettre a jour au CINES
     */
    const ARCHIVE_A_METTRE_A_JOUR = 1;
    /**
     * ARCHIVE à migrer, mise a jour des fichiers au CCSd
     */
    const ARCHIVE_A_MIGRER = 2;
    /**
     * ARCHIVE envoyée au CINES
     */
    const ARCHIVE_ENVOYEE = 3;
    /**
     * ARCHIVE reçue au CINES
     */
    const ARCHIVE_RECUE = 4;

    /*
     * Données pour le transfert
     */
    /**
     *  ARCHIVE acceptee au CINES
     */
    const ARCHIVE_ACCEPTEE = 5;
    /**
     * ARCHIVE rejetee
     */
    const ARCHIVE_REJETEE = 6;

    /*
     * Tableau des fichiers à archiver
     */
    /**
     *  ARCHIVE non archivable (cas des dépôt HTML - décision de ne pas les envoyer)
     */
    const DEPOT_NON_ARCHIVABLE = 7;

    /*
     * Chemin des fichiers descriptifs créés pour le lot
     * Au format CINES
     */
    /**
     * ARCHIVE dont le contributeur a declare ne pas vouloir envoyer son depot a l'archivage
     */
    const ARCHIVE_A_NE_PAS_TRANSMETTRE = 8;

    /*
     * Chemin des fichiers descriptifs créés pour le lot
     * Au format CCSD
     */
    /**
     *  ARCHIVE dont les fichiers pdf ont été envoyé sur clio pour une tentative de correction
     * en vue de devenir archivable - campagne des improperly formed date
     */
    const ARCHIVE_EN_CORRECTION = 9;

    /*
     * Chemin pour les logs
     */
    /**
     * ARCHIVE dont les fichiers pdf ont été envoyé sur clio pour correction sans succes
     */
    const ARCHIVE_NON_CORRIGEE = 10;


    /**
     * ARCHIVE en test sur facile
     */
    const ARCHIVE_EN_TEST = 11;
    /**
     * ARCHIVE testée avec succes sur facile
     */
    const ARCHIVE_ELIGIBLE = 12;


    /*
     * Etat des lots
     */

    /**
     * Taille maximale des fichiers à envoyer à facile.cines.fr
     */
    const FACILE_MAX_FILESIZE = 100000000;


    const CONVERTED_FILES_PATH = '/nas/archivage_hal/';

    /*
     * ARCHIVE qui répond au critère de format et de date pour être envoyée au CINES
     */
    public static $_ACTIONS = [
        "PRISE_EN_CHARGE",
        "MISE_A_JOUR",
        "MIGRATION",
        "ENVOI",
        "RECEPTION",
        "ARCHIVAGE",
        "REJET",
        "CONVERSION",
        "TEST_ELLIGIBILITE",
        "ARCHIVE_A_ENVOYER",
        "PB TRANSFERT"
    ];
    public static $_URL_SCHEMA_SIP = "https://www.cines.fr/pac/sip.xsd";
    public static $_URL_SCHEMA_METIER = "https://hal.archives-ouvertes.fr/documents/aofr.xsd";
    public static $_TYPE_FORMATS_ARCHIVABLES = [
        'text' => [
            'application/pdf'
        ],
        'image' => [
            '^image'
        ],
        'audio' => [
            '^audio'
        ],
        'video' => [
            '^video',
            'application/ogg'
        ]
    ];
    /**
     * URL du webservice de validation de fichier du CINES 'FACILE'
     * @var string
     */
    public static $cinesFacileServerUrl = 'https://facile.cines.fr/xml';
    /**
     * correspondance type mime et select facile
     * @var array
     */
    private static $_cinesFacileFileTypes = [
        'text/xml' => 'XML',
        'application/pdf' => 'PDF',
        'application/x-pdf' => 'PDF',
        'image/jpeg' => 'JPEG',
        'image/gif' => 'GIF',
        'image/tiff' => 'TIFF',
        'image/png' => 'PNG',
        'image/svg+xml' => 'SVG',
        'audio/x-wav' => 'WAV',
        'audio/mp4' => 'MPEG-4',
        'video/mp4' => 'MPEG-4',
        'video/x-matroska' => 'MKV',
        'audio/x-matroska' => 'MKV',
        'audio/ogg' => 'OGG',
        'video/ogg' => 'OGG',
        'application/ogg' => 'OGG'
    ];
    public static $_ENCODING_TYPE = [
        'PCM',
        'FLAC',
        'AVC/ACC',
        'AVC/FLAC',
        'AVC',
        'AAC',
        'THEORA/VORBIS'
    ];
    public static $_FORMATS_MIME_AUTRE = [
        'XML' => 'text/xml'
    ];
    public static $_PERSONNE = "Automate";
    public static $_DUREE_CONSERVATION = "P1000Y";

    public static $_REPERTOIRE_LOG = ARCHIVAGE_LOG_PATH;
    public static $_PREFIXE_SAUVEGARDE_AVANT_CONVERSION = "ORI1_";
    public static $prefixe_version_convertie_archivable = "archivable_";
    public static $_REPERTOIRE_BASE_DEPOT = ARCHIVAGE_SSH_PATH; // 100Mo
    public static $_PREFIXE_REPERTOIRE_DEPOT = "DOCUMENT_";
    public static $_REPERTOIRE_BASE_DEPOT_FICHIERS = "DEPOT";
    public static $_SOUS_REPERTOIRE_FICHIERS_METIERS = "DESC";
    public static $_VERSION_PDF_NON_ARCHIVABLE = 1.3;

    /*
     * Données concernant la connexion sur le serveur distant
     */
    public static $_MAJ = "maj";
    public static $_NOUVELLE_VERSION = "version";

    /*
     * Données concernant les repertoires de travail locaux et distants
     */
    public static $_PRODUCTEUR = "Producteur";
    public static $_ARCHIVEUR = "PAC";
    public static $_ENTETE_SIP = [
        "production" => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<pac xmlns=\"http://www.cines.fr/pac/sip\"\n  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n  xsi:schemaLocation=\"http://www.cines.fr/pac/sip http://www.cines.fr/pac/sip.xsd\">\n",
        "development" => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<pac xmlns=\"http://www.cines.fr/pac/test/sip\"\n   xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n  xsi:schemaLocation=\"http://www.cines.fr/pac/test/sip http://www.cines.fr/pac/test/sip.xsd\">\n",
        "testing" => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<pac xmlns=\"http://www.cines.fr/pac/test/sip\"\n   xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n  xsi:schemaLocation=\"http://www.cines.fr/pac/test/sip http://www.cines.fr/pac/test/sip.xsd\">\n"
    ];
    /**
     * Pour mémoire : textes explicites des erreurs
     */
    public static $_ERREURS_CINES = [
        "E0001" => "Le fichier sip.xml est absent",
        "E0002" => "Le fichier sip.xml invalide par rapport au schéma http://www.cines.fr/pac/sip.xsd",
        "E0003" => "Un ou plusieurs fichiers déclarés dans le sip.xml sont manquants dans le document",
        "E0004" => "Un ou plusieurs fichiers sont présents dans le document mais ne sont pas déclarés dans le sip.xml",
        "E0005" => "Fichier bien formé mais non valide",
        "E0006" => "Un ou plusieurs fichiers ont une empreinte numérique incorrecte",
        "E0007" => "Encodage non autorisé pour ce format de fichier",
        "E0008" => "le format de fichier n'existe pas dans la Bibliothèque d'Information de Représentation",
        "E0100" => "L’encodage d’un ou plusieurs noms de fichier est incorrect",
        "E0101" => "Les droits d’accès sur les fichiers sont insuffisants",
        "E0102" => "La vérification d’un ou plusieurs fichiers a provoqué une erreur système",
        "E0103" => "Erreur de timeout de la demande d'archivage",
        "E0104" => "L'unicité du versement n'est pas respectée ; le versement est déjà archivé"
    ];

    /**
     * Données concernant le compte mail utilisé pour nous renvoyer les états d'archivage des données transmises
     */
    public static $_MAIL_FROM = [
        "production" => "Production",
        "development" => "Test",
        "testing" => "Test"
    ];

    public $forceConversion;
    /**
     * @var Ccsd_Archivage_Connection
     */
    protected $_connectionArchive;

    /*
     * Donnéees concernant le type d'echange
     */
    private $DOCID = 0;
    /**
     * Identifiant du lot au CINES
     * @var int
     */
    private $IDPAC = 0;

    /*
     * Donnéees concernant le type d'echange
     */
    /**
     * @var Hal_Document  // A remplacer par un type interface....
     */
    private $DEPOT = null;
    private $STATUT = -1;
    private $DATE_ACTION = null;
    private $ACTION = null;
    private $CODE = null;
    private $INSTANCE = null;
    private $FICHIERS = [];
    private $FICHIERSIP = "";
    private $FICHIERMETIER = "";
    private $FICHIERLOGS = "";

    /**
     *
     * @var string
     */
    private $_planDeClassement;


    /**
     * curl handler
     * @var
     */
    private $_curlHandler;

    /**
     * Ccsd_Archive constructor.
     * @param int $docid
     * @param array|false|string $env
     * @param Ccsd_Archivage_Connection|null $connectionArchive
     */
    function __construct($docid = 0, $env = APPLICATION_ENV, Ccsd_Archivage_Connection $connectionArchive = null)
    {
        if ($connectionArchive == null) {
            $connectionArchive = new Ccsd_Archivage_Connection();
        }

        $this->setConnectionArchive($connectionArchive);


        if (APPLICATION_ENV == 'development') {
            $this->FICHIERLOGS = realpath(sys_get_temp_dir()) . '/' . "logs_" . $env . date("Y-m-d") . ".txt";
        } else {
            $this->FICHIERLOGS = static::$_REPERTOIRE_LOG . "logs_" . $env . date("Y-m-d") . ".txt";
        }

        if ($docid != 0) {
            $this->DOCID = $docid;
            $this->initialise();
        }
    }

    private function initialise()
    {
        if (!$this->rechercheInfoBase()) {
            // premier essai d'archivage
            $this->DEPOT = Hal_Document::find($this->DOCID, '', '', true);
            if ($this->DEPOT === false) {
                // Pb de chargement du dépôt fin de la tentative d'archivage
                $this->logAction("find du depôt impossible - fin procédure");
                $this->STATUT = static::DEPOT_NON_ARCHIVABLE;
                $this->ACTION = static::$_ACTIONS [6];
                $this->CODE = "ECHEC FIND DEPOT";
            } else {
                if (!$this->isArchivable($this->DEPOT)) {
                    $this->STATUT = static::DEPOT_NON_ARCHIVABLE;
                    $this->ACTION = static::$_ACTIONS [6];
                } else {
                    $this->STATUT = static::ARCHIVE_PRISE_EN_CHARGE;
                    $this->ACTION = static::$_ACTIONS [0];
                    $this->FICHIERSIP = $this->DEPOT->getRacineCache() . '/' . $this->DOCID . '.sip';
                    $this->FICHIERMETIER = $this->DEPOT->get('tei', false) . ".xml";
                }
            }
            $this->INSTANCE = !($this->DEPOT) ? "Inconnu" : $this->DEPOT->getInstance();
            $this->DATE_ACTION = date("Y-m-d H:i:s");
            $this->sauvegardeBase();
        } else {
            $this->FICHIERSIP = $this->DEPOT->getRacineCache() . '/' . $this->DOCID . '.sip';
            $this->FICHIERMETIER = $this->DEPOT->get('tei', false) . ".xml";
        }
    }

    /**
     * @return bool
     */
    function rechercheInfoBase()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES)->where('DOCID = ?', $this->DOCID)->where('CURRENT = 1');

        $resultat = $db->fetchRow($sql);

        if ($resultat) {
            $this->IDPAC = $resultat ['IDPAC'];
            $this->DEPOT = Hal_Document::find($this->DOCID);
            $this->ACTION = $resultat ['ACTION'];
            $this->DATE_ACTION = $resultat ['DATE_ACTION'];
            $this->STATUT = $resultat ['STATUT'];
            $this->INSTANCE = $resultat ['INSTANCE'];
            return true;
        }

        return false;
    }

    /**
     * @param string $texte
     * @param string $nomFichier
     * @param bool $verbose
     */
    function logAction($texte, $nomFichier = "", $verbose = true)
    {
        $nomFichier = (strlen($nomFichier) == 0) ? $this->FICHIERLOGS : $nomFichier;
        $ligne = date("Y-m-d G:i:s") . " - DOCID : " . $this->DOCID . " - " . $texte;
        @file_put_contents($this->FICHIERLOGS, $ligne . "\n", FILE_APPEND);
        if ($verbose) {
            echo $ligne . PHP_EOL;
        }
    }

    function isArchivable(Hal_Document $depot)
    {
        if ($depot->getFormat() != Hal_Document::FORMAT_FILE) {
            // le dépôt n'est pas de type file
            $this->CODE = "DEPOT PAS DE TYPE FILE";
            return false;
        } else {
            // On descend au niveau des fichiers et on regarde si le main est de type file (et non src ou annex)
            $fichierPrincipal = $depot->getDefaultFile();
            // Pas de fichier principal ou son type n'est pas file
            if ((!$fichierPrincipal) || ($fichierPrincipal->getType() != Hal_Settings::FILE_TYPE)) {
                $this->CODE = "FICHIER PRINCIPAL DE TYPE SRC OU ANNEX";
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     * @throws Zend_Db_Adapter_Exception
     */
    function sauvegardeBase()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        // je remets les current à 0
        $donnees = [
            'CURRENT' => 0
        ];
        $db->update(self::TABLE_DONNEES, $donnees, 'DOCID = ' . $this->DOCID);

        $infos = [];
        $infos ['DOCID'] = $this->DOCID;
        $infos ['IDPAC'] = $this->IDPAC;
        $infos ['DATE_ACTION'] = $this->DATE_ACTION;
        $infos ['STATUT'] = $this->STATUT;
        $infos ['CODE_ERREUR'] = $this->CODE;
        $infos ['ACTION'] = $this->ACTION;
        $infos ['CURRENT'] = 1;
        $infos ['INSTANCE'] = $this->INSTANCE;

        $db->insert(self::TABLE_DONNEES, $infos);

        return ($db->lastInsertId($infos));
    }


    /**
     * @param string $start // Date
     * @param string $end // Date
     * @param array $statut
     * @return Zend_Paginator
     * @throws Zend_Paginator_Exception
     */
    static function getPaginator($start = null, $end = null, $statut = [])
    {
        $conditions = [
            "CURRENT = 1"
        ];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES, [
            'DOCID'
        ]);

        if (count($statut)) {
            $conditions [] = "STATUT IN (" . implode(',', $statut) . ")";
        }
        if (isset($start)) {
            $conditions [] = "DATE_ACTION >= '" . $start . "'";
        }
        if (isset($end)) {
            $conditions [] = "DATE_ACTION <= '" . $end . "'";
        }

        foreach ($conditions as $condition) {
            $sql->where($condition);
        }

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
        return ($paginator);
    }

    /**
     * @param string $start //date
     * @param string $end //date
     * @param array $statut
     * @param int $page_number
     * @param int $max_records_per_page
     * @return array
     */


    static function getListe($start = null, $end = null, $statut = [], $page_number = 1, $max_records_per_page = 0)
    {
        $docids = [];
        // Je m'interesse uniquement aux derniers états
        $conditions = [
            "CURRENT = 1"
        ];

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES, [
            'DOCID'
        ]);

        if (count($statut)) {
            $conditions [] = "STATUT IN (" . implode(',', $statut) . ")";
        }
        if (isset($start)) {
            $conditions [] = "DATE_ACTION >= '" . $start . "'";
        }
        if (isset($end)) {
            $conditions [] = "DATE_ACTION <= '" . $end . "'";
        }

        foreach ($conditions as $condition) {
            $sql->where($condition);
        }

        if ($max_records_per_page != 0) {
            $depart = ($page_number - 1) * $max_records_per_page;
            $sql->limit($max_records_per_page, $depart);
        }

        foreach ($db->fetchAll($sql) as $row) {
            $docids [] = $row ['DOCID'];
        }

        return ($docids);
    }


    static function getEtats()
    {
        // tableau des etats possibles
        return [
            static::ARCHIVE_PRISE_EN_CHARGE,
            static::ARCHIVE_ENVOYEE,
            static::ARCHIVE_RECUE,
            static::ARCHIVE_ACCEPTEE,
            static::ARCHIVE_REJETEE,
            static::ARCHIVE_ELIGIBLE
        ];
    }

    /**
     * Retourne les informations sur l'archivage d'un fichier
     * @param $fileId
     * @return mixed
     */
    static function getFileInfo($fileId)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(Hal_Document_File::TABLE, [
            'SEND',
            'ARCHIVED'
        ])->where('FILEID = ?', $fileId);

        return $db->fetchRow($sql);
    }

    /**
     * @return Hal_Document
     */
    public function getDocument()
    {
        return $this->DEPOT;
    }

    /**
     * @return array
     */
    function getFileIdsEnvoye()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES, [
            'FILEID'
        ])->where('DOCID = ?', $this->DOCID)->where('SEND = 1');
        $tableauFileIds = $db->fetchCol($sql);
        return ($tableauFileIds);
    }

    /**
     * @return int
     */
    public function getIDPAC()
    {
        return $this->IDPAC;
    }

    /**
     * Fichier A archiver
     * On passe en revue, les fichiers potentiellement archivables
     * On vérifie l'eligibilité du fichier principal dans facile
     * Si Ok le lot va être envoyé
     * On passe en revue tous les autres fichiers possibles en annexes
     * Si un fichier ne passe pas on passe au suivant
     * La fonction getFichiers renvoient les fichiers qui ont passé avec succès le test de l'interafce facile et
     * qui seront donc présent dans le fichier SIP
     */

    public function getFichiers()
    {
        return $this->FICHIERS;
    }

    /**
     * @param bool $miseAjour
     * @param bool $sansAnnexe
     * @param string $env
     * @return bool
     */
    function envoiArchivage($miseAjour = false, $sansAnnexe = false)
    {
        if (!$miseAjour) {
            if (!$this->preparationFichiersAArchiver($sansAnnexe)) {
                $this->logAction("Lot pour lequel il n'y a rien à envoyer");
                return false;
            }
        } else {
            // Cas d'une mise a jour
            if ($this->IDPAC == 0) {
                $this->logAction("Essai de mise a jour sur un lot non archive.. :-( ");
            }
            $this->STATUT = self::ARCHIVE_A_METTRE_A_JOUR;
            $this->ACTION = static::$_ACTIONS [1];
            $this->DATE_ACTION = date("Y-m-d H:i:s");
            $this->sauvegardeBase();
        }
        if (!$this->creationFichierSIP($miseAjour)) {
            $this->logAction("Lot pour lequel on a rencontré un pb dans l'écriture des fichiers de metadonnees");
            return false;
        }
        // verification du fichier metier produit
        // fichier METIER
        // pas de verification du SIP reference liste code language non geree
        if (!$this->valide($this->getFichierMetier())) {
            // rejet METIER invalide
            $this->STATUT = static::ARCHIVE_REJETEE;
            $this->CODE = "Fichier METIER non valide";
            $this->ACTION = static::$_ACTIONS [6];
            $this->DATE_ACTION = date("Y-m-d H:i:s");
            $this->sauvegardeBase();
            return false;
        }

        return $this->scpCINES($miseAjour);
    }

    /**
     * Creation du tableau des fichiers à envoyer et mise a jout des statuts du depot
     * Si le tableau est vide : pas d'envoi au CINES
     * @param bool $sansAnnexe
     * @return bool
     */
    public function preparationFichiersAArchiver($sansAnnexe = false)
    {
        $this->logAction("Statut : " . $this->getSTATUT());
        switch ($this->getSTATUT()) {
            case static::ARCHIVE_PRISE_EN_CHARGE :
            case static::ARCHIVE_EN_TEST :
            case static::ARCHIVE_REJETEE :
            case static::ARCHIVE_ELIGIBLE :
                break;
            default :
                $this->logAction("Fichier à ne pas tester");
                return false;
        }

        $this->FICHIERS = [];
        $this->ACTION = static::$_ACTIONS [8];
        $this->STATUT = static::ARCHIVE_EN_TEST;
        $this->DATE_ACTION = date("Y-m-d H:i:s");
        $this->sauvegardeBase();

        $repBase = $this->DEPOT->getRacineDoc();

        // Recherche en base des fichiers associés au dépôt
        $fichiers = $this->DEPOT->getFiles();

        $i = 0;

        /* @var $fichier Hal_Document_File */

        foreach ($fichiers as $fichier) {

            // On n'archive pas les fichiers de type source
            if ($fichier->getType() == Hal_Settings::FILE_TYPE_SOURCES ) {
                continue;
            }

            // Façon de shunter les annexes
            if (($fichier->getType() == Hal_Settings::FILE_TYPE_ANNEX) && $sansAnnexe) {
                continue;
            }

            // test de la validité du fichier sur facile
            $nomComplet = $repBase . $fichier->getName();

            $fileMimeType = $this->getArchivageMimeType($nomComplet);
            $this->logAction("Fichier de type mime : " . $fileMimeType);

            if (!$this->forceConversion) {
                $resultat = $this->verifie_by_facile($nomComplet, $fileMimeType);
            } else {
                //va forcer la conversion
                $this->logAction('La conversion du fichier est demandée sans verifier si il est archivable.');
                $resultat ['archivable'] = false;
            }

            if ($resultat ['archivable']) {
                // Le fichier est valide pour l'archivage
                // Je le stocke dans le tableau des fichiers du lots
                if (strcasecmp($fileMimeType, "application/pdf") || ($this->version_pdf($nomComplet) > static::$_VERSION_PDF_NON_ARCHIVABLE)) {
                    $this->logAction("Ajout du fichier " . $nomComplet . " à archiver de type " . $fileMimeType);
                    $this->FICHIERS ['nom_fichier'] [$i] = $nomComplet;
                    $this->FICHIERS ['id_cible'] [$i] = $fichier->getFileid() . "." . $fichier->getExtension();
                    $this->FICHIERS ['type_mime'] [$i] = $fileMimeType;
                    $this->FICHIERS ['md5'] [$i] = $fichier->getMd5();
                    $i++;
                    continue;
                }
            }

            // Cas d'un fichier pdf - il n'est pas valide on essaie de le convertir
            if (0 == strcasecmp($fileMimeType, "application/pdf")) {

                $version = $this->version_pdf($nomComplet);
                $this->logAction("Version pdf du fichier " . $fichier->getName() . " : " . $version);
                // on cree un fichier de travail quoiqu'il arrive
                $nomTravail = $repBase . static::$_PREFIXE_SAUVEGARDE_AVANT_CONVERSION . $fichier->getFileid() . "." . $fichier->getExtension();

                $resOfCopy = copy($nomComplet, $nomTravail);
                if ($resOfCopy === false) {
                    // Probleme au demarrage
                    // Fin de l'essai d'archivage
                    $this->logAction("Fichier " . $fichier->getName() . " impossible à copier");
                    $this->STATUT = static::ARCHIVE_REJETEE;
                    $this->CODE = "Fichier " . $fichier->getName() . " IMPOSSIBLE A COPIER";
                    $this->ACTION = static::$_ACTIONS [6];
                    $this->DATE_ACTION = date("Y-m-d H:i:s");
                    $this->sauvegardeBase();
                    $this->FICHIERS = [];
                    return (false);
                }


                $this->logAction("Copy ok de Nom original : " . $nomComplet . " vers Nom de travail : " . $nomTravail);

                $nomFichierConverti = $this->convertPDFtoPDFa($nomTravail, $fichier->getFileid());

                $this->logAction("Fin conversion, résultat : " . $nomFichierConverti);


                // pas de conservation du fichier de travail
                unlink($nomTravail);
                $this->logAction('Suppression du fichier de travail après conversion : ' . $nomTravail);

                if (!is_file($nomFichierConverti)) {

                    if ($fichier->getType() == Hal_Settings::FILE_TYPE_ANNEX) {
                        // La conversion ne fonctionne pas mais c'est une annexe, on continue
                        $this->logAction("La conversion du fichier d'annexe : " . $fichier->getName() . " n'a pas abouti, il ne sera pas intégré au lot.");
                        continue;
                    } else {
                        // La conversion concerne un fichier principal, on ne pourra pas envoyer le lot
                        // Fin de l'essai d'archivage
                        $this->logAction("Fichier " . $fichier->getName() . " non convertible, lot non archivable.");
                        $this->STATUT = static::ARCHIVE_REJETEE;
                        $this->CODE = "Fichier " . $fichier->getName() . " PDF " . $version . " NON CONVERTIBLE";
                        $this->ACTION = static::$_ACTIONS [6];
                        $this->DATE_ACTION = date("Y-m-d H:i:s");
                        $this->sauvegardeBase();
                        $this->FICHIERS = [];
                        return (false);
                    }
                } else {


                    //si on a forcé la conversion on essaie d'archiver sans vérifier avec facile.cines.fr
                    if (!$this->forceConversion) {
                        // on integre le nouveau fichier au lot si il est valide
                        $this->logAction("Test facile de : " . $nomFichierConverti);
                        $resultat2 = $this->verifie_by_facile($nomFichierConverti, $fileMimeType);
                    } else {
                        // quel est le rapport ? il peut être converti et pas archivable
                        $this->logAction('La conversion du fichier a été forcée, on ne teste pas si il est archivable');
                        $resultat2 ['archivable'] = true;
                    }

                    if ($resultat2 ['archivable']) {


                        $convertedDocDir = self::getConvertedDocDir($this->DOCID);
                        if (!is_dir($convertedDocDir)) {
                            $mkdirRes = mkdir($convertedDocDir, 0755, true);
                            if (!$mkdirRes) {
                                $this->logAction("Problème de création du répertoire " . $convertedDocDir);
                                $this->STATUT = static::ARCHIVE_REJETEE;
                                $this->CODE = "Problème de création du répertoire de fichier converti";
                                $this->ACTION = static::$_ACTIONS [6];
                                $this->DATE_ACTION = date("Y-m-d H:i:s");
                                $this->sauvegardeBase();
                                $this->FICHIERS = [];
                                return false;
                            }
                        }

                        $nomCompletVersionArchivable = self::$prefixe_version_convertie_archivable . $fichier->getName();
                        $destOfConvertedFile = $convertedDocDir . $nomCompletVersionArchivable;

                        $resOfCopyVersionArchivable = rename($nomFichierConverti, $destOfConvertedFile);

                        // copie du fichier qui est désormais archivable dans le répertoire où on garde les archives de fichiers convertis
                        if ($resOfCopyVersionArchivable === false) {
                            $this->logAction("Problème de déplacement de " . $nomFichierConverti . " vers " . $destOfConvertedFile);
                            $this->STATUT = static::ARCHIVE_REJETEE;
                            $this->CODE = "Impossible de renommer le fichier résultant de la conversion";
                            $this->ACTION = static::$_ACTIONS [6];
                            $this->DATE_ACTION = date("Y-m-d H:i:s");
                            $this->sauvegardeBase();
                            $this->FICHIERS = [];
                            return false;
                        } else {
                            $this->logAction("OK: Déplacement de " . $nomFichierConverti . " vers " . $destOfConvertedFile);
                        }

                        unset($resOfCopyVersionArchivable);
                        $this->logAction("Pour l'archivage utilisation du fichier archivable : " . $nomCompletVersionArchivable . " au lieu de l'original " . $fichier->getName());
                        $this->FICHIERS ['nom_fichier'] [$i] = $destOfConvertedFile;
                        $this->FICHIERS ['id_cible'] [$i] = $fichier->getFileid() . "." . $fichier->getExtension();
                        $this->FICHIERS ['type_mime'] [$i] = $fileMimeType;
                        $this->FICHIERS ['md5'] [$i] = md5_file($destOfConvertedFile);
                        $i++;

                        // supprime le fichier de travail
                        if (is_readable($repBase . static::$_PREFIXE_SAUVEGARDE_AVANT_CONVERSION . $fichier->getName())) {
                            unlink($repBase . static::$_PREFIXE_SAUVEGARDE_AVANT_CONVERSION . $fichier->getName());
                            $this->logAction("Suppression de : " . $repBase . static::$_PREFIXE_SAUVEGARDE_AVANT_CONVERSION . $fichier->getName());
                        }

                    } else {
                        // fichier converti toujours pas archivable
                        if ($fichier->getType() == Hal_Settings::FILE_TYPE_ANNEX) {
                            // La conversion ne fonctionne pas mais c'est une annexe, on continue
                            $this->logAction("La conversion du fichier d'annexe ne sert à rien : " . $fichier->getName() . " fichier converti non envoyé.");
                            continue;
                        } else {
                            $this->logAction("Conversion de " . $fichier->getName() . " inefficace, erreur sur Facile du fichier converti lot en rejet\n" . $resultat2 ['message']);
                            $this->STATUT = static::ARCHIVE_REJETEE;
                            $this->CODE = "Fichier converti aussi en erreur pour " . $fichier->getName() . "\n" . $resultat2 ['message'];
                            $this->ACTION = static::$_ACTIONS [6];
                            $this->DATE_ACTION = date("Y-m-d H:i:s");
                            $this->sauvegardeBase();
                            $this->FICHIERS = [];
                            return false;
                        }
                    }
                }
                // fin procedure pour les fichiers pdf
            } else {
                // cas d'un fichier d'un format autre que PDF non éligible sur Facile
                $this->logAction("Erreur sur facile pour " . $fichier->getName() . $resultat ['message']);
                $this->STATUT = static::ARCHIVE_REJETEE;
                $this->CODE = "Fichier " . $fichier->getName() . "\n" . $resultat ['message'];
                $this->ACTION = static::$_ACTIONS [6];
                $this->DATE_ACTION = date("Y-m-d H:i:s");
                $this->sauvegardeBase();
                $this->FICHIERS = [];
                return (false);
            }
            // fin de la boucle sur les fichiers du dépôt
        }
        // Fin de la préparation des fichiers - pas de sortie en erreur
        $this->logAction("Lot éligible " . $this->DOCID);
        $this->STATUT = static::ARCHIVE_ELIGIBLE;
        $this->ACTION = static::$_ACTIONS [9];
        $this->DATE_ACTION = date("Y-m-d H:i:s");
        $this->sauvegardeBase();
        return (true);
    }

    public function getSTATUT()
    {
        return $this->STATUT;
    }

    private function getArchivageMimeType($filename)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimeType;
    }

    /**
     *  Envoi du fichier sur l'interface facile avant de l'envoyer à l'archivage
     * @param $nomCompletFichier
     * @param $typeMime
     * @return array
     */
    function verifie_by_facile($nomCompletFichier, $typeMime)
    {
        if (is_file($nomCompletFichier)) {
            if (!array_key_exists($typeMime, $this->getCinesFacileFileTypes())) {
                return [
                    'archivable' => false,
                    'message' => ' type_fichier_inconnu ' . $typeMime
                ];
            }

            if (filesize($nomCompletFichier) > static::FACILE_MAX_FILESIZE) {
                return [
                    'archivable' => true,
                    'message' => 'Fichier non testé avec FACILE, poids trop important'
                ];
            }


            // vérification avec facile.cines.fr
            $curlHandler = $this->getCurlHandler();
            if ($curlHandler == null) {
                $curlHandler = curl_init(self::$cinesFacileServerUrl);
                curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curlHandler, CURLOPT_POST, 1);
                curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curlHandler, CURLOPT_TIMEOUT, 300); //timeout in seconds
                $this->setCurlHandler($curlHandler);
            }

            $fichier = curl_file_create($nomCompletFichier, null, basename($nomCompletFichier));
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, [
                'format' => $this->getCinesFacileFileTypes() [$typeMime],
                'file' => $fichier
            ]);


            curl_setopt($curlHandler, CURLOPT_USERAGENT, 'CCSD Archivage HAL https://www.ccsd.cnrs.fr/ ccsd-archivage@ccsd.cnrs.fr');


            $this->logAction("Format " . $this->getCinesFacileFileTypes() [$typeMime]);
            $this->logAction("File " . $nomCompletFichier);

            if (($response = curl_exec($curlHandler)) == '') {

                $errno = curl_errno($curlHandler);
                $error_message = curl_strerror($errno);
                curl_close($curlHandler);
                $this->logAction("cURL error ({$errno}): {$error_message}", $errno);

                return [
                    'archivable' => false,
                    'message' => $error_message
                ];
            }


            $responsePourXml = preg_replace("/éèê/", "e", utf8_decode(trim($response)));

            if (($xml = simplexml_load_string($responsePourXml)) === false) {
                $this->logAction($responsePourXml . "- Reponse complete...false ");
                return [
                    'archivable' => false,
                    'message' => 'response_cines_nok page renvoyee'
                ];
            }

            if (((string)$xml->valid == "true") && ((string)$xml->wellFormed == "true")) {
                return [
                    'archivable' => true,
                    'message' => 'Ok'
                ];
            } else {
                $monTableau = (array)$xml;
                $this->logAction(implode(" - ", $monTableau));
                $maChaine = "";
                $proprietes = [
                    "valid",
                    "wellformed",
                    "version",
                    "format",
                    "message"
                ];
                foreach ($proprietes as $cle) {
                    $maChaine .= isset($monTableau [$cle]) ? " - " . $cle . " -> " . $monTableau [$cle] : "";
                }
                return [
                    'archivable' => false,
                    'message' => $maChaine
                ];
            }
        } else {
            return [
                'archivable' => false,
                'message' => 'pb_pas_de_fichier ' . $nomCompletFichier
            ];
        }
    }

    public function getCurlHandler()
    {
        return $this->_curlHandler;
    }

    public function setCurlHandler($curlHandler)
    {
        $this->_curlHandler = $curlHandler;
        return $this;
    }

    function version_pdf($filename)
    {
        $version = 0;
        foreach ($this->get_PDF_info($filename) as $infos) {
            if (is_array($infos) && array_key_exists('PDF version', $infos)) {
                $version = $infos ['PDF version'];
            }
        }
        return ((float)$version);
    }

    function get_PDF_info($filename)
    {
        $return = [];
        setlocale(LC_CTYPE, "fr_FR.UTF-8"); // escapeshellarg strip les lettres accentuees si on n'est pas dans une locale Utf8
        $commande = "/usr/bin/pdfinfo";
        if (is_executable($commande) && is_file($filename) && is_readable($filename)) {
            exec($commande . " " . escapeshellarg($filename), $output);
            if (array_key_exists("0", $output) && $output [0] != "") {
                for ($iline = 0; $iline < count($output); $iline++) {
                    if (preg_match('/^([a-zA-Z ]+):[[:space:]]*(.+)$/', $output [$iline], $match) && count($match) == 3) {
                        $key = trim($match [1]);
                        if (in_array($key, [
                            'Creator',
                            'Producer',
                            'Pages',
                            'Encrypted',
                            'PDF version'
                        ])) {
                            $return [] = [
                                $key => trim($match [2])
                            ];
                        }
                    }
                }
            }
        }
        return ($return);
    }

    private function convertPDFtoPDFa($nomCompletFichier, $fileId)
    {
        try {
            $this->logAction("Conversion en PDF/A de " . $nomCompletFichier . ' fileID : ' . $fileId);
            $execOutput = Ccsd_File::pdf2pdfa($nomCompletFichier);
            $this->logAction('Conversion output file: ' . $execOutput['outputFile'] . ' fileID : ' . $fileId);

            return $execOutput['outputFile'];

        } catch (Exception $e) {
            $this->logAction("Erreur Conversion en PDF/A de " . $nomCompletFichier . ' fileID : ' . $fileId);
            $this->logAction($e->getMessage());
            return false;
        }

    }

    /**
     * Retourne répertoire où stocker doc convertis pour l'archivage
     * @param int $docid
     * @return string
     */
    private static function getConvertedDocDir(int $docid): string
    {
        return self::CONVERTED_FILES_PATH . wordwrap(sprintf("%08d", $docid), 2, DIRECTORY_SEPARATOR, 1) . DIRECTORY_SEPARATOR;

    }

    function creationFichierSIP($miseAjour = false)
    {
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');

        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;
        $root = $xml->createElement('pac');

        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'http://www.cines.fr/pac/sip');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'http://www.cines.fr/pac/sip http://www.cines.fr/pac/sip.xsd');
        $xml->appendChild($root);

        $metasDepot = $xml->createElement('DocDC');

        // Ajout des titre dans les differentes langues Element titre
        foreach ($this->DEPOT->getTitle() as $lang => $titleByLang) {
            $title = $xml->createElement('title', $titleByLang);
            switch (strlen($lang)) {
                case 3 :
                    $codeLang = $lang;
                    break;
                case 2 :
                    $codeLang = Ccsd_Lang_Mapper::getIso2($lang, 'und');
                    break;
                default :
                    $codeLang = "und";
            }
            $title->setAttribute('language', $codeLang);
            $metasDepot->appendChild($title);
        }

        // Ajout des auteurs
        foreach ($this->DEPOT->getAuthors() as $author) {
            $creator = $xml->createElement('creator', $author->getLastname() . ", " . $author->getFirstname());
            $metasDepot->appendChild($creator);
        }

        // Libelle du domaine en français
        foreach ($this->DEPOT->getMeta('domain') as $domain) {
            $subject = $xml->createElement('subject', Ccsd_Tools_String::getHalDomainTranslated($domain, 'fr', '/'));
            $subject->setAttribute('language', 'fra');
            $metasDepot->appendChild($subject);
        }

        // Ajout des sujets des mots clés
        if (count($this->DEPOT->getKeywords())) {
            foreach ($this->DEPOT->getKeywords() as $lang => $keywordsByLang) {
                switch (strlen($lang)) {
                    case 3 :
                        $codeLang = $lang;
                        break;
                    case 2 :
                        $codeLang = Ccsd_Lang_Mapper::getIso2($lang, 'und');
                        break;
                    default :
                        $codeLang = "und";
                }
                if (is_array($keywordsByLang)) {
                    foreach ($keywordsByLang as $keyword) {
                        $subject = $xml->createElement('subject', $keyword);
                        $subject->setAttribute('language', $codeLang);
                        $metasDepot->appendChild($subject);
                    }
                } else {
                    $subject = $xml->createElement('subject', $keywordsByLang);
                    $subject->setAttribute('language', $codeLang);
                    $metasDepot->appendChild($subject);
                }
            }
        }

        // Ajout des résumés
        $finChaineTropLongue = " [...]";
        $longueurMax = (int)(4000 - mb_strlen($finChaineTropLongue));
        $presenceAbstract = false;
        foreach ($this->DEPOT->getAbstract() as $lang => $abstractByLang) {
            // cas des resumes longs : on coupe à 4000
            if (strlen($abstractByLang) > $longueurMax) {
                $abstractByLang = mb_substr($abstractByLang, 0, $longueurMax) . $finChaineTropLongue;
            }
            $presenceAbstract = true;
            $abstract = $xml->createElement('description', $abstractByLang);
            switch (strlen($lang)) {
                case 3 :
                    $codeLang = $lang;
                    break;
                case 2 :
                    $codeLang = Ccsd_Lang_Mapper::getIso2($lang, 'und');
                    break;
                default :
                    $codeLang = "und";
            }
            $abstract->setAttribute('language', $codeLang);
            $metasDepot->appendChild($abstract);
        }
        if (!$presenceAbstract) {
            $abstract = $xml->createElement('description', 'Sans objet');
            $abstract->setAttribute('language', "fra");
            $metasDepot->appendChild($abstract);
        }
        // Ajout provenance
        $publisher = $xml->createElement('publisher', 'hal; HAL; hal.archives-ouvertes.fr');
        $metasDepot->appendChild($publisher);

        // Ajout de la date
        // Besoin d'une date complete Y-m-d

        $date = $xml->createElement('date', str_pad($this->DEPOT->createProducedDate(), 10, "-01"));
        $metasDepot->appendChild($date);

        // Type de document
        $type = $xml->createElement('type', Zend_Registry::get('Zend_Translate')->translate('typdoc_' . $this->DEPOT->getTypDoc(), 'fr'));
        $type->setAttribute('language', 'fra');
        $metasDepot->appendChild($type);

        // Ajout des formats des fichiers du dépôt, le format xml est forcement présent avec le fichier de description
        $typeMimeXML = "text/xml";
        $format = $xml->createElement('format', $typeMimeXML);
        $format->setAttribute('language', 'eng');
        $metasDepot->appendChild($format);

        if (count($this->FICHIERS) > 0) {
            $formats = array_unique($this->FICHIERS ['type_mime']);
            foreach ($formats as $formatMime) {
                if (!strcasecmp($typeMimeXML, $formatMime)) {
                    // format xml dejà présent dans tous les cas
                    continue;
                }
                $format = $xml->createElement('format', $formatMime);
                $format->setAttribute('language', 'eng');
                $metasDepot->appendChild($format);
            }
        }

        // Ajout de la langue du document - dans le cas d'un depot de type image : und
        $langDoc = Ccsd_Lang_Mapper::getIso2($this->DEPOT->getMeta('language'), 'und');

        $language = $xml->createElement('language', $langDoc);
        $metasDepot->appendChild($language);
        // ajout des droits
        $rights = $xml->createElement('rights', 'Document sous copyright de ou des auteurs - consultation libre');
        $rights->setAttribute('language', 'fra');
        $metasDepot->appendChild($rights);

        $root->appendChild($metasDepot);

        $descDepot = $xml->createElement('DocMeta');

        // Ajout identifiant
        $identifiant = $xml->createElement('identifiantDocProducteur', $this->DOCID);
        $descDepot->appendChild($identifiant);


        // Ajout plan de classement
        // TODO à ajouter, en attente information du CINES
        //$planClassement = $xml->createElement('planClassement', $this->DOCID);
        //$planClassement->setAttribute('language', 'fra');
        //$descDepot->appendChild($planClassement);


        if ($miseAjour) {
            // relation dans le systeme du CCSD
            $relation = $xml->createElement('docRelation');
            $type = $xml->createElement('typeRelation', self::$_MAJ);
            $source = $xml->createElement('sourceRelation', self::$_PRODUCTEUR);
            $id = $xml->createElement('identifiantSourceRelation', $this->DOCID);
            $relation->appendChild($type);
            $relation->appendChild($source);
            $relation->appendChild($id);
            $descDepot->appendChild($relation);

            // relation dans le systeme du CINES
            $relation = $xml->createElement('docRelation');
            $type = $xml->createElement('typeRelation', self::$_MAJ);
            $source = $xml->createElement('sourceRelation', self::$_ARCHIVEUR);
            $id = $xml->createElement('identifiantSourceRelation', $this->IDPAC);
            $relation->appendChild($type);
            $relation->appendChild($source);
            $relation->appendChild($id);
            $descDepot->appendChild($relation);
        } else if ($this->DEPOT->getVersion() > 1) {
            // Il s'agit d'une version successive, on la relie à la première version archivée
            // Recherche du docid de la version 1
            $doc = new Hal_Document(0, $this->DEPOT->getId(), 1, true);
            $this->logAction("Essai d'archivage version successive");
            $this->logAction("Docid de la version 1 " . $doc->getDocid());
            $archive = new self($doc->getDocid());
            $idPacInitial = $archive->getIDPACInitial();
            $this->logAction("PACid de la version 1 " . $idPacInitial);
            if ($idPacInitial == 0) {
                // Lot dont la version 1 n'a pas été archivée
                $this->logAction("Essai d'archivage de la version " . $this->DEPOT->getVersion() . " du depot " . $this->DEPOT->getId() . " non archivée");
                $this->STATUT = static::ARCHIVE_REJETEE;
                $this->CODE = "VERSION " . $this->DEPOT->getVersion() . " DU DEPOT " . $this->DEPOT->getId() . " NON ARCHIVE";
                $this->ACTION = static::$_ACTIONS [6];
                $this->DATE_ACTION = date("Y-m-d H:i:s");
                $this->sauvegardeBase();
                return false;
            }
            // relation dans le systeme du CCSD
            $relation = $xml->createElement('docRelation');
            $type = $xml->createElement('typeRelation', self::$_NOUVELLE_VERSION);
            $source = $xml->createElement('sourceRelation', self::$_PRODUCTEUR);
            $id = $xml->createElement('identifiantSourceRelation', $doc->getDocid());
            $relation->appendChild($type);
            $relation->appendChild($source);
            $relation->appendChild($id);
            $descDepot->appendChild($relation);

            // relation dans le systeme du CINES
            $relation = $xml->createElement('docRelation');
            $type = $xml->createElement('typeRelation', self::$_NOUVELLE_VERSION);
            $source = $xml->createElement('sourceRelation', self::$_ARCHIVEUR);
            $id = $xml->createElement('identifiantSourceRelation', $idPacInitial);
            $relation->appendChild($type);
            $relation->appendChild($source);
            $relation->appendChild($id);
            $descDepot->appendChild($relation);
        }
        // Ajout service versant
        $serviceVersant = $xml->createElement('serviceVersant', 'Centre de Communication Scientifique Directe (CCSD)');
        $descDepot->appendChild($serviceVersant);

        $root->appendChild($descDepot);

        // Ajout des formats des fichiers du dépôt, le format xml est forcement présent avec le fichier de description
        // Declaration du fichier metier
        $descFile = $xml->createElement('FichMeta');

        // Rajout de l'element encodage
        $encodage = $xml->createElement('encodage', 'UTF-8');
        $descFile->appendChild($encodage);

        $formatFichier = $xml->createElement('formatFichier', $this->getCinesFacileFileTypes() [$typeMimeXML]);
        $descFile->appendChild($formatFichier);

        $nomFichier = $xml->createElement('nomFichier', 'DESC/metier_' . $this->DOCID . '.xml');
        $descFile->appendChild($nomFichier);


        $this->creationFichierMetier();
        $empreinteOri = $xml->createElement('empreinteOri', md5_file($this->getFichierMetier()));
        $empreinteOri->setAttribute('type', 'MD5');
        $descFile->appendChild($empreinteOri);

        $root->appendChild($descFile);

        if ((!$miseAjour) && (count($this->FICHIERS) > 0)) {
            for ($i = 0; $i < count($this->FICHIERS ['nom_fichier']); $i++) {
                $descFile = $xml->createElement('FichMeta');

                // Pour les fichier de type texte
                if (!strcasecmp($this->FICHIERS ['type_mime'] [$i], "text/xml")) {
                    // precision du type d'encodage
                    $encodage = $xml->createElement('encodage', 'UTF-8');
                    $descFile->appendChild($encodage);
                } elseif (substr($this->FICHIERS ['type_mime'] [$i], 0, 5) == 'video') {
                    // Pour les fichier de type vidéo
                    $this->logAction("Envoi d'un fichier de type Vidéo : " . $this->FICHIERS ['type_mime'] [$i]);
                    $encodage = $xml->createElement('encodage', 'AVC/AAC');
                    $descFile->appendChild($encodage);
                }


                $formatFichier = $xml->createElement('formatFichier', $this->getCinesFacileFileTypes() [$this->FICHIERS ['type_mime'] [$i]]);
                $descFile->appendChild($formatFichier);

                $nomFichier = $xml->createElement('nomFichier', $this->FICHIERS ['id_cible'] [$i]);
                $descFile->appendChild($nomFichier);

                $empreinteOri = $xml->createElement('empreinteOri', md5_file($this->FICHIERS ['nom_fichier'] [$i]));
                $empreinteOri->setAttribute('type', 'MD5');
                $descFile->appendChild($empreinteOri);

                $root->appendChild($descFile);
            }
        }
        // sauvegarde du fichier sip dans le repertoire cache
        file_put_contents($this->FICHIERSIP, $xml->saveXML());
        return true;
    }

    function getIDPACInitial()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES, [
            'IDPAC'
        ])->where('DOCID = ?', $this->DOCID)->where('ACTION = "ARCHIVAGE"')->order('DATE_ACTION ASC');
        $resultat = $db->fetchCol($sql);

        return (current($resultat));
    }

    /**
     * Creation du fichier des metadonnées métiers d'un dépôt
     * Similaire au fichier tei
     * je regarde dans le cache et s'il existe je le copie
     */
    function creationFichierMetier()
    {
        // appel au cache
        $nomFile = $this->DEPOT->get('tei', false);
        if (!is_file($nomFile)) {
            $this->DEPOT->createCache('tei');
        }
        $enteteXML = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rajoutRefSchema = '<TEI xmlns="http://www.tei-c.org/ns/1.0"' . "\n" . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.tei-c.org/ns/1.0 ' . AOFR_SCHEMA_URL . '">' . "\n";
        $tei = file_get_contents($nomFile);


        $teiArchive = $enteteXML . $rajoutRefSchema . substr($tei, strpos($tei, ">") + 1);
        file_put_contents($this->FICHIERMETIER, $teiArchive);
        return $this->FICHIERMETIER;
    }

    /*
     * Communication par mail avec le CINES pour les retours d'archivage
     */

    public function getFichierMetier()
    {
        return $this->FICHIERMETIER;
    }

    function valide($monFichier)
    {
        libxml_use_internal_errors(true);
        $dom = new DomDocument ();
        $dom->load($monFichier);

        if ($dom->schemaValidate(AOFR_SCHEMA_URL)) {
            return true;
        } else {
            $this->logAction("Document " . $monFichier . " non conforme au schema");
            $this->libxml_log_errors();
            return false;
        }
    }

    function libxml_log_errors()
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $this->libxml_log_error($error);
        }
        libxml_clear_errors();
    }

    function libxml_log_error($error)
    {
        $return = "";
        if ($error->level == LIBXML_ERR_FATAL) {
            $return .= "Fatal Error $error->code : ";
        }

        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in $error->file";
        }
        $return .= " on line $error->line\n";
        $this->logAction($return);
    }

    /**
     * envoie effectif des fichiers au CINES
     *
     * @param boolean $avecFichiers
     *         true : nouveau depot ou nouvelle version
     *         false : mise à jour des métadonnées
     *
     */
    function scpCINES($miseAjour = false)
    {
        $texte = "";
        $retour = true;


        if (($this->getConnectionArchive()->getSshResource() === false) || ($this->getConnectionArchive()->getSftpResource() === false)) {
            $texte .= "Probleme de connexion sur le serveur pac fin de la procedure";
            $this->logAction($texte);
            return false;
        }

        if ((!$miseAjour) && (count($this->FICHIERS ['nom_fichier']) == 0)) {
            $texte .= "\n\tFin procedure : pas une mise à jour et pas de fichier associé au lot  " . $this->DOCID;
            $this->logAction($texte);
            return false;
        }

        $repertoireDistantSIP = static::$_REPERTOIRE_BASE_DEPOT . static::$_PREFIXE_REPERTOIRE_DEPOT . $this->DOCID;
        if ($miseAjour) {
            // Je rajoute la date pour l'unicite du repertoire de depot
            $this->logAction("Mise a jour ");
            $repertoireDistantSIP .= "_" . date("Ymd");
        }

        ssh2_sftp_mkdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP);

        $this->logAction("Fichier a creer " . $repertoireDistantSIP . "/sip.xml");
        if ($this->envoiFichier($this->getFichierSIP(), $repertoireDistantSIP . "/sip.xml") === false) {
            ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP . "/sip.xml");
            ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP);
            $texte .= "\n\tSuppression de l'envoi du lot  " . $this->DOCID;
            $this->logAction($texte);
            return false;
        }

        $texte .= "\n\tFin creation du fichier sip.xml";

        $repertoireDistantDepot = $repertoireDistantSIP . "/" . static::$_REPERTOIRE_BASE_DEPOT_FICHIERS;

        ssh2_sftp_mkdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantDepot);


        $repertoireDistantMetier = $repertoireDistantDepot . "/" . static::$_SOUS_REPERTOIRE_FICHIERS_METIERS;

        ssh2_sftp_mkdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantMetier);

        $this->logAction("Fichier a creer " . $repertoireDistantMetier . "/metier_" . $this->DOCID . ".xml");
        if ($this->envoiFichier($this->getFichierMetier(), $repertoireDistantMetier . "/metier_" . $this->DOCID . ".xml") === false) {
            ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantMetier . "/metier_" . $this->DOCID . ".xml");
            ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantMetier);
            ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantDepot);
            ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP . "/sip.xml");
            ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP);
            $texte .= "\n\tSuppression de l'envoi du lot  " . $this->DOCID;
            $this->logAction($texte);
            return false;
        }

        $texte .= "\n\tFin creation fichier metier_" . $this->DOCID . ".xml";

        $indiceCourant = 0;
        if (!$miseAjour) {
            for ($i = 0; $i < count($this->FICHIERS ['nom_fichier']); $i++) {
                $this->logAction("Fichier a copier " . $this->FICHIERS ['nom_fichier'] [$i]);
                $this->logAction("Fichier a creer " . $repertoireDistantDepot . "/" . $this->FICHIERS ['id_cible'] [$i]);
                if ($this->envoiFichier($this->FICHIERS ['nom_fichier'] [$i], $repertoireDistantDepot . "/" . $this->FICHIERS ['id_cible'] [$i]) === false) {
                    $indiceCourant = $i;
                    $retour = false;
                    break;
                }

            }
            if ($retour === false) {
                // l'envoi se s'est pas passé correctement on supprime le repertoire
                for ($i = 0; $i < $indiceCourant; $i++) {
                    ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantDepot . "/" . $this->FICHIERS ['id_cible'] [$i]);
                }
                ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantMetier . "/metier_" . $this->DOCID . ".xml");
                ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantMetier);
                ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantDepot);
                ssh2_sftp_unlink($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP . "/sip.xml");
                ssh2_sftp_rmdir($this->getConnectionArchive()->getSftpResource(), $repertoireDistantSIP);
                $texte .= "\n\tProbleme transfert des fichiers de donnees  " . $this->DOCID;
                $texte .= "\n\tSuppression de l'envoi du lot  " . $this->DOCID;
                $this->STATUT = static::ARCHIVE_REJETEE;
                $this->ACTION = static::$_ACTIONS [9];
                $this->DATE_ACTION = date("Y-m-d H:i:s");
                $this->sauvegardeBase();
                $this->logAction($texte);
                return false;
            }
            $this->sauvegardeEnvoiFichier($this->FICHIERS ['id_cible']);
            $texte .= "\n\tFin copie des fichiers du lot";
        }
        $this->STATUT = static::ARCHIVE_ENVOYEE;
        $this->ACTION = static::$_ACTIONS [3];
        $this->DATE_ACTION = date("Y-m-d H:i:s");
        $this->sauvegardeBase();

        $texte .= "\n\tEnvoi du lot  " . $this->DOCID;
        $this->logAction($texte);

        return true;
    }

    /**
     * @return Ccsd_Archivage_Connection
     */
    public function getConnectionArchive(): Ccsd_Archivage_Connection
    {
        return $this->_connectionArchive;
    }

    /**
     * @param Ccsd_Archivage_Connection $connectionArchive
     */
    public function setConnectionArchive(Ccsd_Archivage_Connection $connectionArchive)
    {
        $this->_connectionArchive = $connectionArchive;
    }

    function envoiFichier($fichierLocal, $fichierDestination)
    {

        $bytesWritten = $this->sendChunkedFileViaSFTP($fichierLocal, $fichierDestination);

        if (filesize($fichierLocal) == $bytesWritten) {
            return true;
        }

        $this->logAction('bytes read from local file ' . $fichierLocal . ': ' . filesize($fichierLocal));
        $this->logAction('bytes written for remote file ' . $fichierDestination . ' ' . $bytesWritten);
        return false;

    }

    /**
     * Ecrit un fichier distant par paquets de $buffer_size octets
     * @param $source
     * @param $destination
     * @param int $buffer_size
     * @return int nb of bytes written*
     */
    public function sendChunkedFileViaSFTP($source, $destination, $buffer_size = 100000000)
    {
        $buffer_size = (int)$buffer_size;
        $nbBytesWritten = 0;
        $fin = fopen($source, "rb");
        $fout = fopen("ssh2.sftp://" . intval($this->getConnectionArchive()->getSftpResource()) . $destination, 'w');
        while (!feof($fin)) {
            $nbBytesWritten += fwrite($fout, fread($fin, $buffer_size));
        }
        fclose($fin);
        fclose($fout);
        return $nbBytesWritten;


    }

    public function getFichierSIP()
    {
        return $this->FICHIERSIP;
    }

    function sauvegardeEnvoiFichier($tableauFileIdsAvecExtension)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        foreach ($tableauFileIdsAvecExtension as $fileIdavecExtension) {
            $donnees = [
                'SEND' => 1
            ];
            $fileId = substr($fileIdavecExtension, 0, strrpos($fileIdavecExtension, "."));
            $this->logAction("Memoire envoi du fileID " . $fileId);
            $db->update(self::TABLE_FICHIERS, $donnees, 'FILEID = ' . $fileId);
        }
    }

    /**
     * Lecture des mails de retour des dialogues avec le CINES
     * Verification de l'expediteur
     * Identification de l'self correspondante
     * Affectation des informations dans la base
     * Alerte si necessaire
     */
    public function lireCompteMail($env = APPLICATION_ENV)
    {
        $mail_info = [
            'host' => ARCHIVAGE_MAIL_SERVER,
            'user' => ARCHIVAGE_MAIL_USERNAME,
            'password' => ARCHIVAGE_MAIL_PWD,
            'ssl' => ARCHIVAGE_MAIL_PROTOCOL
        ];

        Zend_Db_Table_Abstract::getDefaultAdapter();
        // lecture mail et mise à jour correspondante
        try {
            $maConnection = new Zend_Mail_Storage_Imap($mail_info);
            if (!$nbMessageTotal = $maConnection->countMessages()) {

                $texte2 = "Pas de message";
                $this->logAction($texte2);
            }
            // Il faut stocker le nombre de message au début et ne pas incrémenter le numéro de message
            // car la boite évolue à chaque suppression
            $idMessageCourant = 1;
            for ($i = 1; $i <= $nbMessageTotal; $i++) {
                $message = $maConnection->getMessage($idMessageCourant);

                $this->DATE_ACTION = date("Y-m-d H:i:s", strtotime($message->getHeader('date', 'string')));
                $j = 1;
                $erreur = false;
                if ($message->isMultipart()) {
                    while (!$erreur) {
                        try {
                            $part = $message->getpart($j);
                            $contentType = strtok($part->contentType, ';');
                            if (!strcmp($contentType, "text/xml")) {
                                $xml = simplexml_load_string(quoted_printable_decode($part->getContent()));
                            }
                            $j++;
                        } catch (Zend_Mail_Exception $exception) {
                            $this->logAction($exception->getMessage());
                            $erreur = true;
                        }
                    }
                } else {
                    // Tout est dans le corps du message
                    if (($xml = simplexml_load_string(quoted_printable_decode($message->getContent()))) == false) {
                        $this->logAction("Probleme xml : \n" . quoted_printable_decode($message->getContent()));
                    }
                }


                if ($xml) {
                    switch ($xml->id) {
                        case "ACCUSE_RECEPTION_DE_VERSEMENT" :
                            if (strpos($xml->id_versement, "_") == strrpos($xml->id_versement, "_")) {
                                $docid = substr($xml->id_versement, mb_strlen(static::$_PREFIXE_REPERTOIRE_DEPOT));
                            } else {
                                $docid = substr($xml->id_versement, mb_strlen(static::$_PREFIXE_REPERTOIRE_DEPOT), -strrpos($xml->id_versement, "_"));
                            }
                            // recuperation des informations précédentes dans la table
                            $this->DOCID = $docid;
                            $this->rechercheInfoBase();
                            $this->CODE = "";
                            $texte = "Accuse reception de versement";
                            $this->STATUT = static::ARCHIVE_RECUE;
                            $this->ACTION = static::$_ACTIONS [4];
                            break;
                        case "REJET_VERSEMENT" :
                            if (strpos($xml->id_versement, "_") == strrpos($xml->id_versement, "_")) {
                                $docid = substr($xml->id_versement, mb_strlen(static::$_PREFIXE_REPERTOIRE_DEPOT));
                            } else {
                                $docid = substr($xml->id_versement, mb_strlen(static::$_PREFIXE_REPERTOIRE_DEPOT), -strrpos($xml->id_versement, "_"));
                            }
                            $this->DOCID = $docid;
                            $this->rechercheInfoBase();
                            $this->IDPAC = 0;
                            $this->CODE = $xml->codeErreur . "\n" . $xml->erreurValidation;
                            $texte = "Rejet versement, cause : " . $xml->codeErreur . "\n Commentaire : " . $xml->commentaire;
                            if (strcmp($xml->codeErreur, "E0104")) {
                                $this->STATUT = static::ARCHIVE_REJETEE;
                                $this->ACTION = static::$_ACTIONS [6];
                            } else {
                                // archive déjà présente pb de tests...
                                $this->STATUT = static::ARCHIVE_ACCEPTEE;
                                $this->ACTION = static::$_ACTIONS [5];
                            }
                            // envoi d'un mail aux administrateur pour signifier le rejet
                            $destinataires = implode(",", ARCHIVAGE_MAIL_ADMINISTRATOR);
                            $sujet = "[ArchivHAL] AVIS DE REJET docid " . $docid;
                            $this->DEPOT = Hal_Document::find($docid);
                            $message = "Cet avis concerne le depot : \n" . $this->DEPOT->getID() . " version " . $this->DEPOT->getVersion() . "\n\n" . "Depose par " . implode(" - ", $this->DEPOT->getContributor()) . " le " . $this->DEPOT->getSubmittedDate() . "\n" . "L'erreur est : code " . $xml->codeErreur . " - " . static::$_ERREURS_CINES [(string)$xml->codeErreur] . "\n -- \n" . $xml->commentaire . "\n -- \n" . $xml->erreurValidation . "\n\n" . "\n -- \n" . "\t\tArchivHAL";
                            $entetes = "From: ArchivHAL <hal@ccsd.cnrs.fr>\n" . "Content-type: text/plain; charset=utf-8'\n";
                            mail($destinataires, $sujet, $message, $entetes);
                            break;
                        case "CERTIFICAT_ARCHIVAGE" :
                            $texte = "Fin archivage DOCID " . $xml->identifiantDocProducteur . " - IDPAC " . $xml->identifiantDocPac;
                            $this->DOCID = (int)$xml->identifiantDocProducteur;
                            $this->rechercheInfoBase();
                            // Il faut savoir si il s'agit d'une mise a jour
                            $this->STATUT = static::ARCHIVE_ACCEPTEE;
                            $this->CODE = "";
                            $this->ACTION = static::$_ACTIONS [5];
                            $this->IDPAC = $xml->identifiantDocPac;
                            $dateArchivageFormatee = date("Y-m-d H:i:s", strtotime((string)$xml->dateArchivage));
                            $this->DATE_ACTION = $dateArchivageFormatee;
                            $this->priseEnCompteDepotARCHIVE($dateArchivageFormatee);
                            break;
                    } // fin switch
                    $this->logAction($texte);
                    $this->sauvegardeBase();
                } // fin element xml bien forme
                $maConnection->removeMessage($idMessageCourant);
            } // fin boucle des messages
            $maConnection->close();
        } // fin try
        catch (Exception $e) {
            $this->logAction("Error");
            $this->logAction($e->getMessage());
        }
    }

    /**
     * Mise à jour de la table listant les fichiers déjà envoyés à l'archivage
     * 1 ligne par fichier, enregistrement de la taille en Ko
     * Une réinitialisation de l'objet courant est necessaire dans un contexte
     * de lecture de mail
     */
    function priseEnCompteDepotArchive($maDate)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_FICHIERS, "FILEID")->where('SEND = 1')->where('DOCID = ?', $this->DOCID);
        $filesids = $db->fetchAll($sql);
        $this->sauvegardeArchivageFichier($filesids, $maDate);
    }

    function sauvegardeArchivageFichier($tableauFileIds, $dateArchivage)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        foreach ($tableauFileIds as $fileId) {
            $donnees = [
                'ARCHIVED' => $dateArchivage
            ];
            $db->update(static::TABLE_FICHIERS, $donnees, 'FILEID = ' . $fileId ['FILEID']);
        }
    }

    function historique()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DONNEES)->where('DOCID = ?', $this->DOCID)->order('DATE_ACTION DESC')->order('ID DESC');

        $actions = $db->fetchAll($sql);
        return ($actions);
    }

    /**
     * @return string
     */
    public function getPlanDeClassement(): string
    {
        return $this->_planDeClassement;
    }

    /**
     * @param string $planDeClassement
     */
    public function setPlanDeClassement(string $planDeClassement)
    {

        $planDeClassement = 'HAL_';
        $typeDocCamelCase = Zend_Registry::get('Zend_Translate')->translate('typdoc_' . $this->DEPOT->getTypDoc(), 'fr');
        $typeDocCamelCase = ucwords($typeDocCamelCase);
        $typeDocCamelCase = str_replace(' ', '', $typeDocCamelCase);
        $this->_planDeClassement = $planDeClassement . $typeDocCamelCase;
    }

    /**
     * @return array
     */
    public static function getCinesFacileFileTypes(): array
    {
        return self::$_cinesFacileFileTypes;
    }


}
