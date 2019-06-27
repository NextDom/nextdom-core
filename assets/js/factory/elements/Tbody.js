/* exported Tbody */
/* global NextDomEnum NextDomElement */

/* 
 * Tbody(_id, _className, _nextdomElement)
 */
class Tbody extends NextDomElement {
	/**
	 * @constructor Tbody
	 * @description Object to create a tbody element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
        super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.TBODY);
  }
}
