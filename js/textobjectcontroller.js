/*
 * 
 * WaveCMS 
 *
 * textobjectcontroller.js
 * 
 * jQuery plugin for handling X5 text object in order to obtain proper
 * HTML markup that meets the HTML standards and makes the text object code
 * mor ecompatible with text editors (e.g. redactor)
 *
 * Requires:
 * - WaveCMS common
 * - jQuery (built to version 1.8.2)
 *
 * COPYRIGHT
 * 
 * All use of this code must be according to agreement with the owners
 * of WaveCMS - www.wavecms.eu
 * 
 */

var TextObjectController = new function() {
    
	"use strict";

    var main = this,
        linebreaks = ['<br>', '<br/>', '<br />'],
        blockElements = 'p, div, h1, h2, h3, h4, h5, h6, blockquote, dl, ul, fieldset, form, hr, ol, pre, table';

    /**
     * getStyle
     * 
     * Gets all computed styles for an element
     * 
     * @param element elem
     * @returns array computed style
     */
    this.getStyle = function(elem) {
        if ( (typeof getComputedStyle)=='undefined') {
            // browser is IE 8 (or lower)
            return typeof elem.currentStyle === 'function' ? elem.currentStyle() : [];
        } else {
            // browser is not IE8
            return getComputedStyle(elem);
        }
    }


    /*****
     * The following functions are from http://stackoverflow.com/a/4379864
     */
    
    //this will start from the current element and get all the previous siblings
    this.getPreviousSiblings = function(elem, filter) {
        var sibs = [];
        while (elem = elem.previousSibling) {
            if (!filter || filter(elem)) sibs.push(elem);
        }
        return sibs.reverse();
    };

    //this will start from the current element and get all of the next siblings
    this.getNextSiblings = function(elem, filter) {
        var sibs = [];
        while (elem = elem.nextSibling) {
            if (!filter || filter(elem)) sibs.push(elem);
        }
        return sibs;
    };
    
    /*
     *****
     */
    
    /**
     * MakeParagraphs
     * 
     * Creates paragraphs on the basis of the context of the linebreak
     * 
     * @param elem linebreakElem
     */
    this.MakeParagraphs = function(linebreakElem) {
        
        var $span = $(linebreakElem).parent(),
            $prevBlockSiblings = $span.prevAll(blockElements),
            $nextBlockSiblings = $span.nextAll(blockElements),
            blockSiblings = ($prevBlockSiblings.length || $nextBlockSiblings.length) ? 1 : 0,
            $blockParent = $span.parent(blockElements),
            $paragraphParent = $span.parent('p'),
            $newElement,
            nextSiblings,
            debug = false;
        
        if ($paragraphParent.length && blockSiblings) {
            
            if (debug) console.log('CASE 1');
            // nothing to do here, since it is not proper html to have block elements inside p

        // Parent is block, but not paragraph: wrap prev. and next inline elements in two paragraphs
        } else if ($paragraphParent.length === 0 && $blockParent.length && blockSiblings) {

            if (debug) console.log('CASE 2');

        // Split P on linebreaks position
        } else if ($paragraphParent.length && !blockSiblings) {

            if (debug) console.log('CASE 3');
            
            if ($(linebreakElem).is(':last-child')) {
                
                $newElement = $paragraphParent.clone(false);
                nextSiblings = main.getNextSiblings($span[0]);

                if (debug) $newElement.css('color', 'blue');
                if (debug) $paragraphParent.css('background-color', 'red');

                // Create a copy of the current <p> on the position of
                // the linebreaks, so that one part of the content is still
                // in the original <p>, and the other part is in the new <p>
                if (nextSiblings.length) {
                    $newElement.insertAfter($paragraphParent).html(nextSiblings);
                }
                
            }

        } else if ($blockParent.length === 0 && !blockSiblings) {
            
            if (debug) console.log('CASE 4');
            
        }
    };
    
    /**
     * SplitSpans
     * 
     * Split <span>'s on linebreak position
     * 
     * @param element linebreakElem
     */
    this.SplitSpans = function(linebreakElem) {
        
        var $this = $(linebreakElem).parent(),
            nextSiblings,
            newElement,
            debug = false;
        
        if (debug) console.log('-------------------');
        if (debug) console.log($this[0].innerHTML);

        newElement = $this.clone(false);
        nextSiblings = main.getNextSiblings(linebreakElem);

        if (debug) newElement.css('color', 'blue');
        if (debug) $this.css('background-color', 'red');

        // Create a copy of the current <span> on the position of
        // the linebreaks, so that one part of the content is still
        // in the original <span>, and the other part is in the new <span>
        if (nextSiblings.length) {
            newElement.insertAfter($this).html(nextSiblings);
        }

        // Move the linebreak outside of the <span> if the parent element
        // is also a <span>.
        // Call this function recursively to in order to split the
        // parent <span> that now contains the linebreak
        if ($this.parent('span').length) {

            $(linebreakElem).insertAfter($this);

            // Set earlier inherited font size as inline style to preserve same height
            main.copyStyles($this[0], $(linebreakElem), ['font-size']);

            // Split the parent <span>
            main.SplitSpans(linebreakElem);
        }

    };
    
    /**
     * FindLinebreaksInSpans
     * 
     * Finds all linebreaks in <span>'s, and returns an array of the linebreaks.
     * 
     * @param element elem Element to search for linebreaks in
     * @returns array Linebreaks found
     */
    this.FindLinebreaksInSpans = function(elem) {
        var returnValue = [];
        $(elem).find('span > br').each(function() {
            returnValue.push(this);
        });
        return returnValue;
    };
    
    /**
     * ChangeLinebreakInEmptyParagraph
     * 
     * Basically converts
     * '<p><br></p>' to
     * '<p>&nbsp;</p>'
     * 
     * because when contenteditable is true in IE it makes 2 lines
     * in the first case (one line to write on, and one line representing the
     * linebreak)
     * 
     * @param element elem
     */
    this.ChangeLinebreakInEmptyParagraph = function(elem) {
        $(elem).find('p > br:only-child, p > span:only-child > br:only-child').each(function() {
            $(this).replaceWith('&nbsp;');
        });
    };
    
    /**
     * RemoveLastLinebreak
     * 
     * @param element elem
     */
    this.RemoveLastLinebreak = function(elem) {
        $(elem).find('p > span > br:last-child, p > br:last-child, li > br:last-child, li > span:last-child > br:last-child').remove();
    };
    
    /**
     * CleanUpLinebreaks
     * 
     * Split <span>'s on linebreaks and creates <p>'s accordingly
     * 
     * @param element elem
     */
    this.CleanUpLinebreaks = function(elem) {
        var linebreaksFound = main.FindLinebreaksInSpans(elem);
        
        // Split <span>'s when a linebreak is encountered
        $.each(linebreaksFound, function() {
            main.SplitSpans(this);
        });
        
        // Wrap <span>'s in paragraphs
        $.each(linebreaksFound, function() {
            main.MakeParagraphs(this);
        });
        
        // Empty <span>'s are removed - they are to no use
        $(elem).find('span:empty').remove();
        
        // Convert '<p><br></p>' to '<p>&nbsp;</p>'
        main.ChangeLinebreakInEmptyParagraph(elem);
        
        // <br> in the ending of a e.g. <p> will result in an extra linebreak
        // in IE, thus these linebreaks are removed
        main.RemoveLastLinebreak(elem);
    }
    
    /**
     * RemoveParagraphsInLists
     * 
     * Basically converts
     * '<li><p>$1</p></li>' to
     * '<li>$1</li>'
     * 
     * @param element elem
     * @returns elem
     */
    this.RemoveParagraphsInLists = function(elem) {
        $(elem).find('li > p').replaceWith(function(){
            return $(this).contents();
        });
        return elem;
    }
    
    /**
     * copyStyles
     * 
     * Copies selected computed styles from one element and pastes these
     * as inline styles to another element.
     * 
     * @param element fromElem
     * @param element toElem
     * @param array stylesToCopy
     */
    this.copyStyles = function(fromElem, toElem, stylesToCopy) {

        var copyStyles = typeof stylesToCopy !== 'undefined' ? stylesToCopy : ['font-size', 'font-family', 'font-weight', 'font-style', 'color', 'line-height'],
            fromStyle;

        // Get the computed style for the fromElem
        fromStyle = main.getStyle(fromElem);

        // Add style as inline to toElem
        $.each(copyStyles, function(i, v) {
            if (typeof fromStyle[v] !== 'undefined') {
                $(toElem).css(v, fromStyle[v]);
            }
        });
        
        return toElem;

    };
    
    /**
     * ImagesCleanupAttributes
     * 
     * Cleanup <img> tags
     * 
     * @param element elem
     */
    this.ImagesCleanupAttributes = function(elem) {
        
        // Run through all images
        $(elem).find('img').each(function() {
            
            var attrWidth  = $(this).attr('width'),
                attrHeight = $(this).attr('height');
                
            // remove "width"
            if (typeof attrWidth !== 'undefined' && attrWidth !== false) {
                $(this).css('width', attrWidth + 'px');
                $(this).removeAttr('width');
            }
            
            // remove "height"
            if (typeof attrHeight !== 'undefined' && attrHeight !== false) {
                $(this).removeAttr('height');
            }
            
            // don't set height - redactor controls the size of the image entirely by width
            $(this).css('height', '');
            
        });
    }
    
    
    /**
     * init
     * 
     * Run the above functions in order to clean up the markup in the 
     * text object.
     * 
     * @param element elem Element that needs clean up
     * @returns elem
     */
    this.init = function(elem) {
        
        // Check if cell has allready been cleaned, if it hasn't, do it.
        if ($(elem).data("cleaned_v1")!=true) {
            main.RemoveParagraphsInLists($(elem));
            main.CleanUpLinebreaks($(elem));
            main.ImagesCleanupAttributes($(elem));
            // set a default font size for parentless textnodes
            $(elem).css('font-size', '13px');
            $(elem).data("cleaned_v1",true);
        };
        
        return elem;
    }

};