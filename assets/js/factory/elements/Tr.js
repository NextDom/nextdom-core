/* exported Tr */
/* global NextDomEnum NextDomElement */

/* 
 * Tr(_id, _className, _nextdomElement)
 */
class Tr extends NextDomElement {
	/**
	 * @constructor Tr
	 * @description Object to create a tr element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
        super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.TR);
  }
}
