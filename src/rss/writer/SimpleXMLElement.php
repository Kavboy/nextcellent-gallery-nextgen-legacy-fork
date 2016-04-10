<?php

namespace NextCellent\RSS\Writer;

/**
 * Class SimpleXMLElement
 * @package Suin\RSSWriter
 */
class SimpleXMLElement implements XMLElement
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $value;

    /** @var array */
    protected $attributes = [];

    /** @var XMLElement[] */
    protected $children = [];

    /** @var bool */
    protected $cdata;

    /**
     * SimpleXMLElement constructor.
     *
     * @param string      $name
     * @param null|string $value
     * @param bool        $cdata
     */
    public function __construct($name, $value = null, $cdata = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->cdata = $cdata;
    }

    /**
     * Help function to add a child node containing a value.
     *
     * @param string $name  The name of the element.
     * @param string $value The value of the element.
     * @param bool   $cdata If the value needs CDATA wrapping or not.
     *
     * @return SimpleXMLElement The created element.
     */
    protected function simpleChild($name, $value = null, $cdata = false)
    {
        $node = new SimpleXMLElement($name, $value, $cdata);
        $this->children[] = $node;
        return $node;
    }

    /**
     * Return XML object
     *
     * @param \DOMDocument $document
     *
     * @return \DOMElement
     */
    public function asXML(\DOMDocument $document)
    {
        if($this->cdata) {
            $element = $document->createElement($this->name);

            if($this->value !== null) {
                $cdata = $document->createCDATASection($this->value);
                $element->appendChild($cdata);
            }
        } else {
            $element = $document->createElement($this->name, $this->value);
        }
        

        foreach ($this->attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }

        foreach ($this->children as $child) {
            $element->appendChild($child->asXML($document));
        }

        return $element;
    }

    /**
     * Add a child to this XML element.
     * 
     * Note: it is often better to use the 'append...' method of the child
     * element.
     * 
     * @param XMLElement $element
     */
    protected function addChild(XMLElement $element)
    {
        $this->children[] = $element;
    }
}