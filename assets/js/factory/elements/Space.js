/* exported Space */
/* global NextDomElement */

/* 
 * Space(_id, _className)
 */
class Space extends NextDomElement {
	/**
	 * @constructor Space
	 * @description Object to create a textNode element with a space
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
  constructor(_id,_className) {
  	super(_id,_className);
    this.htmlElement = new TextNode(_id, _className, NextDomEnum.ElementType.SPACE).htmlElement;
  }
}
