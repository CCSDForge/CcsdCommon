<?php

/**
 * Class updateAnrScript
 *
 * Nettoyage de la base des projet ANR
 */
class updateAnrScript extends Hal_Script {
    /**
     * @param Zend_Console_Getopt $args
     */
    public function main($args) {
        Ccsd_Log::message("Demarrage du script...");

        try {

            $db = Zend_Db_Table::getDefaultAdapter();

            $sql  = $db->select()->from('REF_PROJANR')->where('ACRONYME = ""')->order(new Zend_Db_Expr('CAST(VALID as CHAR) DESC'));
            $stmt = $db->prepare($sql);

            Ccsd_Log::message("Requete preparee : " . $sql);
            Ccsd_Log::message("Demarrage du statement...");
            $stmt->execute();
            Ccsd_Log::message("Execution du statement terminee...");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Ccsd_Log::message("Nombre de lignes trouvées : " . count ($rows));

            foreach ($rows as $row) {
                try {
                    if ($row['REFERENCE'] != "") {
                        $sql = $db->select()->from('REF_PROJANR', 'ANRID')->where('ACRONYME != "" AND REFERENCE = ?', $row['REFERENCE'])->order(new Zend_Db_Expr('CAST(VALID as CHAR) DESC'));
                        $id = $db->fetchOne($sql);
                        if ($id) {
                            // Un autre projet avec la meme reference existe
                            // On en efface un et on garde l'autre
                            // On transfert les documents associes au premier sur le deuxieme
                            $docids = Ccsd_Referentiels_Anrproject::getRelatedDocid($row['ANRID']);
                            if (count($docids)) {
                                Hal_Document::updateMeta($docids, Ccsd_Referentiels_Anrproject::METANAME, $id, $row['ANRID']);
                                echo count($docids) . " dépôts modifiés" . PHP_EOL;
                            }
                            $db->delete('REF_PROJANR', 'ANRID = ' . (int)$row['ANRID']);
                            echo $row['ANRID'] . " deleted and replaced by " . $id . PHP_EOL;
                        } else {
                            // On a juste a mettre a jour la reference pour garder (ACRONYME, REFERENCE) unique
                            $db->update('REF_PROJANR', ['ACRONYME' => $row['REFERENCE']], ['ANRID = ?' => (int)$row['ANRID']]);
                            echo "ACRONYME added for " . $row['ANRID'] . PHP_EOL;
                        }
                    }
                } catch (Zend_Db_Exception $e) {
                    echo 'Probleme pour ' . $row['ANRID'] . ' (' . $e->getMessage() . ')';
                }
            }

            Ccsd_Log::message("Fin du script...");

        } catch (PDOException $e) {
            echo 'Connexion échouée : ' . $e->getMessage();
        }
    }

}

