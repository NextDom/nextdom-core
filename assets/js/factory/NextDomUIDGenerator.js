/* exported NextDomUIDGenerator */

/* 
 * NextDomUIDGenerator()
 */
class NextDomUIDGenerator {
	/**
	 * @constructor NextDomUIDGenerator
	 * @description Base for manage UID
	 * @public
	 *  @example <caption>Check duplicate for 10 Million calls.</caption>
	 * var duplicates = NextDomUIDGenerator._checkDuplicates(NextDomUIDGenerator.generateUID, Math.pow(10, 7));
	 * // 10 MILLION 
	 * // now only show the duplicates that have a `duplicateCount` of more than 1 // (meaning they have been duplicated for a second time)
	 * duplicates.filter(function(cur){
	 * 	return cur.duplicateCount > 0 
	 * });
	 * @return {void}
	 */
	constructor() {
	}
}


/**
 * @function NextDomUIDGenerator#_chr4
 * @static
 * @private
 * @description Generate a random serial number
 * @return {string} Random number
 */
NextDomUIDGenerator._chr4 = function() {
	return Math.random().toString(16).slice(-4);
};

/**
 * @function NextDomUIDGenerator#_checkDuplicates
 * @static
 * @private
 * @description Method to check if there is duplicates in the result of
 *              million times calling
 * @see generateUID
 * @param {string} generator Description
 * @param {number} count Description
 * @return {string[]} duplicates
 */
NextDomUIDGenerator._checkDuplicates = function(generator, count) {
	var hash = {};
	var dupe = [];
	for (var idx = 0; idx < count; ++idx) {
		var gen = NextDomUIDGenerator.generateUID(); // generate our unique ID

		// if it already exists, then it has been duplicated
		if (typeof hash[gen] !== 'undefined') {
			dupe.push({
				duplicate: gen,
				indexCreated: hash[gen],
				indexDuplicated: idx,
				duplicateCount: dupe.filter(function (cur) {
					return cur.duplicate === gen
				}).length
			});
		}
		hash[gen] = idx;
	}
	return dupe;
};

/**
 * @function NextDomUIDGenerator#generateUID
 * @static
 * @description Generate an UID - NOTE: This format of 8 chars, followed by 3
 *              groups of 4 chars, followed by 12 chars is known as a UUID and
 *              is defined in RFC4122 and is a standard for generating unique
 *              IDs. This function DOES NOT implement this standard. It simply
 *              outputs a String that looks similar. The standard is found here:
 *              https://www.ietf.org/rfc/rfc4122.txt
 * @return {string} UID
 */
NextDomUIDGenerator.generateUID = function() {
	return NextDomUIDGenerator._chr4() + NextDomUIDGenerator._chr4() + '-' + NextDomUIDGenerator._chr4() + '-'
			+ NextDomUIDGenerator._chr4() + '-' + NextDomUIDGenerator._chr4() + '-'
			+ NextDomUIDGenerator._chr4() + NextDomUIDGenerator._chr4() + NextDomUIDGenerator._chr4();
};

