/* exported Th */
/* global NextDomEnum NextDomElement */

/* 
 * Th(_id, _className, _nextdomElement)
 */
class Th extends NextDomElement {
	/**
	 * @constructor Th
	 * @description Object to create a th element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
        super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.TH);
  }
}
