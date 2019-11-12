<?php

/**
 * Aide à la construction des requêtes avec solarium
 */
class Ccsd_Search_Solr_Search extends Ccsd_Search_Solr {

    const SOLR_DEFAULT_BOOLEAN_OPERATOR = 'AND';
    const SOLR_DEFAULT_SORT_TYPE = 'score desc';

    private $_checkedFilters;
    private $_defaultFilters;
    private $_facets;
    private $_returnedFields;

    /**
     *
     * @var \Solarium\QueryType\Select\Query\Query object La requête en cours
     */
    private $_query;

    /**
     *
     * @var array Paramètres de recherche récupérés
     */
    private $_rawSearchParams = array();

    /**
     *
     * @var string[] // Paramètres de recherche pour la vue
     */
    private $_parsedSearchParams = array();

    /**
     *
     * @var array // Liste des filtres pour la vue
     */
    private $_parsedFilterParams;
    /**
     *
     * @var array // Liste des filtres pour la vue
     */
    private $_filterExcludeTags;

    /**
     *
     * @var string Opérateur booléen par défaut entre les termes
     */
    private $_booleanOperator;

    /**
     * Nombre de résultats par page
     *
     * @return Ccsd_Search_Solr_Search
     */
    public function queryAddResultPerPage($paginatorNumberOfResultsArray = null, $paginatordefaultNumberOfResults = null) {
        if ($paginatorNumberOfResultsArray == null) {
            return $this;
        }

        if ($paginatordefaultNumberOfResults == null) {
            return $this;
        }
        $resultsPerPage = $this->getRawSearchParamsbyKey('rows');

        if (($resultsPerPage == null) or ( $resultsPerPage == $paginatordefaultNumberOfResults)) {
            $this->setParsedSearchParamsbyKey('rows', $paginatordefaultNumberOfResults);
        } else {

            if (array_search($resultsPerPage, $paginatorNumberOfResultsArray)) {
                $this->setParsedSearchParamsbyKey('rows', $resultsPerPage);
            } else {
                // valeur inacceptable
                $this->setParsedSearchParamsbyKey('rows', $paginatordefaultNumberOfResults);
            }
        }

        return $this;
    }

    /**
     * Traitement de la requête de recherche avancée
     *
     * @return string
     */
    public function queryParseAdvancedSearch() {
        $this->setBooleanOperator();
        $qa = array();

        // supprime le reste d'une recherche simple précédente
        $this->setParsedSearchParamsbyKey('q');

        // on garde qa tel quel pour le formulaire

        $fieldsArr = $this->getRawSearchParamsbyKey('qa');

        if (is_array($fieldsArr)) {
            $qaParsed = [];
            foreach ($fieldsArr as $solrField => $values) {

                $valuesAsString = trim(implode(' ', $values));

                if ($valuesAsString != '') {
                    $qa [] = ' ' . $solrField . ':(' . $valuesAsString . ')';
                    foreach ($values as $val) {
                        $qaParsed [] = 'qa[' . $solrField . '][]=' . urlencode($val);
                    }
                }
            }
            $this->setParsedSearchParamsbyKey('qa', implode('&', $qaParsed));
        }
        $qa = implode(' ' . $this->getBooleanOperator() . ' ', $qa);
        return trim($qa);
    }

    /**
     * Ajout successif des facettes à générer
     *
     * @param array $excludeTags
     * @return Ccsd_Search_Solr_Search
     */
    public function queryAddFacets($facetsArr = null) {
        if ($facetsArr == null) {
            return $this;
        }

        $query = $this->getQuery();
        $excludeTags = $this->getFilterExcludeTags();

        if (is_array($facetsArr)) {

            // @see
            // https://wiki.apache.org/solr/SimpleFacetParameters#facet.threads
            $query->addParam('facet.threads', count($facetsArr));

            $facetSet = $query->getFacetSet();
            foreach ($facetsArr as $facet) {

                $fc = $facetSet->createFacetField($facet ['fieldName'])->setField($facet ['fieldName'])->setLimit($facet ['maxReturned'])->setMincount($facet ['minCount']);

                if ($facet ['sort'] == 'index') {
                    $fc->setSort('index');
                }

                if (is_array($excludeTags)) {
                    foreach ($excludeTags as $tag) {
                        // le tag contient le nom du paramètre utilisé
                        // dans
                        // l'URL
                        $tagAsArray = explode('__', $tag);
                        if ($tagAsArray [1] == $facet ['fieldName']) {
                            $fc->addExclude($tag);
                            /**
                             * Pour créer la facette, exclue un filtre en le
                             * mentionnant par son tag correspondant
                             * le tag doit être défini avec addTag()
                             */
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Filtres de recherche : mise en place des filtres utilisateur
     * correspondance entre le nom du champ
     * passé dans l'URL et son nom dans l'index solr
     *
     * @see solr.json
     */
    public function queryAddFilters() {
        $filterMapping = null;

        $filterParams = [];
        $parsedSearchParams = '';
        $query = $this->getQuery();

        $helper = $query->getHelper();
        $indexOfArray = 0;

        $searchParams = $this->getRawSearchParams();

        unset($searchParams ['q']);
        unset($searchParams ['qa']);
        unset($searchParams ['controller']);
        unset($searchParams ['action']);
        unset($searchParams ['module']);
        unset($searchParams ['rows']);
        unset($searchParams ['page']);
        unset($searchParams ['lang']);
        unset($searchParams ['sort']);
        unset($searchParams ['submit']);
        unset($searchParams ['tampid']);
        unset($searchParams ['_module']);
        unset($searchParams ['submit_advanced']);

        foreach ($searchParams as $solrFieldName => $filterValue) {

            $filterParams [$solrFieldName] = explode(' OR ', $searchParams [$solrFieldName]);

            $filterValue = $this->getRawSearchParamsbyKey($solrFieldName);

            if ($filterValue != null) {

                $this->setParsedSearchParamsbyKey($solrFieldName, $filterValue);

                // ajout des filtres de recherche successifs

                $filterValue = trim($filterValue, '"');

                $nameOfTagFilter = 'tag' . $indexOfArray . '__' . $solrFieldName;

                // créé le tableau de tags à exclure
                $excludeTags [] = $nameOfTagFilter;

                // filtre pour une recherche exacte avec un champ type string
                //permet un filtre de ce type authFullName_s:("Michèle Soria" OR "Alexis Darrasse" OR "Olivier Roussel")
                if (substr($solrFieldName, -2, 2) == '_s') {
                    $filterValueArray = explode(" OR ", $filterValue);
                    $filterValueArray = array_map(function($v) use ($helper) {
                        // Escape the term and put it into quote
                        return '"'. $helper->escapeTerm($v) . '"';
                        }, $filterValueArray);
                    $filterValue = implode(" OR ", $filterValueArray);
                } else {
                    $filterValue = $helper->escapeTerm($filterValue);
                }

                $query->createFilterQuery($nameOfTagFilter)->setQuery($solrFieldName . ':(' . $filterValue . ')')->addTag($nameOfTagFilter);

                /**
                 * addTag ajoute un tag qui doit être unique, il
                 * sert quand on génère les facettes pour
                 * exclure le filtre de la création des facettes
                 * un tag par filtre
                 *
                 * @see https://wiki.apache.org/solr/SimpleFacetParameters#Multi-Select_Faceting_and_LocalParams
                 */
                $indexOfArray ++;
                $this->setFilterExcludeTags($excludeTags);
            }
        }

        /**
         * Filtres de recherche : mise en place des filtres utilisateur
         * //
         */
        if (is_array($parsedSearchParams)) {
            $this->setParsedSearchParams(array_merge($this->getParsedSearchParams(), $parsedSearchParams));
        }

        /**
         * Filtres de recherche suite : récupération des filtres en
         * cours pour réaffichage
         */
        $this->setParsedFilterParams($filterParams);

        /**
         * Filtres de recherche suite : récupération des filtres en
         * cours pour réaffichage //
         */
        return $this;
    }

    /**
     * Filtres de recherche par défaut
     *
     * @return Ccsd_Search_Solr_Search
     */
    public function queryAddDefaultFilters($defaultFilters = null) {
        $query = $this->getQuery();

        if ($defaultFilters != null) {

            if (is_array($defaultFilters)) {

                foreach ($defaultFilters as $defaultFilterIndex => $defaultFilterToApply) {
                    $query->createFilterQuery('df' . $defaultFilterIndex)->setQuery($defaultFilterToApply);
                }
            }
        }

        // cas d'une collection
        if ((defined('MODULE')) and defined('SPACE_COLLECTION') and defined('SPACE_NAME')) {

            if (MODULE == SPACE_COLLECTION) {
                $query->createFilterQuery('df' . SPACE_NAME)->setQuery('collCode_s:' . strtoupper(SPACE_NAME));
            } else {
                // portail
                $query->createFilterQuery('df_status')->setQuery('NOT status_i:111');
            }
        }

        return $this;
    }

    /**
     * Ajoute les critères de tri
     *
     * @return Ccsd_Search_Solr_Search
     */
    public function queryAddSort() {
        $query = $this->getQuery();

        $sortValue = $this->getRawSearchParamsbyKey('sort');

        if (null == $sortValue) {
            // Tri par défaut = score desc = pas de tri
            return $this;
        }

        // le sens de tri est obligatoire
        $sortValuesArray = explode(' ', $this->getRawSearchParamsbyKey('sort'));
        $solrSortFieldName = htmlspecialchars($sortValuesArray [0]);

        $this->setParsedSearchParamsbyKey('sort', $this->getRawSearchParamsbyKey('sort'));


        if (!isset($sortValuesArray [1])) {
            $query->addSort($solrSortFieldName, $query::SORT_ASC);
            return $this;
        }


        switch ($sortValuesArray [1]) {
            case 'asc' :
                $query->addSort($solrSortFieldName, $query::SORT_ASC);

                break;
            case 'desc' :
                $query->addSort($solrSortFieldName, $query::SORT_DESC);

                break;
            default :
                $query->addSort($solrSortFieldName, $query::SORT_ASC);
                return $this;

                break;
        }

        return $this;
    }

    /**
     * Retourne une clé du tableau des Paramètres de recherche de la vue
     *
     * @param string $key
     * @return string
     */
    public function getParsedSearchParamsbyKey($key = null) {
        if (null == $key) {
            return null;
        }

        if (!is_array($this->getParsedSearchParams())) {
            return null;
        }

        $parsedSearchParams = $this->getParsedSearchParams();
        if (array_key_exists($key, $parsedSearchParams)) {
            return $parsedSearchParams [$key];
        } else {
            return null;
        }
    }

    /**
     * Ajoute ou écrase une valeur par clé au tableau des Paramètres de
     * recherche de la vue
     *
     * @param string $key
     * @param string $value
     * @return Ccsd_Search_Solr_Search
     */
    public function setParsedSearchParamsbyKey($key = null, $value = null) {
        if (null == $key) {
            return $this;
        }
        if (null == $value) {
            $parsedSearchParams = $this->getParsedSearchParams();

            unset($parsedSearchParams [$key]);

            $this->setParsedSearchParams($parsedSearchParams);
            return $this;
        }

        $parsedSearchParams = $this->getParsedSearchParams();

        $parsedSearchParams [$key] = $value;

        $this->setParsedSearchParams($parsedSearchParams);
        return $this;
    }

    /**
     * Ajoute ou écrase une valeur par clé au tableau des Paramètres de
     * recherche récupérés
     *
     * @param string $key
     * @param string $value
     * @return Ccsd_Search_Solr_Search
     */
    public function setRawSearchParamsbyKey($key = null, $value = null) {
        if (null == $key) {
            return $this;
        }
        if (null == $value) {
            return $this;
        }

        $rawSearchParams = $this->getRawSearchParams();

        $rawSearchParams [$key] = $value;

        $this->setRawSearchParams($rawSearchParams);
        return $this;
    }

    /**
     * Retourne une clé du tableau des Paramètres de recherche récupérés
     *
     * @param string $key
     * @return Ambigous <the, multitype:>|NULL|Ambigous <>
     */
    public function getRawSearchParamsbyKey($key = null) {
        if (null == $key) {
            return null;
        }

        if (!is_array($this->getRawSearchParams())) {
            return null;
        }

        $rawSearchParams = $this->getRawSearchParams();

        if (array_key_exists($key, $rawSearchParams)) {
            return $rawSearchParams [$key];
        } else {
            return null;
        }
    }

    /**
     *
     * @return $_query  // object de la requête en cours
     */
    public function getQuery() {
        return $this->_query;
    }

    /**
     *
     * @param object $_query
     */
    public function setQuery($_query) {
        $this->_query = $_query;
        return $this;
    }

    /**
     *
     * @return the $_rawSearchParams
     */
    public function getRawSearchParams() {
        return $this->_rawSearchParams;
    }

    /**
     * Enregistre un tableau de paramètres de requête passés par le formulaire
     *
     * @param array: $_rawSearchParams
     */
    public function setRawSearchParams($_rawSearchParams) {
        $this->_rawSearchParams = $_rawSearchParams;

        return $this;
    }

    /**
     *
     * @return $_parsedSearchParams
     */
    public function getParsedSearchParams() {
        return $this->_parsedSearchParams;
    }

    /**
     * Enregistre un tableau de paramètres de requête pour la vue
     *
     * @param array: $_parsedSearchParams
     */
    public function setParsedSearchParams(array $_parsedSearchParams) {
        $this->_parsedSearchParams = $_parsedSearchParams;
        return $this;
    }

    /**
     *
     * @return $_parsedFilterParams
     */
    public function getParsedFilterParams() {
        return $this->_parsedFilterParams;
    }

    /**
     *
     * @param multitype: $_parsedFilterParams
     */
    public function setParsedFilterParams($_parsedFilterParams = null) {
        $this->_parsedFilterParams = $_parsedFilterParams;
        return $this;
    }

    /**
     *
     * @return $_filterExcludeTags
     */
    public function getFilterExcludeTags() {
        return $this->_filterExcludeTags;
    }

    /**
     *
     * @param field_type $_filterExcludeTags
     */
    public function setFilterExcludeTags($_filterExcludeTags = null) {
        $this->_filterExcludeTags = $_filterExcludeTags;
        return $this;
    }

    /**
     *
     * @return the $_booleanOperator
     */
    public function getBooleanOperator() {
        return $this->_booleanOperator;
    }

    /**
     *
     * @param string $_booleanOperator
     */
    public function setBooleanOperator($_booleanOperator = null) {
        if (($_booleanOperator != 'AND') and ( $_booleanOperator != 'OR')) {
            $this->_booleanOperator = self::SOLR_DEFAULT_BOOLEAN_OPERATOR;
        } else {
            $this->_booleanOperator = $_booleanOperator;
        }

        $this->setParsedSearchParamsbyKey('op', $_booleanOperator);
        return $this;
    }

    /**
     * Ajoute les filtres par défaut à une URL à envoyer à solr
     *
     * @param array $defaultFilters
     * @return NULL string
     */
    static function getDefaultFiltersAsURL($defaultFilters = null) {
        if ($defaultFilters == null) {
            return null;
        }

        if (is_array($defaultFilters)) {
            $filterQuery = '';
            foreach ($defaultFilters as $defaultFilterIndex => $defaultFilterToApply) {
                $filterQuery .= '&fq=' . urlencode($defaultFilterToApply);
            }
        }

        return $filterQuery;
    }

    static function parseSolrError(Exception $e) {
        switch ($e->getCode()) {
            case '0' :
                $message = 'Le serveur est indisponible, merci de réssayer dans quelques instants.';
                break;
            case '400' :
                if (strpos($e->getMessage(), "sort param field can't be found") !== false) {
                    $message = "Le paramètre de tri n'est pas valide";
                } elseif (strpos($e->getMessage(), "undefined field ") !== false) {
                    $message = "Vous essayez d'utiliser un champ qui n'existe pas.";
                } else {
                    $message = 'Le serveur est indisponible, merci de réssayer dans quelques instants.';
                }

                break;

            default :
                $message = 'Le serveur est indisponible, merci de réssayer dans quelques instants.';
                break;
        }
        return $message;
    }

}

//End class

