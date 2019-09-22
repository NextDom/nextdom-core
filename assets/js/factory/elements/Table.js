/* exported Table */
/* global NextDomElement NextDomEnum */

/* 
 * Table(_id, _className, _nextdomElement)
 */
class Table extends NextDomElement {
	/**
	 * @constructor Table
	 * @description Object to create a table element
	 * @extends NextDomElement
	 * @public
	 * @param {string} _id Description
	 * @param {string} _className name's class
	* @return {void}
	*/
	constructor (_id,_className) {
		super(_id,_className);
		this.htmlElement = this.createHTMLElement(NextDomEnum.ElementType.TABLE);
	}
	/**
	 * @function addRow
	 * @memberOf Table
	 * @instance
	 * @public
	 * @description Defining a method to add a row in a table
	 * @param {number} rowIndex id of the table on which add the row
	 * @param {HTMLElement} _htmlElement to add at the row
	 * @return {void}
	 */
	addRow(rowIndex,_htmlElement) {
		var newRow = this.htmlElement.insertRow(rowIndex);
		var newCell = newRow.insertCell(0);
		newCell.appendChild(_htmlElement);
	}
}