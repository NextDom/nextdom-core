/* exported IFA */
/* global NextDomEnum NextDomElement */

/* 
 * IFA(_id, _className, _nextdomElement)
 */
class IFA extends NextDomElement {
	/**
	 * @constructor IFA
	 * @description Object to create an IFA element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id id of the th element
	 * @param {string} _className name's class
	 * @return {void}
	 */
	constructor(_id, _className) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.I);
        this.htmlElement.className = _className;
	}
}
