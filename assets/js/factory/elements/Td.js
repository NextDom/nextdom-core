/* exported Td */
/* global NextDomEnum NextDomElement */

/* 
 * Td(_id, _className, _nextdomElement)
 */
class Td extends NextDomElement {
	/**
	 * @constructor Td
	 * @description Object to create a td element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
        super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.TD);
  }
}
