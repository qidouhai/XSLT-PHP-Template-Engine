<?php
/**
 * @title            PH XSLT
 * @desc             XSLT PHP template engine.
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License.
 * @package          PH7 / Framework / Layout / Tpl / Engine / PHXSLT
 * @version          1.1
 */
namespace PH7\Framework\Layout\Tpl\Engine\PHXSLT;

class PHXSLT
{
    const ROOT_NAMESPACE = 'template';

    private $_oXml, $_oXsl, $_oXslProcessor, $_sOutput, $_oRoot, $_sFile;

    /**
     * Constructor.
     *
     * @param string $sFile The XSL file.
     */
    public function __construct($sFile)
    {
        // Creating objects
        $this->_oXml = new \DOMDocument('1.0', 'UTF-8');
        $this->_oXsl = new \DOMDocument;
        $this->_oXslProcessor = new \XsltProcessor;

        $this->_sFile = $sFile;

        $this->load();

         // Creation of the XML root node mandatory
        $this->_oRoot = $this->_oXml->createElement(STATIC::ROOT_NAMESPACE);
        // Insertion of this node in the tree view of the XML file
        $this->_oXml->appendChild($this->_oRoot);
    }

    /**
     * Generate XML Node.
     *
     * @param mixed $aData array
     * @param string $sNamespace Optional. Default ''
     * @return object this
     * @throws \PH7\Framework\Layout\Tpl\Engine\PHXSLT\Exception If the data value is not an array.
     */
    public function generateXMLNode($aData, $sNamespace = '')
    {
        if(!is_array($aData)) throw new Exception('The data value ​​must be of type array!');

        $sRoot = $this->_oXml->createElement($sNamespace);
        $this->_oRoot->appendChild($sRoot);

        foreach($aData as $sKey => $sValue) {
            $sNode = $this->_oXml->createElement($sKey);
            $sRoot->appendChild($sNode);
            $sContent = $this->_oXml->createTextNode(utf8_encode($sValue));
            $sNode->appendChild($sContent);
        }

        return $this;
    }

    /**
     * Output.
     *
     * @return void
     * @throws \PH7\Framework\Layout\Tpl\Engine\PHXSLT\Exception If the XSL file contains syntax errors.
     */
    public function render()
    {
        if(!@$this->_sOutput = $this->_oXslProcessor->transformToXML($this->_oXml))
            throw new Exception('Transformation syntax!');

        header('Content-Type: text/xml; charset=utf-8');
        echo $this->_sOutput;
    }

    /**
     * Set variables.
     *
     * @param mixed $mKey (array | string)
     * @param string $sValue Optional only if the variables are passed through an array. Default ''
     * @param string $sNamespace Optional. Default ''
     * @return object this
     */
    public function setParam($mKey, $sValue = '', $sNamespace = '')
    {
        if(is_array($mKey)) {
            foreach($mKey as $sKey => $sVal)
                $this->setParam($sKey, $sVal, $sNamespace); // Recursive method
        } else {
            $this->_oXslProcessor->setParameter($sNamespace, $mKey, $sValue);
        }

        return $this;
    }

    /**
     * Escape function, uses the PHP native htmlspecialchars but improves.
     *
     * @param string $sText
     * @param boolean $bStrip If true, the text will be passed through the strip_tags function PHP.
     * @return string The text to HTML entities.
     */
    public static function escape($sText, $bStrip = false)
    {
        return ($bStrip) ? strip_tags($sText) : htmlspecialchars($sText, ENT_QUOTES);
    }

    /**
     * Load XSL file.
     *
     * @return object this
     * @throws \PH7\Framework\Layout\Tpl\Engine\PHXSLT\Exception If the XSL file does not exist.
     */
    protected function load()
    {
        if(!@$this->_oXsl->load($this->_sFile)) {
            throw new Exception('While loading file: "' . $this->escape($this->_sFile) . '"');
        } else {
            $this->_oXslProcessor->registerPHPFunctions();
            $this->_oXslProcessor->importStylesheet($this->_oXsl);
        }

        return $this;
    }

}