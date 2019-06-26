/* exported HorizontalLayout */
/* global NextDomElement */

/* 
 * HorizontalLayout(_id, _className)
 */
class HorizontalLayout extends NextDomElement {
	/**
	 * @constructor HorizontalLayout
	 * @description simple table
	 * @extends NextDomElement
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
	constructor (_id,_className) {
		super(_id,_className);
		this.htmlElement = this._createLayout();
	}
    /**
     * @function HorizontalLayout#_createLayout
     * @instance
     * @public
     * @description Create the current layout for this nextdomElement
     * @return {HTMLElement} html element result
     */
	_createLayout(){
		this.layout= new Div(this.id,this.className);
		this.layout.getHTMLElement().style.display = "flex";
		this.setContainerElement(this.layout);
		return this.layout.getHTMLElement();
	}

	/**
	 * @function HorizontalLayout#addToLayout
	 * @instance
	 * @public
	 * @description Add an Element to the HorizontalLayout
	 * @param {NextDomElement} _element to add to the Layout
	 * @return {void}
	 */
	addToLayout(_element){
		if(!_element.getHTMLElement().style.width){
            _element.getHTMLElement().style.width = "100%";
		}
		_element.getHTMLElement().style.position = "relative";
		this.layout.addChild(_element);
	}
}
