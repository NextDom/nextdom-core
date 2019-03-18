/* exported NextDomElement */
/* global NextDomUIDGenerator */

/* 
 * NextDomElement(_id,_className,_ElementType)
 */
class NextDomElement {
	/**
	 * @name NextDomElement#id
	 * @instance
	 * @type String
	 */
	id = "";
	/**
	 * @name NextDomElement#className
	 * @type String
	 */
	className = "";
	/**
	 * @name NextDomElement#htmlElement
	 * @type HTMLElement
	 */
	htmlElement = undefined;
	/**
	 * @name NextDomElement#containerElement
	 * @type NextDomElement
	 */
	containerElement = undefined;
	/**
	 * @name NextDomElement#childrenElement
	 * @type NextDomElement[]|NextDomElement
	 */
	childrenElement = [];
	/**
	 * @name Position#position
	 * @type Position
	 */
	position = undefined;
	
	/**
	 * @constructor NextDomElement
	 * @description a base for HTML NextDomElement
	 * @extends Class
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	 * @return {void}
	 */
	constructor (_id, _className) {
		this.id=_id;
		this.className =_className;
	}
	/**
	 * @function NextDomElement#addChild
	 * @instance
     	 * @public
	 * @description Add an NextDomElement or array of NextDomElements as child
	 * @param {NextDomElement|NextDomElement[]} _nextDomElement an NextDomElement or array of NextDomElements
	 * @return {void}
	 */
	addChild (_nextDomElement) {
		if (_nextDomElement) {
			if (Array.isArray(_nextDomElement)) {
				for (var i = 0; i < _nextDomElement.length; i++) {
					if (_nextDomElement[i].htmlElement instanceof Node) {
						this.htmlElement.appendChild(_nextDomElement[i]
							.getHTMLElement());
					}
				}
			} else if (_nextDomElement.htmlElement) {
				this.htmlElement.appendChild(_nextDomElement.htmlElement);
			}
			this.childrenElement = _nextDomElement;

		}
	}
	/**
	 * @function NextDomElement#createTextNode
	 * @instance
     	 * @private
	 * @description Create a new node
	 * @param {string} content The content to include in a text node
	 * @return {Node} a new NextDomElement
	 */
	createTextNode (content) {
		return document.createTextNode(content);
	}
	
	/**
	 * @function NextDomElement#createHTMLElement
	 * @instance
      	 * @public
	 * @description Create a new NextDomElement of type
	 * @param {NextDomEnum.ElementType} ElementType The type of the NextDomElement to generate
	 * @return {HTMLElement} a new NextDomElement
	 */

	createHTMLElement (ElementType) {
		var htmlElement = document.createElement(ElementType.toString());
		if (this.id) {
			htmlElement.id = this.id;
		} else {
			htmlElement.id = UIDUtils.generateUID();
		}
		if (this.className) {
			htmlElement.className = this.className;
		}
		// htmlElement.style.zIndex = NextDomEnum.StackOrder.DEFAULT;
		return htmlElement;
	}
	/**
	 * @function NextDomElement#getHTMLElement
	 * @instance
     	 * @public
	 * @description Defining a method to get DOM NextDomElement
	 * @return {HTMLElement} return a node NextDomElement
	 */
	getHTMLElement () {
		return this.htmlElement;
	}
	/**
	 * @function Element#getContainerElement
	 * @instance
	 * @public
	 * @description Get the main container basic component
	 * @return {NextDomElement} The container basic component
	 */
    getContainerElement  () {
    	return this.containerElement;
    }
    /**
	 * @function NextDomElement#setContainerElement
	 * @instance
	 * @public
	 * @param {NextDomElement} _containerElement parent component
	 * @description Set the main container basic component
	 * @return {void}
	 */
    setContainerElement  (_containerElement) {
    	this.containerElement = _containerElement;
    }
    /**
	 * @function NextDomElement#getChildrenElement
	 * @instance
	 * @public
	 * @description Get children of basic component
	 * @return {NextDomElement[]|NextDomElement} Children of basic component
	 */
    getChildrenElement  () {
    	return this.childrenElement;
    }
    /**
	 * @function NextDomElement#setChildrenElement
	 * @instance
	 * @public
	 * @param {NextDomElement[]|NextDomElement} _childrenElement parent component
	 * @description Set children of basic component
	 * @return {void}
	 */
    setChildrenElement  (_childrenElement) {
    	this.childrenElement = _childrenElement;
    }

	/**
	 * @function NextDomElement#addClass
	 * @instance
     	 * @public
	 * @description Add CSS class on HtmlElement
	 * @param {string} _className CSS class name to add to HtmlElement
	 * @returns {void}
	 */
	addClass (_className){
		if (this.htmlElement.className.indexOf(_className) === -1){
			this.htmlElement.className = this.htmlElement.className + " " + _className;
		}
	}
}

