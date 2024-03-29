<?php

class Ccsd_Form_Element_Thesaurus extends Zend_Form_Element_Xhtml implements Ccsd_Form_Interface_Javascript, Ccsd_Form_Interface_Css
{

	use Ccsd_Form_Trait_ImplementFunctionJS;
    public $pathDir = __DIR__ ;
    public $relPublicDirPath = "../../../public"; 
	
    protected $_delete                 = true;
    protected $_multilevel             = true;
    protected $_data;
    protected $_typeahead              = true;
    protected $_typeahead_label        = "Rechercher par nom :";
    protected $_typeahead_description  = "";
    protected $_typeahead_height       = 260;
    protected $_typeahead_value        = "";
    protected $_showing_icon           = true;
    protected $_showing_caret          = true;
    protected $_icon_parent_close      = "glyphicon glyphicon-folder-close";
    protected $_icon_parent_open       = "glyphicon glyphicon-folder-open";
    protected $_icon_parent_hover      = true;
    protected $_icon_child             = "glyphicon glyphicon-file";
    protected $_icon_move              = "glyphicon-move";
    protected $_icon_delete            = "glyphicon glyphicon-trash";
    protected $_icon_info              = "glyphicon glyphicon-info-sign";
    protected $_icon_handle            = ".glyphicon-info-sign";
    protected $_list_title             = "Liste des domaines :";
    protected $_msg_no_result          = "Aucun résultat";
    protected $_list_values            = "Mes choix :";
    protected $_option_collapse        = "<i class='glyphicon glyphicon-chevron-up'></i>";
    protected $_option_expand          = "<i class='glyphicon glyphicon-chevron-down'></i>";
    protected $_option_collapse_msg    = "Réduire la liste des domaines";
    protected $_option_expand_msg      = "Afficher la liste des domaines";
    protected $_is_modifying           = false;
    protected $_sortable               = true;
    protected $_selectable             = true;
    protected $_clickable              = false;
    protected $_css                    = array ();
    protected $_reqitems               = array ();
    protected $_event_on_click         = "";
    protected $_status_tree            = "";
    protected $_collapsable            = true;
    protected $_use_display            = false;
    protected $_prefix_translation     = "";
    protected $_locale                 = '';
    protected $_filtering			   = true;
    protected $_interdisciplinarite    = "";
    protected $_prefix_inter           = "";
    protected $_option_collapse_inter_msg    = "Réduire  : Interdisciplinarité HAL";
    protected $_option_expand_inter_msg      = "Afficher : Interdisciplinarité HAL";
    protected $_parentNode = true;
    protected $_valueTip = true;

    /**
     * @todo translator
     */
    /**
     * @return bool : false if something wrong
     */
    public function init ()
    {
        try {
            $this->_create();
        } catch (Exception $e) {
            return false;
        }

        if (empty($this->_decorators) && !$this->loadDefaultDecoratorsIsDisabled()) {
            $this->loadDefaultDecorators();
        }
        return true;
    }

    public function getParentNode ()
    {
    	return $this->_parentNode;
    }

    public function setParentNode ($b = true)
    {
    	$this->_parentNode = $b;
    	return $this;
    }

    public function getValueTip ()
    {
    	return $this->_valueTip;
    }

    public function setValueTip ($b = true)
    {
    	$this->_valueTip = $b;
    	return $this;
    }

    public function getFiltering()
    {
    	return $this->_filtering;
    }

    public function setFiltering ($b = true)
    {
    	$this->_filtering = $b;
    	return $this;
    }

    //Obsolete
    /** @deprecated  */
    public function getPrefixPath ()
    {
    	error_log('getPrefixPath ne devrait plus etre appele');
		$prefixPath = (new ReflectionClass(get_class($this)))->getFileName();

    	while (dirname ($prefixPath) && !is_dir($prefixPath . '/public')) {
            $prefixPath = dirname($prefixPath);
        }

    	return $prefixPath;
    }

    /**
     * Load default decorators
     *
     * @return Zend_Form_Element
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Errors')
            ->addDecorator('Description', array ('tag' => 'span', 'class' => 'help-block'))
            ->addDecorator('Thesaurus')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class'  => "col-md-9"))
            ->addDecorator('Label', array('tag' => 'label', 'class' => "col-md-3 control-label"));
        }
        return $this;
    }

    public function getDelete ()
    {
        return $this->_delete;
    }

    public function setDelete ( $is = true )
    {
        $this->_delete = $is;
        return $this;
    }

    public function getTypeahead ()
    {
        return $this->_typeahead;
    }

    public function setTypeahead ( $is = true )
    {
        $this->_typeahead = $is;
        return $this;
    }

    public function getPrefix_translation ( $inter = false )
    {
        if ($inter) {
            return $this->_prefix_translation . $this->getPrefix_inter();
        } else {
            return $this->_prefix_translation;
        }
    }

    public function setPrefix_translation ( $str = "" )
    {
        $this->_prefix_translation = $str;
        return $this;
    }

    public function getMsg_no_result ()
    {
        return $this->_msg_no_result;
    }

    public function setMsg_no_result ( $str = "" )
    {
        $this->_msg_no_result = $str;
        return $this;
    }

    public function getTypeahead_value ()
    {
        return $this->_typeahead_value;
    }

    public function setTypeahead_value ( $str = "" )
    {
        $this->_typeahead_value = $str;
        return $this;
    }

    public function getTypeahead_label ()
    {
        return $this->_typeahead_label;
    }

    public function setTypeahead_label ( $str = "")
    {
        $this->_typeahead_label = $str;
        return $this;
    }

    public function getTypeahead_description ()
    {
        return $this->_typeahead_description;
    }

    public function setTypeahead_description ( $str = "")
    {
        $this->_typeahead_description = $str;
        return $this;
    }

    public function getTypeahead_height ()
    {
        return $this->_typeahead_height;
    }

    public function setTypeahead_height ( $str = "")
    {
        $this->_typeahead_height = $str;
        return $this;
    }

    public function getList_title ()
    {
        return $this->_list_title;
    }

    public function setList_title ( $str = "")
    {
        $this->_list_title = $str;
        return $this;
    }

    public function getIs_modifying ()
    {
        return $this->_is_modifying;
    }

    public function setIs_modifying ( $is = false )
    {
        $this->_is_modifying = $is;

        $this->_typeahead  = false;
        $this->_sortable   = false;
        $this->_selectable = false;

        return $this;
    }

    public function getOption_collapse ()
    {
        return $this->_option_collapse;
    }

    public function setOption_collapse ( $str = "")
    {
        $this->_option_collapse = addslashes($str);
        return $this;
    }

    public function getOption_collapse_msg ()
    {
        return $this->_option_collapse_msg;
    }

    public function setOption_collapse_msg ( $str = "")
    {
        $this->_option_collapse_msg = $str;
        return $this;
    }

    public function getOption_expand_msg ()
    {
        return $this->_option_expand_msg;
    }

    public function setOption_expand_msg ( $str = "")
    {
        $this->_option_expand_msg = $str;
        return $this;
    }

    public function getOption_collapse_inter_msg ()
    {
    	return $this->_option_collapse_inter_msg;
    }

    public function setOption_collapse_inter_msg ( $str = "")
    {
    	$this->_option_collapse_inter_msg = $str;
    	return $this;
    }

    public function getOption_expand_inter_msg ()
    {
    	return $this->_option_expand_inter_msg;
    }

    public function setOption_expand_inter_msg ( $str = "")
    {
    	$this->_option_expand_inter_msg = $str;
    	return $this;
    }

    public function getCollapsable ()
    {
        return $this->_collapsable;
    }

    public function setCollapsable ( $is = true )
    {
        $this->_collapsable = $is;
        return $this;
    }

    public function getOption_expand ()
    {
        return $this->_option_expand;
    }

    public function setOption_expand ( $str = "")
    {
        $this->_option_expand = addslashes($str);
        return $this;
    }

    // S'il y a une locale, on la rend
    // Sinon on cherche la locale globale
    // Sinon on choisit le français
    public function getLocale ()
    {
        return empty($this->_locale) ? empty(Zend_Registry::get('Zend_Locale')) ? 'fr' : Zend_Registry::get('Zend_Locale') : $this->_locale;
    }

    public function setLocale ( $locale = "")
    {
    	$this->_locale = $locale;
    	return $this;
    }

    public function getList_values ()
    {
        return $this->_list_values;
    }

    public function setList_values ( $str = "")
    {
        $this->_list_values = $str;
        return $this;
    }

    public function getReqitems ()
    {
        return $this->_reqitems;
    }

    public function setReqitems ( $items = array () )
    {
        $this->_reqitems = $items;
        return $this;
    }

    public function getSortable ()
    {
        return $this->_sortable && $this->_selectable;
    }

    public function setSortable ( $is = true )
    {
        $this->_sortable = $is && $this->_selectable;
        return $this;
    }

    public function getClickable ()
    {
    	return $this->_clickable && $this->_selectable == FALSE;
    }

    public function setClickable ( $is = true )
    {
    	$this->_clickable = $is;
    	return $this;
    }

    public function getSelectable ()
    {
        return $this->_selectable;
    }

    public function setSelectable ( $is = true )
    {
        $this->_selectable = $is;
        return $this;
    }

    public function getMultilevel ()
    {
        return $this->_multilevel;
    }

    public function setMultilevel ( $is = true )
    {
        $this->_multilevel = $is;
        return $this;
    }

    public function getData ()
    {
        return $this->_data;
    }

    public function setData ( $str = "" )
    {
        $this->_data = $this->get ($str);
        return $this;
    }

    public function getUse_display ()
    {
        try {
            return Zend_Json::decode($this->_use_display);
        } catch(Zend_Json_Exception $e) {
            return false;
        }

    }

    public function setUse_display ( $str = "" )
    {
        $this->_use_display = $this->get ($str);
        return $this;
    }

    /**
     * @param array|string $str
     *     Si array:   array de (class=> xxx, method=> xxx)
     *                 autre array retourne tel quel
     *     si chaine:
     *                 On accepte sur un nom de fichier Json -> on retourne le contenu decode.
     *                 On accepte une Url: on retourne le contenu decode
     *                      si le contenu ne peut etre decode, on retourne FALSE
     *                 Autre chaine: on retourne la chaine
     *     si autre: retourne false
     * @return bool|array|string
     * @throws Zend_Form_Exception
     * @throws Zend_Json_Exception
     * Par meta.ini qui specifie class/method
     * @uses Hal_Settings::getDomainsInter()
     * @uses Hal_Settings::getTypdocsSelect()
     * @uses Hal_Settings::getLicencesTradCodes()
     * @uses Ccsd_Locale::getLanguage()
     * @uses Hal_Referentiels_Metadata::getValues()
     */
    private function get ($str = "")
    {
    	if (is_array ($str)) {
    		if (array_key_exists('class', $str) && array_key_exists('method', $str)) {
    			if (!class_exists($str['class'])) {
    				throw new Zend_Form_Exception(sprintf('Class not found: %s', $str['class']));
    			}

    			if (isset ($str['method']) && $str['method']) {
    				try {
    					$reflectionMethod = new ReflectionMethod($str['class'], $str['method']);
    					$pass = array ();
    					if (array_key_exists('arg', $str) && $str['args'])
	    					foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
	    						if (!array_key_exists ($reflectionParameter->name, $str['args'])) {
	    							throw new Zend_Form_Exception(sprintf('Paramètre requis: %s', $reflectionParameter->name));
	    						} else {
	    							$pass[] = $str['args'][$reflectionParameter->name];
	    						}
	    					}

    					if (empty ($pass)) {
    						return file_get_contents ($reflectionMethod->invoke(null));
    					} else {
    						return file_get_contents ($reflectionMethod->invokeArgs($reflectionMethod, $pass));
    					}

    				} catch (Exception $e) {
    					throw new Zend_Form_Exception(sprintf('La méthode ne peut pas être appelée: %s', $str['method']));
    				}
    			}
    		} else {
    			return $str;
    		}
    	} else if (is_file ($str)) {
    		return Zend_Json::decode (file_get_contents ($str));
    	} else try {
    			return Zend_Json::decode($str);
    		} catch (Exception $e) {
    			if (parse_url ($str,  PHP_URL_HOST) !== FALSE) {  // Il faut une url complete, on test cela avec la presence du Host
    				$curl = curl_init();
    				curl_setopt($curl, CURLOPT_URL, $str);
    				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    				curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    				curl_setopt($curl, CURLOPT_TIMEOUT, 15); //Secondes
    				curl_setopt($curl, CURLOPT_POST, false);
    				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    				$a = curl_exec($curl);

    				curl_close($curl);

                    try {
                        return Zend_Json::decode($a);
                    } catch (Exception $e) {
                        return false;
                    }

    			} else {
    				return $str;
    			}
    		}
        return false;
    }

    public function getElementCode ($code)
    {
        return $this->_data[$code];
    }

    public function getShowing_icon ()
    {
        return $this->_showing_icon;
    }

    public function setShowing_icon ( $is = true )
    {
        $this->_showing_icon = $is;
        return $this;
    }

    public function getShowing_caret ()
    {
        return $this->_showing_caret;
    }

    public function setShowing_caret ( $is = true )
    {
        $this->_showing_caret = $is;
        return $this;
    }

    public function getIcon_parent_close ()
    {
        return $this->_icon_parent_close;
    }

    public function setIcon_parent_close ( $str = "glyphicon glyphicon-folder-close" )
    {
        $this->_icon_parent_close = $str;
        $this->_icon_parent_hover = false;
        return $this;
    }

    public function getIcon_parent_open ()
    {
        return $this->_icon_parent_open;
    }

    public function setIcon_parent_open ( $str = "glyphicon glyphicon-folder-open" )
    {
        $this->_icon_parent_open = $str;
        $this->_icon_parent_hover = false;
        return $this;
    }

    public function getIcon_parent_hover ()
    {
        return $this->_icon_parent_hover;
    }

    public function setIcon_parent_hover ( $is = true )
    {
        $this->_icon_parent_hover = $is;
        return $this;
    }

    public function getIcon_child ()
    {
        return $this->_icon_child;
    }

    public function setIcon_child ( $str = "glyphicon glyphicon-file" )
    {
        $this->_icon_child = $str;
        return $this;
    }

    public function getIcon_move ()
    {
        return $this->_icon_move;
    }

    public function setIcon_move ( $str = "glyphicon glyphicon-move" )
    {
        $this->_icon_move = $str;
        return $this;
    }

    public function getIcon_delete ()
    {
        return $this->_icon_delete;
    }

    public function setIcon_delete ( $str = "glyphicon glyphicon-delete" )
    {
        $this->_icon_delete = $str;
        return $this;
    }

    public function getIcon_info ()
    {
        return $this->_icon_info;
    }

    public function setIcon_info ( $str = "glyphicon glyphicon-info-sign" )
    {
        $this->_icon_info = $str;
        return $this;
    }

    public function getIcon_handle ()
    {
        return $this->_icon_handle;
    }

    public function setIcon_handle ( $str = "glyphicon-info-sign" )
    {
        $this->_icon_handle = $str;
        return $this;
    }

    public function getEvent_on_click ()
    {
        return $this->_event_on_click;
    }

    public function setEvent_on_click ( $str = "" )
    {
        $this->_event_on_click = $str;
        return $this;
    }

    public function getStatus_tree ()
    {
        return $this->_status_tree;
    }

    public function setStatus_tree ( $str = "" )
    {
        $this->_status_tree = $str;
        return $this;
    }

    public function getInterdisciplinarite()
    {
    	return $this->_interdisciplinarite;
    }

    public function setInterdisciplinarite ($path_to_default = "")
    {
    	$this->_interdisciplinarite = $this->get ($path_to_default);
    	return $this;
    }

    public function getPrefix_inter ( )
    {
        return $this->_prefix_inter;
    }

    public function setPrefix_inter ( $str = "" )
    {
        $this->_prefix_inter = $str;
        return $this;
    }

    /**
     * Set _data field of object
     * @throws Zend_Form_Exception
     */
    protected function _create ()
    {
        if (!$this->_data) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Peut pas générer la liste sans données...');
        }

        try {
            if (is_array ($this->_data)) {
                $return = $this->_data;
            } else {
                $return = Zend_Json::decode ($this->_data);
            }
        } catch (Exception $e) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Peut pas générer la liste...');
        }

        $this->_data = $this->_update ($return);
    }

    /**
     * Can be overwritten in subclass
     * @param array $a
     * @return array
     */
    protected function _update ($a = array ())
    {
        return $a;
    }

    public function addStylesheets ($code)
    {
        if (!array_search ($code, $this->_css)) {
            $this->_css[] = $code;

        }

        return $this;
    }

    public function getStylesheets ()
    {
        return $this->_css;
    }

    public function clearStylesheets ()
    {
        $this->_css = array ();
        return $this;
    }

	public function isValid ($value, $context = null)
	{

		if ($value) {

			$iterator = new RecursiveArrayIterator( $this->_data );

			if (!function_exists('search_iterator')) {
                /**
                 * @param RecursiveIterator $iterator
                 * @param $needed
                 * @param $prefix
                 */
				function search_iterator ($iterator, &$needed, $prefix) {
					while ($iterator->valid()) {

						if ($iterator->hasChildren()) {
							search_iterator($iterator->getChildren(), $needed, $prefix);
						}

                        if (array_key_exists( $prefix.$iterator->key(), $needed )) {
                            unset($needed[$prefix.$iterator->key()]);
                        }
                        $cle = $iterator->key();
                        $pattern = '/'.$prefix.'/';
                        if (preg_match($pattern,$cle)) {
                            $cle = preg_replace($pattern,'',$cle);
                        }
                        if (array_key_exists( $cle, $needed )) {
                            unset($needed[$cle]);
                        }
                        unset($needed[$iterator->key()]);

						$iterator->next();
					}
				}
			}

            $value2 = array_flip ($value);
            $prefix = $this->getPrefix_inter();
            search_iterator ($iterator, $value2, $prefix);

			if (!empty ($value2)) {
				$this->_messages = array ("Vous ne pouvez pas saisir d'autres valeurs ('" . implode ("', '", array_flip ($value2)) . "') que celles se trouvant dans l'arbre ci-dessous...");
				$this->_errors = array("NOT_IN_ARRAY");
				return false;
			}
		}

		return parent::isValid ($value);
	}

    /**
     * @param mixed $value
     * @return Ccsd_Form_Element_Thesaurus
     */
    public function setValue ($value)
    {
        $value = $this->filter($value);
        return parent::setValue($value);
    }

    /**
     * Clean value by removing the interPrefix
     * Todo: Control, if it a prefix or not.
     *       Yet, we clean the value everywhere
     * @param $value
     * @return array|mixed
     */
    protected function filter ($value)
    {
        $pattern = '/'.$this->getPrefix_inter().'/';
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = preg_replace($pattern,'',$v);
            }
        } else {
            $value = preg_replace($pattern,'',$value);
        }
        return $value;
    }
}