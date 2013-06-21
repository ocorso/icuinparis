<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Model_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    private $moduleTables = array();
    
    //####################################
    
    public function __construct($resourceName)
    {
        // Get needed mysql tables
        $tempTables = Mage::helper('M2ePro/Module')->getMySqlTables();

        // Sort by length tables
        do {
            $hasChanges = false;
            for ($i=0;$i<count($tempTables)-1; $i++) {
                if (strlen($tempTables[$i]) < strlen($tempTables[$i+1])) {
                    $temp = $tempTables[$i];
                    $tempTables[$i] = $tempTables[$i+1];
                    $tempTables[$i+1] = $temp;
                    $hasChanges = true;
                }
            }
        } while ($hasChanges);

        // Prepare sql tables
        //--------------------
        foreach ($tempTables as $table) {
            $this->moduleTables[$table] = $this->getTable($table);
        }
        //--------------------

        parent::__construct($resourceName);
    }

    //####################################

    public function startSetup()
    {
        return parent::startSetup();
    }

    public function endSetup()
    {
        $cacheKey = Mage::helper('M2ePro/Module')->getName().'_VERSION_UPDATER';
        Mage::app()->getCache()->remove($cacheKey);

        return parent::endSetup();
    }

    //####################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }
        $sql = $this->prepareSql($sql);
        $this->_conn->multi_query($sql);
        return $this;
    }

    public function runSqlFile($path)
    {
        if (!is_file($path)) {
            return $this;
        }
        $sql = file_get_contents($path);
        return $this->run($sql);
    }

    //####################################

    public function getModuleTables()
    {
        return $this->moduleTables;
    }
    
    public function getRelatedSqlFilePath($pathPhpFile)
    {
        return dirname($pathPhpFile).DS.basename($pathPhpFile,'.php').'.sql';
    }

    //####################################

    private function prepareSql($sql)
    {
        foreach ($this->moduleTables as $tableFrom=>$tableTo) {
            $sql = str_replace(' `'.$tableFrom.'`',' `'.$tableTo.'`',$sql);
            $sql = str_replace(' '.$tableFrom,' `'.$tableTo.'`',$sql);
        }
        return $sql;
    }

    //####################################
}