/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*
* @Support <https://www.nextdom.org>
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

let NEAR_DISTANCE = 15;
/**
 * @function designer#addDebugInfo
 * @description Generate a debug html element for all elements
 * @return {void}
 */
function addDebugInfo() {
    document.getElementById('container').addEventListener("mousemove", function(e){
        var x = e.pageX;
        var y = e.pageY;
        e.target.title = "Mouse X="+x+", Y="+y+ " | X=" + document.getElementById('container').offsetWidth
            + " Y=" + document.getElementById('container').offsetHeight;
    });
}

/**
 * @function designer#allowDrop
 * @description Allow drop on this HTMLElement
 * @param {Event} ev Event
 * @return {void}
 */
function allowDrop(ev) {
    ev.preventDefault();
}

/**
 * @function designer#drag
 * @description action on drag event
 * @param {Event} ev DragEvent
 * @return {void}
 */
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

/**
 * @function designer#drop
 * @description action on drop event
 * @param {Event} ev DropEvent
 * @return {void}
 */
function drop(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    var element = document.getElementById(data);
    if(element.classList.contains("component")){
        var cloneElement = document.getElementById(data).cloneNode(true);
        cloneElement.classList.remove("component");
        ev.target.appendChild(cloneElement);
    } else {
        ev.target.appendChild(element);
    }

}

window.addEventListener('load', () => {
    let domContainer = document.getElementById('container');
    let domMask = document.getElementById('lines');
    let domHoriMagnet = domMask.querySelector('.hori');
    let domVertMagnet = domMask.querySelector('.vert');

    addDebugInfo();

    function genBlock(dom) {
        let rootWidth = (dom.innerWidth||dom.clientWidth);
        let rootHeight = (dom.innerHeight||dom.clientHeight);
        let width = Math.max(30, parseInt(Math.random()*rootWidth/2));
        let height = Math.max(30, parseInt(Math.random()*rootHeight/2));
        let block = document.createElement('span');
        block.style.width = (width+'px');
        block.style.height = (height+'px');
        block.style.top = (parseInt(Math.random()*(rootHeight-height))+'px');
        block.style.left = (parseInt(Math.random()*(rootWidth-width))+'px');
        block.style.backgroundColor = ('#'+[1, 2, 3].map(() => ('0'+parseInt(100+Math.random()*155).toString(16)).slice(-2)).join(''));
        block.style.opacity = (0.25+Math.random()*0.75);
        block.classList.add('block');
        return block;
    }

    let globalMagnet = new Magnet().distance(NEAR_DISTANCE);
    let magnets = [];
    Array.prototype.forEach.call(domContainer.querySelectorAll('.group'), function(dom) {
        let magnet = new Magnet().distance(NEAR_DISTANCE);
        let doms = [];
        for (let bInx=(2+parseInt(Math.random()*2)); 0<bInx; bInx--) {
            let block = genBlock(dom);
            let checkbox = document.createElement('input');
            checkbox.setAttribute('type', 'checkbox');
            checkbox.setAttribute('checked', '');
            checkbox.addEventListener('change', function() {
                let block = this.parentNode;
                if (this.checked) {
                    magnet.add(block);
                    doms.push(block);
                    this.parentNode.droppable=false;
                } else {
                    magnet.remove(block);
                    doms.splice(doms.indexOf(block), 1);
                    this.parentNode.droppable=true;
                }
            });
            block.addEventListener('mousedown', function() {
                this.style.zIndex = 10;
            });
            block.addEventListener('click', function() {
                this.style.zIndex = 1;
                dom.appendChild(this);
            });
            block.addEventListener('dblclick', function() {
                let checkbox = this.querySelector('input[type=checkbox]');
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    magnet.add(this);
                    doms.push(this);
                } else {
                    magnet.remove(this);
                    doms.splice(doms.indexOf(this), 1);
                }
            });
            ['attract', 'unattract', 'attracted', 'unattracted'].forEach((type) => {
                block.addEventListener(type, function(e) {
                    console.log(type, e);
                });
            });
            block.appendChild(checkbox);
            dom.appendChild(block);
            doms.push(block);
        }
        magnets.push({
            magnet: magnet,
            doms: doms
        });
    });
    function setAttract() {
        globalMagnet.clear();
        magnets.forEach((obj) => obj.magnet.clear());
        (() => {
            if (this.checked) {
                return [globalMagnet.add(magnets.map((obj) => obj.doms))];
            } else {
                return magnets.map((obj) => obj.magnet.add(obj.doms));
            }
        })().forEach((magnet) => magnet.on('magnetenter', (e) => {
            let result = e.detail;
            console.log('magnetenter', result);
            domHoriMagnet.classList.remove('show');
            domVertMagnet.classList.remove('show');

            let navBarTop = document.getElementsByClassName('navbar navbar-static-top');
            let navBarLeft = document.getElementsByClassName('main-sidebar');
            let tabDesigner = document.getElementById('designer-tab-content');

            let resultX = result.x;
            if(resultX && navBarLeft && tabDesigner && navBarLeft.length >0){
                var diffX = tabDesigner.offsetWidth + tabDesigner.offsetLeft + navBarLeft.item(0).offsetWidth;
                resultX.position = resultX.position - diffX;
                resultX.rect.right = resultX.position;
                resultX.rect.left = resultX.rect.left - diffX;
            }
            let resultY = result.y;
            if(resultY && navBarTop && navBarTop.length >0){
                var diffY = resultY.position - navBarTop.item(0).offsetHeight;
                resultY.position = diffY;
                resultY.rect.bottom = resultY.position;
                resultY.rect.top = resultY.rect.top - diffY;
            }
            if (resultX) {
                domVertMagnet.style.left = (resultX.position+'px');
                domVertMagnet.classList.add('show');
            }
            if (resultY) {
                domHoriMagnet.style.top = (resultY.position+'px');
                domHoriMagnet.classList.add('show');
            }
        }).on('magnetleave', () => {
            console.log('magnetleave');
            domHoriMagnet.classList.remove('show');
            domVertMagnet.classList.remove('show');
        }));
    };
    document.getElementById('attract').addEventListener('change', setAttract);
    setAttract();
});