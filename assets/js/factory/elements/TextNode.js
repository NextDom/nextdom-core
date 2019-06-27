/* exported TextNode */
/* global NextDomElement */

/* 
 * TextNode(_id, _className, _content)
 */
class TextNode extends NextDomElement {
	/**
	 * @constructor TextNode
	 * @description Object to create a textNode element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	 * @param {string} _content text to display
	* @return {void}
	*/
  constructor(_id,_className,_content) {
        super(_id,_className);
    this.htmlElement = this.createTextNode(_content);
  }
}
