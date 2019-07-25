/* exported Span */
/* global NextDomEnum NextDomElement */

/* 
 * Span(_id, _className, _nextdomElement)
 */
class Span extends NextDomElement {
  /**
   * @constructor Span
   * @description Object to create a span element
   * @extends NextDomElement
   * @public
   * @param {string} _id id of the th element
   * @param {string} _className name's class
   * @return {void}
   */
  constructor(_id,_className) {
        super(_id,_className);
    this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.SPAN);
  }
}