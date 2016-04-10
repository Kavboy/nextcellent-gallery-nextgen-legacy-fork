<?php

namespace NextCellent\RSS\Writer;

/**
 * @author  Niko Strijbol
 * @version 10/04/2016
 */
interface XMLElement {

	/**
	 * Return XML object
	 *
	 * @param \DOMDocument $document
	 *
	 * @return \DOMElement
	 */
	public function asXML(\DOMDocument $document);

}