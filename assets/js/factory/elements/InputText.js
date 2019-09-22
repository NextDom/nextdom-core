/* exported InputText */
/* global NextDomElement NextDomEnum TextNode */

/* 
 * InputText(_id, _className, _content)
 */
class InputText extends NextDomElement {
	/**
	 * @constructor InputText
	 * @description Object to create an input text
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id id of the div element
	 * @param {string} _className name's class
	 * @param {string} _content text value
	 * @return {void}
	 */
	constructor(_id,_className,_content) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.INPUTTEXT);
        this.htmlElement.setAttribute("value", _content);
        this.htmlElement.setAttribute("alt", _content);
	}
}
