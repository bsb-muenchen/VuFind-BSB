<?php
/**
 * Model for XML records in Solr.
 *
 * PHP version 8
 *
 * Copyright (C) Bayerische Staatsbibliothek 2024.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Bayerische Staatsbibliothek
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */

namespace Bsb\RecordDriver;

use Laminas\Log\LoggerAwareInterface;
use SimpleXMLElement;
use VuFind\I18n\Translator\TranslatorAwareInterface;

class SolrXml extends \VuFind\RecordDriver\SolrDefault implements LoggerAwareInterface, TranslatorAwareInterface
{
    use \VuFind\Log\LoggerAwareTrait;
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $mappings;
    protected $fidxmlFields;
    protected $searchSettings;
    public $vufindConfig;

    /**** Initialisation methods *******************************************/

    /**
     * Constructor
     *
     * @param \Laminas\Config\Config $vufindConfig VuFind main configuration (omit
     * for built-in defaults)
     * @param \Laminas\Config\Config $fidxmlConfig Record-specific configuration
     * file (omit to use $fidxmlConfig as $recordConfig)
     * @param \Laminas\Config\Config $searchSettings Search-specific configuration
     * file
     */
    public function __construct($vufindConfig = null, $fidxmlConfig = null, $searchSettings = null)
    {
        parent::__construct($vufindConfig, $fidxmlConfig, $searchSettings);
        $this->mappings = array();
        $this->vufindConfig = $vufindConfig;
        $this->fidxmlFields = $fidxmlConfig->Fields;
        $this->searchSettings = $searchSettings;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function readData($viewField)
    {
        $indexField = $this->getIndexField($viewField);
        // Remove invisible non-sorting characters (ITA-1403)
        $indexField = str_replace("\u{0098}", "", $indexField);
        $indexField = str_replace("\u{009C}", "", $indexField);

        $data = array();
        if ($indexField) {
            $xml = new SimpleXMLElement($indexField);
            foreach (array_keys($this->fidxmlFields->toArray()) as $field) {
                $output = array();
                $xpath = $this->fidxmlFields[$field];
                if (($pos = strpos($xpath, "#")) !== false) {
                    $result = $xml->xpath(substr($xpath, $pos + 1));
                    foreach ($result as $node) {
                        $output[] = $node;
                    }
                    $data[$field] = $output;
                } else {
                    $result = $xml->xpath($xpath);
                    foreach ($result as $node) {
                        if ($string = (string)$node) {
                            $output[] = $string;
                        }
                    }
                    $data[$field] = array_unique($output);
                }
            }
        }
        return $data;
    }

    /**** Common getter methods *******************************************/

    // Index-Fields
    public function getIndexField($fieldName)
    {
        if (isset($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        }
        return null;
    }

    // FullRecord-Fields
    public function getFidField($field)
    {
        if (!isset($this->data)) {
            $this->data = $this->readData('fullrecord');
        }
        // fs, 19.07.22: removed array_unique() in return value because it does not work reliably with SimpleXMLElement
        // https://stackoverflow.com/questions/20601975/array-unique-not-working-with-xml
        // non-node data is uniqued in readData anyway, xml nodes have to be checked by other methods
        return $this->data[$field] ?? array();
    }

    public function getFidField0($field, $default = '')
    {
        if ($element = $this->getFidField($field)) {
            return $element[0];
        }
        return $default;
    }

    public function getAllFidFields()
    {
        if (!isset($this->data)) {
            $this->data = $this->readData('fullrecord');
        }
        return $this->data;
    }

    public function getFidRootNode()
    {
        return $this->getFidField('rootNode')[0];
    }

    public function getFieldValueForFormatter($fieldName)
    {
        // used for RecordDataFormatter
        return $this->getFidField($fieldName);
    }

    public function getCollection()
    {
        return $this->getFidField('col');
    }

    public function getXmlData()
    {
        $indexField = $this->getIndexField('fullrecord');
        // Remove invisible non-sorting characters
        $indexField = str_replace("\u{0098}", "", $indexField);
        $indexField = str_replace("\u{009C}", "", $indexField);

        if ($indexField) {
            $xml = new SimpleXMLElement($indexField);
            return $xml->asXML();
        }

        return null;
    }
}
