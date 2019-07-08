/* exported VerticalLayout */
/* global NextDomElement Table Tr Td */

/* 
 * VerticalLayout(_id, _className)
 */
class VerticalLayout extends NextDomElement {
	/**
	 * @constructor VerticalLayout
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
     * @function VerticalLayout#_createLayout
     * @instance
     * @public
     * @description Create the current layout for this nextdomElement
     * @return {HTMLElement} html element result
     */
	_createLayout(){
		this.layout= new Table(this.id,this.className);
		this.layout.getHTMLElement().style.height = "100%";
		this.setContainerElement(this.layout);
		return this.layout.getHTMLElement();
	}

	/**
	 * @function VerticalLayout#addToLayout
	 * @instance
	 * @public
	 * @description Add an Element to the VerticalLayout
	 * @param {NextDomElement} _element to add to the Layout
	 * @return {void}
	 */
	addToLayout(_element){
		let _tr = new Tr(null,null);
		let _td = new Td(null,null);

		this.layout.addChild(_tr);
		_tr.addChild(_td);
		_td.addChild(_element);
	}
}
