/**
 * Utility functions to assist in preventing XSS vulnerabilities.
 */
var XSSHelpers = function () {

    /**
     * Builds a RegExp that will match the given HTML tag. Also
     * matches even if the tag contains attributes. Can be in
     * the format "<strong>", or just "strong". 
     * 
     * @param string    tag     The tag to build a regex for
     * 
     * @return RegExp   a Javascript RegExp object
     */
    buildTagRegex = function(tag) {
        return new RegExp('(<\\/?\\s*\\b' + tag + '\\b(.*?)\\s*>)', "g");
    },

    /**
     * Removes the chevrons, front slash, and any white space from an
     * HTML tag. This ensures any regex operations will be working with
     * the same data without concerns of formatting (e.g., "</strong>"
     * will return "strong").
     * 
     * @param string    tag     The tag to clean
     * 
     * @return string   The cleaned tag
     */
    cleanTag = function(tag) {
        // remove chevrons, forward slashes, and whitespace
        return tag.replace(/<\s*\/?|>/g, "").trim();
    },

    /**
     * Checks the given text for the specified tag.
     * 
     * @param text  string  The text to check for tag
     * @param tag   string  The tag to search for    
     * 
     * @return bool If the specified tag was found in the text
     */
    containsTag = function (text, tag) {
        return buildTagRegex(cleanTag(tag)).exec(text) !== null;
    },

    /**
     * Checks the given text for the specified tags.
     * 
     * @param text  string      The text to check for tags
     * @param tags  string[]    An array of tags to search for    
     * @param bool  containsAll If the given text should contain ALL
     * 
     * @return bool If any/all (depends on containsAll) of the 
     *              specified tags were found in the text.
     */
    containsTags = function(text, tags, containsAll) {
    	if(containsAll == undefined) {
    		containsAll = false;
    	}
        for (var i = 0; i < tags.length; i++) {
            var hasTag = containsTag(text, tags[i]);

            if (containsAll) {
                if (!hasTag) { return false; }
            } else {
                if (hasTag) { return true; }
            }
        }

        return false;
    },

    /**
     * Strips the given text of the specified tag.
     * 
     * @param text  string  The text to strip tags from
     * @param tag   string  The tag to strip from the text   
     * 
     * @return string   The stripped text
     */
    stripTag = function (text, tag) {
        return text.replace(buildTagRegex(cleanTag(tag)), "");
    },

    /**
     * Strips the given text of the specified tags.
     * 
     * @param text  string  The text to strip tags from
     * @param tags  string  Array of tags to strip from the text   
     * 
     * @return string   The stripped text
     */
    stripTags = function(text, tags) {
        for (var i = 0; i < tags.length; i++) {
            text = stripTag(text, tags[i]);
        }

        return text;
    };

    return {
        buildTagRegex: buildTagRegex,
        containsTag: containsTag,
        containsTags: containsTags,
        stripTag: stripTag,
        stripTags: stripTags
    };
}();

if (typeof module !== 'undefined') {
    module.exports = XSSHelpers;
}