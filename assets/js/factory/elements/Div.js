/* exported Div */
/* global NextDomEnum NextDomElement */

/* 
 * Div(_id, _className, _nextdomElement)
 */
class Div extends NextDomElement {
	/**
	 * @constructor Div
	 * @description Object to create a div element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id id of the div element
	 * @param {string} _className name's class
	 * @return {void}
	 */
	constructor(_id, _className) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.DIV);
	}
    /**
     * @function Div#setToggleable
     * @instance
     * @public
     * @description Set the current html element toggleable
     * @return {void}
     */
    setToggleable (){
        ClickHandler.getInstance().applyToggleClickEvents(this.htmlElement);
    }
    /**
     * @function Div#setResizable
     * @instance
     * @public
     * @description Set the current html element resizable
     * @return {void}
     */
    setResizable (){
        ResizeHandler.getInstance().applyResizeEvents(this.htmlElement);
    }
    /**
     * @function Div#setDraggable
     * @instance
     * @public
     * @description Set the current html element draggable
     * @return {void}
     */
    setDraggable (){
        DragNDropHandler.getInstance().applyDragEvents(this.htmlElement);
    }
    /**
     * @function Div#setDroppable
     * @instance
     * @public
     * @description Set the current html element droppable
     * @return {void}
     */
    setDroppable (){
        DragNDropHandler.getInstance().applyDropEvents(this.htmlElement);
    }
    /**
     * @function Div#setCtrlClickable
     * @instance
     * @public
     * @description Set the current html element key down Control and clickable
     * @return {void}
     */
    setCtrlClickable (){
        ClickHandler.getInstance().applyClickEvents(this.htmlElement);
    }
}
