/* exported A */
/* global NextDomEnum NextDomElement */

/* 
 * A(_id, _className)
 */
class A extends NextDomElement {
	/**
	 * @constructor A
	 * @description Object to create an A element
	 * @extends Element
	 * @public
	 * @param {string} _id id of the th element
	 * @param {string} _className name's class
	 * @return {void}
	 */
	constructor(_id, _className) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.A);
	}
}
