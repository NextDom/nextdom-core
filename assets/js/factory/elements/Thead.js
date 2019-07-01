/* exported Thead */
/* global NextDomEnum NextDomElement */

/* 
 * Thead(_id, _className, _nextdomElement)
 */
class Thead extends NextDomElement {
	/**
	 * @constructor Thead
	 * @description Object to create a thead element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
	super(_id,_className);
	this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.THEAD);
  }
}
