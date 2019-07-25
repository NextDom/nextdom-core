/* exported NextDomEnum */
/* global */
"use strict";
/*
 * NextDomEnum
 */
class NextDomEnum {
    /**
     * @constructor NextDomEnum
     * @description Defining all enum values used throw the application
     * @property {string}
     * @public
     * @return {void}
     */
    constructor(){

    }
};

/**
 * @description NextDomEnum for css element values.
 * @readonly
 * @enum {string} NextDomEnum#CSSElement
 */
NextDomEnum.CSSElement = {
    /** @description Pixel css property **/
    PIXEL : "px",
    /** @description Poucrentage css property **/
    PERCENT : "%",
    /** @description em css property **/
    EM : "em",
    /** @description space css property **/
    SPACE : " ",
	/** @description Undefined css property **/
	UNDEFINED : ""
};
/**
 * @description NextDomEnum for element types.
 * @readonly
 * @enum {string} NextDomEnum#ElementType
 */
NextDomEnum.ElementType= {
    /** @description Tag type <a></a> **/
    A : "a",
    /** @description Tag type <body></body> **/
    BODY : "body",
    /** @description Tag type <br></br> **/
    BR : "br",
    /** @description Tag type <button></button> **/
    BUTTON : "button",
    /** @description Tag type <div></div> **/
    DIV : "div",
    /** @description Tag type <i></> **/
    I : "i",
    /** @description Tag type <label></label> **/
    INPUTTEXT : "input",
    /** @description Tag type <label></label> **/
    LABEL : "label",
    /** @description Tag type &nbsp; **/
    SPACE : "\u00A0",
    /** @description Tag type <span></span> **/
    SPAN : "span",
    /** @description Tag type <table></table> **/
    TABLE : "table",
    /** @description Tag type <tbody></tbody> **/
    TBODY : "tbody",
    /** @description Tag type <td></td> **/
    TD : "td",
    /** @description Tag type <th></th> **/
    TH : "th",
    /** @description Tag type <thead></thead> **/
    THEAD : "thead",
    /** @description Tag type <h></h> **/
    TITLE : "title",
    /** @description Tag type <tr></tr> **/
    TR : "tr",
    /** @description Undefined tag **/
    UNDEFINED : ""
};

/**
 * @description NextDomEnum for DOM event values.
 * @readonly
 * @enum {string} NextDomEnum#DOMEvent
 */
NextDomEnum.DOMEvent = {
	/** @description Click (down and up) on the element **/
	CLICK : "click",
    /** @description Double-click on the element **/
    DBLCLICK : "dblclick",
    /** @description Occurs when an element is being dragged **/
    DRAG : "drag",
    /** @description Occurs when the user has finished dragging the element **/
    DRAGEND : "dragend",
    /** @description Occurs when the dragged element enters the drop target **/
    DRAGENTER : "dragenter",
    /** @description Occurs when the dragged element leaves the drop target **/
    DRAGLEAVE : "dragleave",
    /** @description Occurs when the dragged element is over the drop target **/
    DRAGOVER : "dragover",
    /** @description Occurs when the user starts to drag an element **/
    DRAGSTART : "dragstart",
    /** @description Occurs when the dragged element is dropped on the drop target **/
    DROP : "drop",
	/** @description Move the cursor over the element **/
	MOUSEOVER : "mouseover",
	/** @description Move the cursor out the element **/
	MOUSEOUT : "mouseout",
	/** @description Press (without release) on left button on the element **/
	MOUSEDOWN : "mousedown",
	/** @description Release the left button on the element **/
	MOUSEUP : "mouseup",
    /** @description Move the cursor on the element **/
    MOUSEMOVE : "mousemove",
    /** @description Move the scroll wheel on the element **/
    MOUSEWHEEL : "mousewheel",
	/** @description Press (without release) a keyboard key on the element **/
	KEYDOWN : "keydown",
	/** @description Release a keyboard key on the element **/
	KEYUP : "keyup",
	/** @description Press (with release) a keyboard key on the element **/
	KEYPRESS : "keypress",
	/** @description « Target » the element **/
	FOCUS : "focus",
	/** @description Cancel the « target » element **/
	BLUR : "blur",
	/** @description Change the element value specific to forms (input,checkbox, etc.) **/
	CHANGE : "change",
	/** @description Tape a carachter in a text field **/
	INPUT : "input",
    /** @description Select a text field content (input,textarea, etc.) **/
    SELECT : "select",
    /** @description Occurs when scroll the page **/
    SCROLL : "scroll",
    /** @description Occurs when scroll start the page **/
    SCROLLSTART : "scrollstart",
    /** @description Occurs when scroll stop the page **/
    SCROLLSTOP : "scrollstop",
    /** @description Undefined DOM event **/
    UNDEFINED : ""
};

