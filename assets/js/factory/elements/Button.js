/* exported Button */


/*
 * Button(_id, _className, _event)
 */
class Button extends NextDomElement {
    /**
     * @constructor Div
     * @description Object to create a button element
     * @extends NextDomElement
     * @public
     * @param {string} _id id of the button element
     * @param {string} _className name's class
     * @param {string} _function name's function to call
     * @return {void}
     */
    constructor(_id, _className, _function) {
        super(_id,_className);
        this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.BUTTON);
        this.htmlElement.addEventListener(NextDomEnum.DOMEvent.CLICK,_function);
    }
}