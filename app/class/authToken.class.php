<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 13/03/14
 * Time: 11:46
 */

namespace gcc;


use Sabre\XML;
use Sabre\XML\Reader;
use Sabre\XML\Writer;


/**
 * Class authToken
 *
 * This class represents the authentication token
 * @package gubd
 */
class authToken //extends XML\Element\Base
{

    public $userName, $tokenType, $tokenValue;
    private $ipAddress, $dif;

    /**
     * @param $ipAddress
     * @param $tokenType
     * @param $userName
     */
    function __construct($userName, $tokenType, $ipAddress)
    {
        $this->ipAddress = $ipAddress;
        $this->tokenType = $tokenType;
        $this->userName = $userName;
        $this->dif = (date("h") * date('s')) . rand(10000, 99999);
        $this->buildString();
    }

    private function buildString()
    {
        $sec = new Secure();

        $ts = $this->dif . "#:#" . $this->ipAddress . "#:#" . $this->tokenType . "#:#" . $this->userName;

        $this->tokenValue = $sec->encrypt($ts);

        if (VERBOSELEVEL == \verbose::DEBUG) {
            $log = logFactory::getLogger();
            $log->Debug("Criating token  BCons=" . $ts . " value=" . $this->tokenValue);
        }
    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * Important note 2: You are responsible for advancing the reader to the
     * next element. Not doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param Reader $reader
     * @return mixed
     */
    static public function deserializeXml(Reader $reader)
    {

        // NOT IMPLEMENTED ON PURPUSE

        return null;

        /*$attributes = $reader->parseAttributes();

        $link = new self();
        foreach($attributes as $name=>$value) {
            if (property_exists($link,$name)) {
                $link->$name = $value;
            }
        }
        $reader->next();

        return $link;*/
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    function __toString()
    {
        return $this->tokenValue;
    }

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * Important note 2: If you are writing any new elements, you are also
     * responsible for closing them.
     *
     * @param Writer $writer
     * @return void
     */
    public function serializeXml(Writer $writer)
    {
        $writer->startElement('token');
        $writer->writeAttribute('tokenValue', (string)$this);
        $writer->writeAttribute('username', $this->userName);
        $writer->writeAttribute('tokenType', $this->tokenType);

        //$writer->writeElement('username', new XML\Element\Base($this->userName));
        //$writer->writeElement('tokentype',new XML\Element\Base( $this->tokenType));

        $writer->endElement();
    }
}