/* exported Br */
/* global NextDomElement */

/* 
 * Br(_id, _className)
 */
class Br extends NextDomElement {
	/**
	 * @constructor Br
	 * @description Object to create a textNode element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
  	super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.BR);
  }
}
