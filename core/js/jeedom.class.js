/**
 * Interface pour nextdom.class.js
 */
function jeedom() {}

jeedom.prototype = Object.create(nextdom.prototype);
jeedom.prototype.constructor = nextdom;
jeedom.prototype._super = nextdom;