<?php

/**
 * Navbar : Liens vers les différentes applications + lien connexion et langues
 */
class Ccsd_View_Helper_Navbar extends Zend_View_Helper_Abstract
{

    /**
     * Liste des applications
     */
    const APP_HAL = 'hal';

    const APP_EPI = 'epi';

    const APP_SC = 'sc';

    /**
     * Afficher le choix des langues de l'interface
     *
     * @var bool
     */
    protected $_displayLang = false;

    /**
     * Tableau des langues de l'interface
     *
     * @var array
     */
    protected $_languages = array();

    /**
     * Langue courante de l'interface
     *
     * @var string
     */
    protected $_lang = '';

    /**
     * Afficher le bouton Connexion
     *
     * @var bool
     */
    protected $_displayLogin = false;

    /**
     * Indique si l'utilisateur est connecté
     *
     * @var bool
     */
    protected $_isLogged = false;

    /**
     * Fichier de rendu du bloc "utilisateur connecté"
     *
     * @var string
     */
    protected $_userRenderScript = 'common/user.phtml';

    /**
     * Application active
     *
     * @var string
     */
    protected $_active = self::APP_HAL;

    /**
     * Préfixe des URLs des liens de la navbar
     *
     * @var string
     */
    protected $_prefixUrl = '/';

    protected $_apiEnv = 'api';

    protected $_hostEnv = '';

    protected $_labelEnv = false;

    protected $_labelEnvClass = '';

    /**
     * @param bool $displayLang
     * @param bool $langOptions
     * @param bool $displayLogin
     * @param array $loginOptions
     * @param string $prefixUrl
     * @param string $application
     */
    public function navbar ($displayLang, $langOptions, $displayLogin, $loginOptions, $prefixUrl = '/', $application = self::APP_HAL)
    {
        if ($displayLang) {
            $this->_displayLang = true;
            foreach (array(
                    'languages',
                    'lang'
            ) as $option) {
                if (isset($langOptions[$option])) {
                    $this->{'_' . $option} = $langOptions[$option];
                }
            }
        }
        if ($displayLogin) {
            $this->_displayLogin = true;
            foreach (array(
                    'isLogged',
                    'userRenderScript'
            ) as $option) {
                if (isset($loginOptions[$option])) {
                    $this->{'_' . $option} = $loginOptions[$option];
                }
            }
        }
        $this->_prefixUrl = $prefixUrl;
        $this->_active = $application;

        // Initialisation de la navbar en fonction de l'environnement
        if (defined('APPLICATION_ENV') && APPLICATION_ENV != 'production') {
            switch (APPLICATION_ENV) {

                case 'development':
                    $this->_apiEnv = 'api-dev';
                    $this->_labelEnvClass = 'label-danger';
                    $this->_hostEnv = '-dev';
                    break;

                case 'testing':
                    $this->_apiEnv = 'api-test';
                    $this->_labelEnvClass = 'label-warning';
                    $this->_hostEnv = '-test';
                    break;

                case 'preprod':
                    $this->_apiEnv = 'api-preprod';
                    $this->_labelEnvClass = 'label-primary';
                    $this->_hostEnv = '-preprod';
                    break;

                case 'demo':
                    $this->_labelEnvClass = 'label-info';
                    break;

                default:
                    $this->_labelEnv = APPLICATION_ENV;
                    break;

            }
        }

        return $this->render();
    }

    public function render ()
    {
        /** @var Hal_View $view */
        $view = $this->view;
        $request = Zend_Controller_Front::getInstance()->getRequest();
        ?>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <?php //Logo CCSD + bouton si taille ecran mobile ?>
            <div class="navbar-header ">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target="#nav-services">
                    <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span
                            class="icon-bar"></span> <span class="icon-bar"></span>
                </button>
                <div class="logo-ccsd">
                    <a class="brand" href="https://www.ccsd.cnrs.fr/"
                       title="Centre pour la Communication Scientifique Directe"><img src="/img/ccsd.png"
                                                                                      border="0" alt="CCSD" /></a>
                    <?php if ($this->_labelEnv != false) { ?>
                        <span style="margin-left: 8px;"
                              class="label <?php echo $this->_labelEnvClass; ?>"><?php echo $view->translate($this->_labelEnv);?></span>
                    <?php } ?>
                </div>
            </div>
            <?php //Liens services ?>
            <div class="collapse navbar-collapse" id="nav-services">
                <ul class="nav navbar-nav">
                    <li class="dropdown <?php echo ($this->_active == self::APP_HAL ? 'active' : '')?>"><a
                                href="#" class="dropdown-toggle" data-toggle="dropdown">HAL <b class="caret" style="border-top-color:#ee5a35;border-bottom-color:#ee5a35;"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="https://hal<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr">HAL</a></li>
                            <li><a href="https://halshs<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr">HALSHS</a></li>
                            <li><a href="https://tel<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr">TEL</a></li>
                            <li><a href="https://medihal<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr">MédiHAL</a></li>
                            <li><a href="https://hal<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr/browse/portal"><?php echo $view->translate('Liste des portails');?></a></li>
                            <li class="divider"></li>
                            <li><a href="https://aurehal<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr" target="_blank">AURéHAL</a></li>
                            <li><a href="http://<?php echo $this->_apiEnv; ?>.archives-ouvertes.fr/docs">API</a></li>
                            <li><a href="https://data.archives-ouvertes.fr/" title="HAL SPARQL endpoint">Data</a></li>
                            <li><a href="https://hal<?php echo $this->_hostEnv; ?>.archives-ouvertes.fr/section/documentation" rel="help"><?php echo $view->translate('Documentation');?></a></li>
                        </ul></li>

                    <li class="dropdown <?php echo ($this->_active == self::APP_EPI ? 'active' : '') ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Episciences.org <b class="caret" style="border-top-color:#ee5a35;border-bottom-color:#ee5a35;"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="https://www.episciences.org">Episciences.org</a></li>
                            <li><a href="https://www.episciences.org/page/journals"><?php echo $view->translate('Revues');?></a></li>
                            <li class="divider"></li>
                            <li><a href="https://doc.episciences.org/" rel="help"><?php echo $this->view->translate('Documentation');?></a></li>
                        </ul>
                    </li>
                    <li class="<?php echo ($this->_active == self::APP_SC ? 'active' : '') ?>">
                        <a href="https://www.sciencesconf.org">Sciencesconf.org</a></li>
                    <li><a href="https://support.ccsd.cnrs.fr">Support</a></li>
                </ul>
                <?php if ($this->_displayLogin) {?>
                    <div class="nav navbar-nav navbar-right">
                        <?php
                        if ($this->_isLogged) {
                            try {
                                echo $this->view->render($this->_userRenderScript);
                    } catch (Exception $e) {}
                        } else {
                            ?>
                            <form class="form-inline pull-right"
                                  style="margin-top: 8px; margin-right: 8px;"
                                  action="<?php echo $this->_prefixUrl; ?>user/login" id="form-login" method="post">
                                <input type="hidden" name="forward-controller"
                                       value="<?php echo $request->getControllerName();?>" />
                                <input type="hidden" name="forward-action"
                                       value="<?php echo $request->getActionName();?>" />
                                <?php
                                $forwardParams = $request->getParams();
                                unset($forwardParams['controller'], $forwardParams['action'], $forwardParams['module'], $forwardParams['submit'], $forwardParams['submit_advanced']);

                                foreach ($forwardParams as $name => $value) {
                                    if (is_array($value)) {
                                        if ($name != 'qa') {
                                            echo '<input type="hidden" name="' . htmlspecialchars($name) . '[]" value="' . htmlspecialchars(implode(' OR ', $value)) . '" />';
                                        } else {
                                            // cas particulier pour la recherche avancée
                                            $url = urldecode($_SERVER['REDIRECT_QUERY_STRING']);
                                        }
                                    } else if (is_string($value)) {
                                        echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
                                    }
                                }

                                if (isset($url)) {
                                    echo '<input type="hidden" name="forward-uri" value="' . htmlspecialchars($url) . '" />';
                                }  ?>
                                <div class="btn-group">
                                    <button class="btn btn-small btn-primary" type="button"
                                    onclick="$('#form-login').submit();" accesskey="l">
                                        <i class="glyphicon glyphicon-user glyphicon-white"></i>&nbsp;<?php echo $view->translate('Connexion');?>
                                    </button>
                                    <button class="btn btn-small btn-primary dropdown-toggle" data-toggle="dropdown"
                                            type="button">
                                        <span class="caret" style="border-top-color: #fff; border-bottom-color: #fff;"></span>
                                    </button>

                                    <ul class="dropdown-menu pull-right">
                                        <?php
                                        if ($this->_active == self::APP_HAL) {
                                            /** @var Zend_Controller_Request_Http $req */

                                            $uri = $request->getRequestUri();
                                            if (Zend_Registry::get('APPLICATION_NAME') == 'HAL') {
                                                $site = Hal_Site::getCurrent();
                                                $url = $site->getUrl();
                                            } else if (Zend_Registry::get('APPLICATION_NAME') == 'AUREHAL') {
                                                $url = AUREHAL_URL;
                                            } else if (Zend_Registry::get('APPLICATION_NAME') == 'CV') {
                                                $url = CV_URL;
                                            } else {
                                                // TODO: a reorganiser... lorsqu'on aura un objet pour chaque application...
                                                $url='';
                                            }
                                            $urlredirect = "$url$uri";
                                            //$urlredirect = "https://halv3-local.ccsd.cnrs.fr";
                                        }
                                        if (USE_IDP !== "1") { ?>
                                            <li><a href="#"
                                           onclick="$('#form-login').submit();"><?php echo $view->translate('Connexion'); ?></a>
                                            </li>
                                            <li><a id="getorcid"
                                                   href="https://orcid.org/oauth/authorize?client_id=APP-O6Y5HZD2SFM7ON6Z&response_type=code&scope=/authenticate&redirect_uri=<?php echo HAL_URL; ?>/user/coext?url=<?php echo $urlredirect; ?>"><?php echo $this->view->translate('Connexion avec ORCID'); ?></a>
                                            </li>
                                            <?php
                                        }
                                        else {?>
                                            <li><a id="cas2" href="<?php echo $this->_prefixUrl; ?>user/login?authType=CAS&url=<?php echo $urlredirect; ?>"><?php echo $view->translate('Connexion'); ?></a></li>
                                            <li><a id="getorcid2" href="<?php echo $this->_prefixUrl; ?>user/login?authType=ORCID&url=<?php echo $urlredirect; ?>"><?php echo $view->translate('Connexion avec ORCID');?></a></li>
                                            <li><a id="coidp" href="<?php echo HAL_URL; ?>/user/login?authType=IDP&url=<?php echo $urlredirect;?>"><?php echo $view->translate('se connecter avec Fédération') ?></a></li>
                                        <?php }?>
                                        <li class="divider"></li>
                                        <li><a href="<?php echo $this->_prefixUrl; ?>user/create"><?php echo $view->translate('Créer un compte');?></a></li>
                                        <li class="divider"></li>
                                        <li><a href="<?php echo $this->_prefixUrl; ?>user/lostpassword"><?php echo $view->translate('Mot de passe oublié ?');?></a></li>
                                        <li><a href="<?php echo $this->_prefixUrl; ?>user/lostlogin"><?php echo $view->translate('Login oublié ?');?></a></li>
                                    </ul>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                <?php } ?>
                <form action="" method="post" id="formLang">
                    <input type="hidden" name="lang" id="lang" value="" />
                    <ul class="nav navbar-nav navbar-right navbar-lang">
                        <?php
                        if (count($this->_languages) > 1) {
                            foreach ($this->_languages as $i => $l) {
                                $class = ' ' . (($l == $this->_lang) ? 'active' : '');
                                ?>
                                <li class="<?php echo $class;?>"><a
                                            href="javascript:void(0);" title="<?php echo $view->translate($l); ?>"
                                    onclick="$('#lang').val('<?php echo $l ?>');$('#formLang').submit();"><span
                                                class="badge small"><?php echo $l ?></span></a></li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </form>
            </div>
        </div>
        <?php
    }
}
