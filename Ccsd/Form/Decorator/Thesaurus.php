<?php

/**
 * Class Ccsd_Form_Decorator_Thesaurus
 * @property Ccsd_Form_Element_Thesaurus $_element
 */
class Ccsd_Form_Decorator_Thesaurus extends Zend_Form_Decorator_Abstract
{
    
    use Ccsd_Form_Trait_GenerateFunctionJS;
    
    public $values = array ();

    public $close;
    public $open;
    public $name_s;
    public $name;
    public $move;
    public $delete;
    public $tip;
    public $n;
    public $events;
    public $code = 'domainCode';
    public $label = 'domainName';
    public $idfilter;
    /** @var  array */
    public $items;

    protected $required_items;
    protected $endJavascript;
    
    const DISPLAY_FALSE        = 0; 
    const DISPLAY_TRUE         = 1;
    const DISPLAY_FOLDER_OPEN  = 2;
    const DISPLAY_FOLDER_CLOSE = 3;

    /* Variables de rendu */
    protected $_output = "";

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $this->_prefix = 'thesaurus/';
        
        /* @var Ccsd_Form_Element_Thesaurus $element */
        $element   = $this->getElement();
        
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        /* Tableau JSON */
        $this->items = $element->getData();
        if (!is_array ($this->items)) {
            return $content;
        }

        $this->values = $element->getValue();
        if (!(isset ($this->values) && $this->values)) {
            $this->values = array ();
        }
        
        /* Fonction à surcharger */
        $this->_initViewVariables ();

        /* Fonction à surcharger */
        $this->_initInternVariables ();

        /* Uniquemet si l'élément permet la sélection d'items */
        if ($this->selectable) {
            $this->_renderSelectable();
        }
        $this->_output .= "<div id='panel_" . $this->name . "' style='";
        
        /* Uniquemet si l'élément permet la sélection d'items */
        if ($this->selectable) {
            $this->_output .= $this->isBtnDisplayed ? "" : ($this->collapsable ? "display: none;" : "");
        }
        $this->_output .= "margin-bottom: 10px;'>";
        
        /* Fonction à surcharger */
        $this->addFilterField ();

        /* Fonction à surcharger */
        $this->prepend ();
        
        /* Fonction à surcharger */
        $this->init ();
        
        /* Fonction à surcharger */
        $this->append ();

        /* Fonction à surcharger */
        if ($this->collapsable) {
            $this->_renderCollapsable();
        }
        /* Ajout du javascript pour l'élément */
        $this->_addJavascript ();
        
        /* Ajout du css pour l'élément */
        $this->_addCss ();

        return $content . $this->_output;
    }

    /* Variables d'initialisation pour la génération du Javascript */
    protected function _initViewVariables () 
    {
    	$this->close        = $this->_element->getIcon_parent_close();
        $this->open         = $this->_element->getIcon_parent_open ();
        $this->name         = $this->_element->getName();
        $this->name_s       = $this->_element->getFullyQualifiedName() . "[]";
        $this->nameblock    = str_replace(array("[", "]"), array("-", ""), $this->_element->getFullyQualifiedName()) . "-element";       
        $this->move         = $this->_element->getIcon_move();
        $this->delete       = $this->_element->getIcon_delete();
        $this->n            = $this->_element->getName();
        $this->events       = $this->_element->getEvent_on_click();
        $this->desc         = (bool) $this->_element->getTypeahead_description();
        $this->_info        = $this->_element->getIcon_info();
        $this->info_handle  = $this->_element->getIcon_handle();
        $this->idfilter     = uniqid('typeahead');
        $this->_iclose      = $this->_element->getIcon_parent_close();
        $this->_iopen       = $this->_element->getIcon_parent_open();
        $this->tip	        = (int)$this->_element->getValueTip();
    }
    
    /* Variables pour le fonctionnement interne */
    protected function _initInternVariables ()
    {
    	$this->filtering			= $this->_element->getFiltering();
    	$this->required_items   	= $this->_element->getReqitems();
        $this->collapsable      	= $this->_element->getCollapsable();
        $this->levels           	= array ();
        $this->interdisciplinarite  = (boolean) $this->_element->getInterdisciplinarite();
        $this->selectable       	= $this->_element->getSelectable();
        $this->clickable        	= $this->_element->getClickable();
        $this->selectable_s     	= $this->_element->getList_values();
        $this->domain_title     	= $this->_element->getList_title();
        $this->isTypeahead      	= $this->_element->getTypeahead();
        $this->typeahead_desc   	= $this->_element->getTypeahead_description();
        $this->isShowing_icons  	= $this->_element->getShowing_icon();
        $this->isIconsHover     	= $this->_element->getIcon_parent_hover();
        $this->icon_info        	= $this->_element->getIcon_info();
        $this->icon_handle      	= $this->_element->getIcon_handle();
        $this->typeahead_label  	= $this->_element->getTypeahead_label();
        $this->typeahead_v      	= $this->_element->getTypeahead_value();
        $this->typeahead_h      	= $this->_element->getTypeahead_height();
        $this->status_tree      	= $this->_element->getStatus_tree();
        $this->isBtnDisplayed   	= (bool)(int) $this->status_tree;
        $this->isBtnInterDisplayed  = false; /*TODO*/
        $this->isShowing_caret  	= $this->_element->getShowing_caret();
        $this->icon_close_pa    	= $this->_element->getIcon_parent_close();
        $this->icon_child       	= $this->_element->getIcon_child();
        $this->sortable         	= $this->_element->getSortable();
        $this->use_display      	= $this->_element->getUse_display ();
        $this->displayed_status 	= $this->isBtnDisplayed ? str_split($this->status_tree, 1) : null;
        $this->root_selectable      = (bool) $this->_element->getParentNode();
        $this->endJavascript    	= "";
    }

    protected function _renderSelectable ()
    {
        if ($this->selectable_s) {
            $this->_output .= "<span style='padding: 4px 6px; display: inline-block; height: 22px; font-style: italic; font-weight: bold;'>" . $this->selectable_s . "</span>";
        }
    
        $this->_output  .= "<ul id='list_" . $this->name . "' style='padding-left: 0px!important;'></ul>";
    
        if (isset ($this->required_items) && is_array ($this->required_items)) {
        	foreach ($this->required_items as $value) {
        		
        		//$(\'#%%IDFILTER%%\').closest(\'.form-group\').find(\'ul.tree
        		$this->endJavascript .= '$(\'#' . $this->_element->getId() . '-element\').find(\'ul.tree input[value="' . $value . '"]:first\').closest("li").find(".libelle:first").trigger("click", false);';
        	}
        }

        if (is_array($this->values) && !empty($this->values)) {
        	
        	if (isset ($this->required_items) && is_array ($this->required_items)) {
        		$this->values = array_diff($this->values, $this->required_items);
        	}
            foreach ($this->values as $value) {
	            $this->endJavascript .= '$(\'#' . $this->_element->getId() . '-element\').find(\'ul.tree input[value="' . $value . '"]:first\').closest("li").find(".libelle:first").trigger("click", false);';
	        }
        }
    }
    
    protected function _renderCollapsable ()
    {

        if ($this->selectable) {
            $translator = Ccsd_Form::getDefaultTranslator();
            $element = $this->_element;
            $elemName    = $element->getName();
            $collapseMsg = $translator->translate($element->getOption_collapse_msg());
            $expandMsg   = $translator->translate($element->getOption_expand_msg());

            $this->_output .= "<div class='row'" . ($element->getInterdisciplinarite() ? " style='margin-top: 5px;'" : "") . ">";
        	$this->_output .= "<div class='col-md-2'>";
            $this->_output .= "<button type='button' class='btn btn-sm btn-primary' ";
            $this->_output .= "data-status='" . ($this->isBtnDisplayed ? "show" : "hide") . "' ";
            $this->_output .= "data-hide='$collapseMsg' ";
            $this->_output .= "data-show='$expandMsg' ";
            $this->_output .= "onclick='$(\"#panel_$elemName\").toggle(); $(\"#panel_$elemName\").find(\".clearfix\").toggle(); if ($(this).attr(\"data-status\") == \"hide\") { $(this).html($(this).attr(\"data-hide\")); $(this).attr(\"data-status\", \"show\"); } else { $(this).html($(this).attr(\"data-show\")); $(this).attr(\"data-status\", \"hide\"); }' ";
            $this->_output .= ">";
            $this->_output .= $this->isBtnDisplayed ? $collapseMsg : $expandMsg;
            $this->_output .= "</button>";
            $this->_output .= "</div>";
            $this->_output .= "</div>";
        }
    }
    
    protected function _renderInterdisciplinarite ()
    {
    }
    
    private function _addJavascript ()
    {
        /* Options d'ajout de javascript */
        $options = array ();

        if ($this->isTypeahead) {
            $options['documentReady'][] = 'init';
            if ($this->desc)
                $options['documentReady'][] = 'tooltip';
        }
        
        if ($this->selectable) {
            $options['documentReady'][] = 'selectable';
            if ($this->sortable) {
                $options['documentReady'][] = 'sortable';
            }
        }

        if ($this->clickable) {
        	$options['documentReady'][] = 'click';
        }

        if (!empty ($options)) {
            $this->buildJS('thesaurus/', $options);
        }

        if ($this->selectable) {
            $this->_element->addDocumentReady($this->endJavascript);
            if (isset($this->required_items) && is_array ($this->required_items)) {
                foreach ($this->required_items as $item) {
                    $this->_element->addDocumentReady("setRequiredItems_" . $this->idfilter . " ('$item');");
                }
            }
        }
    }
    
    private function _addCss ()
    {
        $aCss = array (
                "ICONS"         => $this->isShowing_icons ? file_get_contents (CCSDLIB_SRC . "/css/form/decorator/thesaurus/icons.css") : "",
                "ICONS_HOVER"   => $this->isShowing_icons && $this->isIconsHover ? file_get_contents (CCSDLIB_SRC . "/css/form/decorator/thesaurus/icons_hover.css") : "",
                "CARET"         => $this->isShowing_caret ? file_get_contents (CCSDLIB_SRC . "/css/form/decorator/thesaurus/caret.css") : "",
        );

        $aKey = array_keys($aCss);
        
        $iclose = $this->close;
        $iopen  = $this->open;
        
        $sCss = array_reduce ($aCss, function ($v, $w) use ($iclose, $iopen, &$aKey) {
            $v = str_replace("%%" . array_shift($aKey) . "%%", $w, $v);
            $v = str_replace("%%ICLOSE%%", $iclose, $v);
            $v = str_replace("%%IOPEN%%", $iopen, $v);
            return $v;
        }, file_get_contents (CCSDLIB_SRC . "css/form/decorator/thesaurus/init.css"));

        $this->_element->addStylesheets ($sCss);
    }
    
    public function addFilterField ()
    {
        $this->_output .= "<div class='clearfix' style='";
        /* Uniquemet si l'élément permet la sélection d'items */
        if ($this->selectable) {
            $this->_output .= $this->isBtnDisplayed ? "" : ($this->collapsable ? "display: none;" : "");
        }
        $this->_output .= "'>";
        $this->_output .= "<div class='pull-left'>" . $this->domain_title . "</div>";

        if ($this->filtering) {
            $this->_output .= "<div class='pull-right'>";

            /* Mise à niveau avec traductions */
            $aData = $this->_update ();

            $this->_output .= '<div class="input-group" style="width: 200px;">';
            $this->_output .= '<input id="' . $this->idfilter . '" class="typeahead form-control input-sm" placeholder="'
                           .  $this->typeahead_label
                           .  '" type="text" data-provide="typeahead" data-json="'
                           .  str_replace('"', '&quot;', Zend_Json::encode( $aData ))
                           .  '" value="' . addslashes($this->typeahead_v) . '" />';
            $this->_output .= '<span class="input-group-addon" data-container="body" data-placement="bottom" data-original-title="' . $this->typeahead_desc . '" data-toggle="tooltip">';
            
            $this->_output .= "<i class='" . $this->icon_info . "'></i>";
            
            $this->_output .= '</span>';
            $this->_output .= '</div>';

            $this->_output .= "</div>";
        }
        
        $this->_output .= "</div>";
    }

    /**
     * @return array
     */
    protected function _update ()
    {
        $aData = $this->items;
        
        /* array_keys récursif */
        $array_keys_recursive = function ($v) use (&$array_keys_recursive) {
            $childs = array ();
            foreach ($v as $v1) {
                $childs = array_merge($childs, $array_keys_recursive ($v1));
            }
            return array_merge (array_keys ($v), $childs);
        };
        
        $aData = $array_keys_recursive ($aData);
        $aData = array_map (function ($v) {
            $pattern = '/'.$this->_element->getPrefix_inter().'/';
            if (($this->_element->getInterdisciplinarite()) && (preg_match($pattern,$v))) {
                $v = preg_replace($pattern,'',$v);
            }
            $translator = Ccsd_Form::getDefaultTranslator();
            $locale = $this->_element->getLocale();
            if ($translator->isTranslated($this->_element->getPrefix_translation() . $v, $locale)) {
                $newLabel = $translator->translate($this->_element->getPrefix_translation() . $v, $locale);
            } else {
                $newLabel = $translator->translate($v, $locale);
            }

            return array (
                    $this->label => $newLabel,
                    $this->code  => $v
            );
        }, array_combine ($aData, $aData));

        return $aData;
    }

    /**
     *
     */
    public function init ()
    {
        $indice             = 0;
        $displayed_status   = $this->displayed_status;
        $output             = $this->_output;
        $isBtnDisplayed     = $this->isBtnDisplayed;
        $use_display        = $this->use_display;
        $displayed_status   = $this->displayed_status;
        $indice             = isset ($this->indice) ? $this->indice : 0;
        $typeahead_v        = $this->typeahead_v;
        $isShowing_icons    = $this->isShowing_icons;
        $icon_child         = $this->icon_child;
        $display_tag        = $this->display_tag;
        $icon_close_pa      = $this->icon_close_pa;
        $isShowing_caret    = $this->isShowing_caret;
        $endJavascript      = $this->endJavascript;
        $root				= $this->root_selectable;

        /* Function récursive pour le rendu d'items */
        $render = function ($item, $code = null, $prefix = "") use (
            &$render, &$output, $isBtnDisplayed, $use_display,
            $displayed_status, &$indice, $typeahead_v, $isShowing_icons,
            $icon_child, $display_tag, $icon_close_pa, $isShowing_caret,
            &$endJavascript, $root) {

            $childrens = !empty($code);

            $style = "";
            if (isset($displayed_status) && isset($displayed_status[$indice]) && !$prefix) {
                switch ($displayed_status[$indice]) {
                    case self::DISPLAY_FALSE :
                        $style = "display : none;";
                        break;
                    case self::DISPLAY_FOLDER_OPEN :
                        $endJavascript .= '$(\'#' . $this->_element->getName() . '-element\').find(\'ul.tree input[value="' . $item . '"]\').click();';
                        $style = "display : list-item;";
                        break;
                    default:
                        $style = "display : list-item;";
                        break;
                }   
            }

            $indice++;

            $this->_output .= "<li style='$style'>";
            
            if ($this->_element->getInterdisciplinarite()) {
                $pattern = '/'.$this->_element->getPrefix_inter().'/';
                if (preg_match($pattern, $item)) {
                    if (!preg_match($pattern, $prefix)) {
                        $prefix .= $this->_element->getPrefix_inter();
                    }
                    $item = preg_replace($pattern, '', $item);
                }
            }
            
            $this->_output .= "<input id='$prefix$item' value='$item' style='display: none;' type='" . ($childrens ? "checkbox" : "hidden") . "'/>";

            if ($childrens) {
                $this->_output .= "<label for='$prefix$item' style='margin-bottom: 2px; margin-right: 5px;'>";
        
                if ($isShowing_caret) {
                    $this->_output .= "<span class='caret' style='margin-top: 0px;'></span>";
                }
                if ($isShowing_icons) {
                    $this->_output .= "<i class='$icon_close_pa'></i>";
                }
                $this->_output .= "</label>";
            } else if ($isShowing_icons) {
                $this->_output .= "<label  for='$prefix$item' style='margin-bottom: 2px; margin-right: 5px;'>";
        
                if ($isShowing_icons) {
                    $this->_output .= "<i class='$icon_child' style='margin-left: 13px; cursor: default;'></i>";
                }
                $this->_output .= "</label>";
            }
        
            $this->_output .= "<span class='libelle";
            
            if ($root || !$childrens) {
            	$this->_output .= " click";
            }

            $this->_output .= "' style='cursor: pointer;'>";
        
            if (isset ($use_display[$item])) {
                $this->_output .= $use_display[$item];
            } else {
                $pattern = '/'.$this->_element->getPrefix_inter().'/';
                if (($this->_element->getInterdisciplinarite()) && (preg_match($pattern,$item))) {
                    $item = preg_replace($pattern,'',$item);
                }
                if (Ccsd_Form::getDefaultTranslator()->isTranslated($this->_element->getPrefix_translation() . $item, $this->_element->getLocale())) {
                    $this->_output .= Ccsd_Form::getDefaultTranslator()->translate($this->_element->getPrefix_translation() . $item, $this->_element->getLocale());
                } else {
                    $this->_output .= Ccsd_Form::getDefaultTranslator()->translate($item, $this->_element->getLocale());
                }
            }

            $this->_output .= "</span>";

            if ($childrens) {
                $this->_output .= "<ul>";
        
                foreach ($code as $i => $j) {
                    $render ($i, $j, $prefix);
                }
                $this->_output .= "</ul>";
            }
        };

        /* Appel au rendu d'items */
        foreach ($this->items as $item => $code) {
            $render ($item, $code);
        }

        $this->ouput = $output;
        $this->endJavascript = $endJavascript;
    }
    
    public function prepend ()
    {
        $this->_output .= "<div class='clearfix' style='max-height: ";
        $this->_output .= $this->typeahead_h;
        $this->_output .= "px; overflow: auto; ";
        /* Uniquemet si l'élément permet la sélection d'items */
        if ($this->selectable) {
            $this->_output .= $this->isBtnDisplayed ? "" : ($this->collapsable ? "display: none;" : "");
        }
        $this->_output .= "'>";
        $this->_output .= "<div style='display: none; padding: 10px; margin-top: 10px;' class='msg_no_result alert alert-warning'><span>" . Ccsd_Form::getDefaultTranslator()->translate($this->_element->getMsg_no_result()) . "</span></div>";
        $this->_output .= "<ul class='tree'>";
    }
       
    public function append ()
    {
        $this->_output .= "</ul>";
        $this->_output .= "</div>";
        $this->_output .= "</div>";
    }
    
}
