/* exported Label */
/* global NextDomElement NextDomEnum TextNode */

/* 
 * Label(_id, _className, _content)
 */
class Label extends NextDomElement {
	/**
	 * @constructor Label
	 * @description Object to create a label element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id id of the div element
	 * @param {string} _className name's class
	 * @param {string} _content nextdom element to add as child
	 * @return {void}
	 */
	constructor(_id,_className,_content) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.LABEL);
		this.addChild(new TextNode("","",_content));
	}
}
