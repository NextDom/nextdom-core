/* exported DivWithTooltip */
/* global NextDomElement */

/* 
 * DivWithTooltip(_id, _className)
 */
class DivWithTooltip extends Div {
	/**
	 * @constructor DivWithTooltip
	 * @description div with simple tooltip
	 * @extends Div
	 * @param {string} _id Description
	 * @param {string} _className name's class
     * @param {string[]} _content nextdom element to add as child
	 * @param {boolean} _onLeft display tooltip on the left of the nextdomElement Div
	* @return {void}
	*/
	constructor (_id,_className,_content,_onLeft) {
		super(_id,_className);
        let simpleTooltip= new Div(this.id,this.className + " " + "simpletooltip");
        let simpleTooltipText= new Div(this.id,"simpletooltiptext");
        simpleTooltip.getHTMLElement().style.overflow = "inherit !important";
        simpleTooltipText.getHTMLElement().style.zIndex = NextDomEnum.StackOrder.TOOLTIP;
        simpleTooltipText.getHTMLElement().style.fontSize = "small";
        if(_onLeft){
            simpleTooltipText.getHTMLElement().style.right = 1 + NextDomEnum.CSSElement.PERCENT;
		}
        if (Array.isArray(_content)) {
            for (var i = 0; i < _content.length; i++) {
                if (Array.isArray(_content[i])) {
                    for (var j = 0; j < _content[i].length; j++) {
                        if (_content[i][j]) {
                            if (simpleTooltipText.getHTMLElement().childNodes.length > 0) {
                                simpleTooltipText.addChild(new Space(null, null));
                            }
                            if(_content[i][j].lastIndexOf("fa ", 0) === 0){
                                simpleTooltipText.addChild(new IFA("", _content[i][j]));
                            } else {
                                simpleTooltipText.addChild(new TextNode("", "", _content[i][j]));
                            }
                        }
                    }
                } else {
                    if (simpleTooltipText.getHTMLElement().childNodes.length > 0) {
                        simpleTooltipText.addChild(new Br(null, null));
                    }
                    simpleTooltipText.addChild(new TextNode("","",_content[i]));
                }
            }
        } else {
            simpleTooltipText.addChild(new TextNode("","",_content));
        }
        simpleTooltip.addChild(simpleTooltipText);
		this.htmlElement = simpleTooltip.getHTMLElement();
	}
}
