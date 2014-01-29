/*
 *
 * WaveCMS 
 *
 * gridcontroller.js
 *
 * Requires:
 * - WaveCMS common
 * - Infile CSS (in file.php)
 * - jQuery (built to version 1.8.2)
 * 
 * Note:
 * There is in the following used a mix between applying data as a attribute
 * to the DOM element and through jQuerys '.data' method. The guideline is, 
 * that the data is applied as an attribute when it is important to have the
 * possibility to see the data when inspecting the DOM in the browser.
 * 
 * COPYRIGHT
 * 
 * All use of this code must be according to agreement with the owners
 * of WaveCMS - www.wavecms.eu
 *
 */

var GridController = new function() {
    
    "use strict";
    
    var main = this;

    this.gridRulersShow = false; //($.cookie('grid_state') == 0 || $.cookie('grid_state') === 'undefined') ? false : true;
    
    // Page grid information
    // Is enriched by the identifyGridOnPage-function
    this.pageGrid = {
        cols: 0,
        rows: 0, // only divs with the mark 'row', that is rows that has
                 // cell that spans over several rows are only counted as one
        width: 0
    };
    
    this.gridIdentified = false;

    this.wrapperID = '#imContent';
    
    /**
     * identifyGridOnPage
     * 
     * Identifies the structure of the grid, that is:
     * 
     * 1) Counts number of columns in the grid
     * 2) Determines the width of the grid
     * 3) Marks all grid DOM elements with a data attribute, that explains 
     *    what the element is.
     *    The following values for the attribute "data-cms-grid" are possible:
     * 
     *     - 'row':         the div is a row in the grid
     *     - 'cell':        the div contains the cell content. 
     *                      Also has the id "imCell_*"
     *     - 'emptyCell':   Empty cells (no object supplied in X5)
     *     - 'emptyRow':    Actually a empty cell, but the only cell in the row
     *     - 'cellParentDiv':    a parent div to every cell div. 
     *                           Has cell width and floating attributes.
     *     - 'cellParentColDiv': In rows with a cell that spans e.g. 2 rows
     *                           all the other cells (parents) must be 
     *                           contained in an extra div to create the rowspan.
     *                           (See internal documentation for more detailed explanation)
     */
    this.identifyGridOnPage = function () {

        var cellNormal  = $('div' + this.wrapperID  + ' > div > div > div[class^="imGrid["]');
        var cellRowspan = $('div' + this.wrapperID  + ' > div > div > div > div[class^="imGrid["]');

        if (cellNormal.length==0 && cellRowspan.length==0) {
            //if there are neither normal cells or rowspans we have a 1x? grid.
            //When 1x? grids are made they are missing an div. We reinsert the div where it belongs
            $('div' + this.wrapperID  + ' > div > div[class^="imGrid["]').each(function () {
				var new_parent = $('<div>').insertBefore($(this));
				new_parent.prepend($(this));
			});
            
            //Then we'll update cell and rowspan
            cellNormal  = $('div' + this.wrapperID  + ' > div > div > div[class^="imGrid["]');
            cellRowspan = $('div' + this.wrapperID  + ' > div > div > div > div[class^="imGrid["]');
        };
        
        var excludeDivs = [ "#imFooPad", "#imBtMn", ".imClear", '#imPgMn', '#imBreadcrumb', '#excludeFromRowCount'];
        var rowsColCount = new Array();
        /*Special-case: missing outer div because 1 row,several cols*/
        //if the contents are not wider that (imcontent +10%) - imPgMn, we know that they are multiline
   
        var imContentWidth = $('#imContent').width();
        var imContentChildren  = $('#imContent').children('[style]').not(excludeDivs.join(', '));
        var totalWidth=0;
        /*Checks if all widths are the same if they are not we'll add an div*/
        for (var i = 0; i < imContentChildren.length; i++) {
           totalWidth+= imContentChildren.eq(i).width();
        };
        //we'll also add the width of imPgMn if it's there (if it's not we'll just add null, which is fine)
        totalWidth += $('#imPgMn').width();
        //if the contens are more that 10% wider than imContent they must be on several rows
        var singleRow = (imContentWidth*1.1) > totalWidth;
        //if they are not equal - we'll insert another div
        if ( singleRow) {
          var previousDiv = imContentChildren.eq(0).prev();/*Get the div before the children*/
          var new_parent  = $('<div>').insertAfter(previousDiv);
          $(new_parent).prepend(imContentChildren);

          //Then we'll update cell and rowspan
          cellNormal  = $('div' + this.wrapperID  + ' > div > div > div[class^="imGrid["]');
          cellRowspan = $('div' + this.wrapperID  + ' > div > div > div > div[class^="imGrid["]');
        };

    
        /**
         * Count number of cols in a given row
         */
        var countColsPerRow = function (rowElement, colElement) {
            
            var rowIndex   = $(rowElement).index();
        
            // Only count the col if it hasn't already been (it is already
            // counted if the parent has an data-cms-grid attribute)
            if ( typeof $(colElement).attr('data-cms-grid') == 'undefined' ) {
                
                if ( typeof rowsColCount[rowIndex] == 'undefined' ) {
                    rowsColCount[rowIndex] = 1;
                } else {
                    rowsColCount[rowIndex] = parseInt(rowsColCount[rowIndex] + 1, 10);
                }
                return true;

            } else {
                return false;
            }
            
        };
        
        /**
         * Count number of rows
         */
        var countRows = function () {
            main.pageGrid.rows = (main.pageGrid.rows + 1);
        };
        
        
        /**
         * Get number of columns in the row
         */
        var getColsPerRow = function (rowElement) {
            
            var rowIndex   = $(rowElement).index();

            if ( typeof rowsColCount[rowIndex] == 'undefined' ) {
                return 1;
            } else {
                return rowsColCount[rowIndex];
            }
    
        };
        
        /**
         * Get number of columns in the grid
         */
        var getColsCount = function () {
            //return Math.max.apply(Math, rowsColCount);
            var max = 0,
                maxIndex = -1;
            for(var i=0; i < rowsColCount.length; i++) {
               if ( parseInt( rowsColCount[i], 10) > max ) {
                  max = rowsColCount[i];
                  maxIndex = i;
               }
            }
            
            return max;
        };
    
        // Identify whether a normal cell or a cell that spans over several
        // rows are present
        if (cellNormal.length > 0 || cellRowspan.length > 0) {
            
            // Loop through every row
            $('div' + GridController.wrapperID + ' > div').not( excludeDivs.join(', ') ).each(function() {

                // Mark the row
                $(this).attr('data-cms-grid', 'row');
                
                // Get width of row
                //var rowWidth = $(this).css('width');
                
                // Add one to the row count
                countRows();
                
                var currentRow      = $(this),
                    oneCellRow      = $(this).children('div[class^="imGrid["]'),
                    cellsInRow      = $(this).children('div').children('div[class^="imGrid["]'),
                    cellsInSpanRow  = $(this).children('div').children('div').children('div[class^="imGrid["]'),
                    emptyCellsInRow = $(this).children('div').children('div').not('[class]').filter(function () {
                        // div content is equal to a space (decimal 160)
                        return $(this).text().length == 1 && $(this).text().charCodeAt(0) == 160;
                    }),
                    emptyRow        = $(this).children('div').not('[class]').filter(function () {
                        // div content is equal to a space (decimal 160)
                        return $(this).text().length == 1 && $(this).text().charCodeAt(0) == 160;
                    });
                
                // Check if there are any normal cells present within the row
                if ($(cellsInRow).length > 0) {
                    
                    // Loop through every cell
                    $(cellsInRow).each(function() {
                        
                        // Mark the cell
                        $(this).attr('data-cms-grid', 'cell');
                        
                        //Test
                        //$(this).css('borderColor', 'red').css('borderWidth', '3px').css('borderStyle', 'solid');
                        
                        countColsPerRow( currentRow, $(this).parent() );
                        
                        // Mark the cells container div
                        $(this).parent().attr('data-cms-grid', 'cellParentDiv');
                        
                    });
                    
                }
                
                // Check if there are any cells present in a row with
                // a cell that spans over several rows.
                // These cells are in the following named "cellsInSpanRow"
                // The cells that spans over several rows are called "spanCells".
                // spanCells have the same structure as normal cells (that is 
                // div#imContent > div (row) > div (cell container) > div#imCell )
                // and this case is therefore catched by the code above.
                if ($(cellsInSpanRow).length > 0) {
                    
                    // Loop through every normal cell in the row
                    // spanCells will not be processed in this loop
                    $(cellsInSpanRow).each(function() {
                        
                        // Mark the cell
                        $(this).attr('data-cms-grid', 'cell');
                        
                        //Test
                        //$(this).css('borderColor', 'green').css('borderWidth', '3px').css('borderStyle', 'solid');
                        
                        // Mark the cells container div
                        $(this).parent().attr('data-cms-grid', 'cellParentDiv');
                        
                        countColsPerRow( currentRow, $(this).parent().parent() );
                        
                        // Mark the cells container container div.
                        // This container div holds 2 cells from the same col,
                        // because one cell in this row spans over multiple rows
                        $(this).parent().parent().attr('data-cms-grid', 'cellParentColDiv');
                        
                        
                    });
                    
                }
                
                // Check if there are any one-cell-rows
                if ($(oneCellRow).length > 0) {

                    // Will count as 1 cell in the row
                    countColsPerRow( currentRow, $(oneCellRow) );
                    
                    // Mark the cell
                    $(oneCellRow).attr('data-cms-grid', 'cell');

                    //Test
                    //$(oneCellRow).css('borderColor', 'pink').css('borderWidth', '3px').css('borderStyle', 'solid');
                    
                    // The parent div will not be marked since the parent div is the row

                }

                // Check for presence of empty cells
                if ($(emptyCellsInRow).length > 0) {
                    
                    // Loop through every empty cell in the row
                    $(emptyCellsInRow).each(function() {
                        
                        // Mark the cell
                        $(this).attr('data-cms-grid', 'emptyCell');
                        
                        //Test
                        //$(this).css('borderColor', 'blue').css('borderWidth', '3px').css('borderStyle', 'solid');
                        
                        countColsPerRow( currentRow, $(this).parent() );
                        
                        // Mark the cells container div
                        $(this).parent().attr('data-cms-grid', 'cellParentDiv');
                        
                        
                    });
                    
                }

                // Check for presence of a empty row (no cells)
                if ($(emptyRow).length > 0) {

                    // Mark the cell
                    $(emptyRow).attr('data-cms-grid', 'emptyRow');

                    //Test
                    //$(this).css('borderColor', 'yellow').css('borderWidth', '3px').css('borderStyle', 'solid');
                    
                    // The parent div will not be marked since the parent div is the row

                    // Will count as 1 cell in the row
                    countColsPerRow( currentRow, $(emptyRow) );

                }
                
                // Save the number of columns in the row div element
                currentRow.data('data-cms-grid-numberOfCols', getColsPerRow( currentRow ) )
                
            });
            
            // Get grid width and cols
            this.pageGrid.width = $(cellNormal).length > 0 ? $(cellNormal).parent().parent().css('width') : $(cellRowspan).parent().parent().parent().css('width');
            this.pageGrid.cols  = getColsCount();
        }

        this.gridIdentified = true;

        return true;

    };
    
    /**
     * identifyGridOnPageIfNotIdentifiedAlready
     */
    this.identifyGridOnPageIfNotIdentifiedAlready = function () {
      
        if (this.gridIdentified === false) {
            this.identifyGridOnPage();
        }
        
    };
    
    /**
     * identifyColumnSpanAndNumbers
     * 
     * Identifies the columns numbers and column span
     */
    this.identifyColumnSpanAndNumbers = function () {
        
        var main        = this,
            cellNormal  = $('div' + GridController.wrapperID + ' > div > div > div[class^="imGrid["]'),
            cellRowspan = $('div' + GridController.wrapperID + ' > div > div > div > div[class^="imGrid["]'),
            excludeDivs = [ "#imFooPad", "#imBtMn", ".imClear" ];
    
        // Identify whether a normal cell or a cell that spans over several
        // rows are present
        if (cellNormal.length > 0 || cellRowspan.length > 0) {
            
            // Loop through every row
            $('div' + GridController.wrapperID + ' > div').not( excludeDivs.join(', ') ).each(function() {
            
                var currentRow      = $(this),
                    oneColWidth     = currentRow.css('width').replace('px', '') / main.pageGrid.cols,
                    colspan         = 0,
                    nextColNum      = 1;

                // Loop through every column in the row
                currentRow.children().each(function() {
                    colspan = Math.round( parseInt( $(this).css('width').replace('px', ''), 10 ) / parseInt( oneColWidth, 10 ) );
                    $(this).data('data-cms-grid-colspan', colspan);
                    $(this).data('data-cms-grid-colnum', nextColNum);
                    
                    // Next column number
                    nextColNum = nextColNum + colspan;
                });

            });
            
            return true;
            
        } else {
            return false;
        }
        
    };
    
    /**
     * removeGridAttributes
     * 
     * Removes the 'data-cms-grid' attributes from all grid elements
     */
    this.removeGridAttributes = function () {
      
        $('[data-cms-grid]').removeAttr('data-cms-grid');
        
    };
    
    /**
     * hoverGridCell
     * 
     * Controls what happens when you hover a grid cell while dragging a
     * element that is to be inserted into the grid
     */
    this.hoverGridCell = function (elementHoveringOver) {

        var colnum,
            colspan;

        this.removePlaceholders();
        
        // Adjust page menu height. Add the height (100px) of the placeholder.
        this.adjustPageMenuHeight(100);
        
        colnum  = $(elementHoveringOver).parent().data('data-cms-grid-colnum');
        if (colnum == undefined) {
            colnum  = $(elementHoveringOver).parent().parent().data('data-cms-grid-colnum');
        }
        
        colspan = $(elementHoveringOver).parent().data('data-cms-grid-colspan');
        if (colspan == undefined) {
            colspan  = $(elementHoveringOver).parent().parent().data('data-cms-grid-colspan');
        }
        
        $(elementHoveringOver).closest('div[data-cms-grid="row"]').after( 
                                                                        this.createGridRowAndCellPlaceholderMarkup( colnum, colspan, elementHoveringOver )
                                                                        );

    };
    
    /**
     * hoverGridRow
     * 
     * Controls what happens when you hover a grid row while dragging a
     * element that is to be inserted into the grid
     */
    this.hoverGridRow = function (elementHoveringOver) {

        this.removePlaceholders();
        
        // Adjust page menu height. Add the height (100px) of the placeholder.
        this.adjustPageMenuHeight(100);
        
        $(elementHoveringOver).after( this.createGridRowPlaceholderMarkup(elementHoveringOver) );

    };
    
    /**
     * createGridRowAndCellPlaceholderMarkup
     * 
     * @param int colNumber the number of the columns that is to be inserted
     * @param int colSpan how many columns the new column should span over
     * @param int useColWidth
     * 
     * @returns string Row placeholder HTML
     */
    this.createGridRowAndCellPlaceholderMarkup = function ( colNumber, colSpan, elementHoveringOver ) {

        /*
         * note: row span is not implemented at the moment in this function
         */

        var colWidth,
            useColWidth,
            currentColspan,
            columnsDom = $(),
            cellDom,
            cssToCarry;
        var rowWidths = this.individualColWidths();
        //copy the relevant css from the elements to the new 'copy'
        var parentMargPad = this.copyMargPad(elementHoveringOver);
        var thisMargPad = this.copyMargPad($(elementHoveringOver).children('[id^=imCellStyle_]')[0]);

        //array of colors to test with
	     /* var testcolors = ["","red","green","blue","purple","yellow","magenta","black","teal"];*/
        
        useColWidth = parseInt($(elementHoveringOver).css('width').replace('px', ''), 10 );

        // Create columns
        for (var i = 1; i <= this.pageGrid.cols; i++) {

            // Cell parent div
            if (i == colNumber) {

                currentColspan = colSpan;
                
                // Skip the columns that the current column spans
                i = i + (colSpan-1);
                
                // Create new cell in column
                cellDom = this.createRowCell().css(parentMargPad);
                var cellStyle = cellDom.find('div[id^="imCellStyle_"]')
                              .addClass('gridPlaceholderCellHoveringCell')
                              .css(thisMargPad);

                // Define width of column
                colWidth = Math.ceil( useColWidth );

            } else {
                currentColspan = 1;


                // Create new cell to column
                cellDom = this.createRowEmptyCell();

                colWidth = rowWidths[i-1];
                
            }
            // Add column to the object that saves all the created columns
            columnsDom.after(
                    $('<div />').css({ 
                                    /*'background-color':testcolors[i],*/
                                    float: 'left',
                                    width: (colWidth - 2) + 'px'
                                    })
                                .attr('data-cms-grid', 'cellParentDiv')
                                .data('data-cms-grid-colspan', currentColspan)
                                .data('data-cms-grid-colnum', i)
                                .data('cms-grid-pixels-subtracted', 2)
                                .html(
                                    cellDom
                                )
                        );
            
        }

        return this.createGridRowMarkup().addClass('gridPlaceholderRowHoveringCell')
                                         .attr('data-cms-grid', 'row')
                                         .html(
                                              columnsDom
                                              );
    };


    /**
    *   getStyle
    *     
    *   Retrives current style for an element 
    */
    this.getStyle= function(elem){
      if ( (typeof getComputedStyle)=='undefined'){
       //browser is IE 8 (or lower)
       return elem.currentStyle();
      }else{
        //browser is not IE8
        return getComputedStyle(elem);
      }
    }

    /**
    *   individualColWidths
    *
    *    Returns the individual width of each column in the grid
    *
    *
    *
    */
    this.individualColWidths = function(){
    //the maximal number of rows we can encounter
    var noOfCols = this.pageGrid.cols;
    //get all the rows
    var allRows = $('[data-cms-grid="row"]');
    var rowWidths = Array(noOfCols);
    //Iterate over all the rows to find one where all the columns are placed in the document
        for (var i=0;i<allRows.length;i++){
            var children =  allRows[i].children;
           if( children.length == noOfCols){
                //when we find a row with all the columns we get the widths of the columns
                for(var j =0;j<children.length;j++){
                   rowWidths[j]=parseInt( children[j].offsetWidth , 10 );
                }
                break;
           }
        }
        return rowWidths;
    }

    /**
    *   copyMargPad
    *   copies margin and padding from an element
    *
    *   returns an object, with the properties copied. Can be used directly in .css()
    */

    this.copyMargPad = function(elementFrom){
      var style =this.getStyle(elementFrom);
      var cssToCarry = {/*Margin*/
                        margin        :style.margin,
                        marginBottom  :style.marginBottom,
                        marginLeft    :style.marginLeft,
                        marginRight   :style.marginRight,
                        marginTop     :style.marginTop,
                        /*padding*/ 
                        padding       :style.padding,
                        paddingBottom :style.paddingBottom,
                        paddingLeft   :style.paddingLeft,
                        paddingRight  :style.paddingRight,
                        paddingTop    :style.paddingTop
                      };
      var paddingBottom = cssToCarry.paddingBottom.replace("px",'');
      var bottomPaddingThreshold = 15;/*If bottom padding is more than this value we'll ignore the actual value and set it to zero*/
      if (paddingBottom>15) {
        cssToCarry.paddingBottom='0px';
      };
      return cssToCarry;
    }
    
    /**
     * getColWidth
     * 
     * Returns the width of a column
     * 
     * @param object rowObject
     * @param int colNumber
     * @returns int column width
     */
    /*this.getColWidth = function ( rowObject, colNumber ) {
        
        var parentColDivs   = $(rowObject).children('[data-cms-grid="cellParentColDiv"]'),
            parentDivs      = $(rowObject).children('[data-cms-grid="cellParentDiv"]'),
            columns         = parentColDivs || parentDivs, // use the one of the two that is not undefined
            firstTime       = true;
    
        columns.each(function () {
            
            // In case of uneven number when row width is divided
            // make sure that only one columns width is rounded up (ceil)
            // and the rest is rounded down (floor)
            if (firstTime === true) {
                colWidth = Math.ceil( colWidth );
            } else {
                colWidth = Math.floor( colWidth );
            }
            
            firstTime = false;

        });
            
    };*/
    
    /**
     * createGridRowPlaceholderMarkup
     * 
     * @returns string Row placeholder HTML
     */
    this.createGridRowPlaceholderMarkup = function (elementHoveringOver) {

        return this.createGridRowMarkup().addClass('gridPlaceholderRowHoveringRow')
                                         .attr('data-cms-grid', 'row')
                                         .html(
                                              this.createRowCell(elementHoveringOver)
                                              );
    };
    
    /**
     * removePlaceholders
     * 
     * Remove placeholders from document
     */
    this.removePlaceholders = function () {
      
        $('[class^="gridPlaceholder"]').remove();
        this.adjustPageMenuHeight();
        
    };
    
    /**
     * convertPlaceholderToNormal
     * 
     * Removes styles from placeholders, thereby making them normal rows/cells
     */
    this.convertPlaceholderToNormal = function () {
        
        $('[class^="gridPlaceholder"]').removeClass('gridPlaceholderRowHoveringRow gridPlaceholderRowHoveringCell gridPlaceholderCellHoveringCell');
        
        //
        // ROW
        //
        
        // Add 2 pixels to the row width. These 2 pixels were subtracted from the
        // placeholder width because of the 2 pixel border
        var placeholderRow = $('div[data-cms-grid="row"]').filter(function() {
            return $(this).data('cms-grid-pixels-subtracted');
        });
        
        if ( typeof placeholderRow != 'undefined') {
            // Add subtracted pixels
            var newWidth = ( parseInt( placeholderRow.css('width').replace('px', ''), 10) + parseInt( placeholderRow.data('cms-grid-pixels-subtracted'), 10 ) ) + 'px';
            placeholderRow.css('width', newWidth );
            
            // Remove data
            $.removeData( placeholderRow[0], 'cms-grid-pixels-subtracted');
        }
        
        
        //
        // COLUMN / CELL
        //
        
        // Add 2 pixels to the row width. These 2 pixels were subtracted from the
        // placeholder width because of the 2 pixel border
        var placeholderCell = $('div[data-cms-grid="cellParentDiv"]').filter(function() {
            return $(this).data('cms-grid-pixels-subtracted');
        });
        
        if ( typeof placeholderCell != 'undefined') {

            placeholderCell.each(function () {

                // Add subtracted pixels
                var newWidth = ( parseInt( $(this).css('width').replace('px', ''), 10) + parseInt( $(this).data('cms-grid-pixels-subtracted'), 10 ) ) + 'px';
                $(this).css('width', newWidth );

                // Remove data
                $.removeData( $(this)[0], 'cms-grid-pixels-subtracted');

            });
        }
        
        
    };
    
    /**
     * getPlaceholderElement
     * 
     * @returns object 
     */
    this.getPlaceholderElement = function () {
      
        return $('[data-cms-grid="row"][class^="gridPlaceholder"]');
        
    };
    
    
    /**
     * getPlaceholderCellElement
     * 
     * @returns object 
     */
    this.getPlaceholderCellElement = function () {
      
        return $('[class="gridPlaceholderCellHoveringCell"]');
        
    };
    
    
    /**
     * isPlaceholderVisible
     * 
     * @returns boolean
     */
    this.isPlaceholderVisible = function () {
      
        return ( this.getPlaceholderElement().length > 0 );
        
    };
    
    /**
     * getInnerPlaceholderWidth
     * 
     * Returns the width of the smallest placeholder (cell or row placeholder)
     * 
     * @returns integer width of placeholder
     */
    this.getInnerPlaceholderWidth = function () {
        
        var cellPlaceholder = this.getPlaceholderCellElement(),
            rowPlaceholder  = this.getPlaceholderElement();
        
        if ( cellPlaceholder.length > 0 ) {
            return cellPlaceholder.width();
        } else if ( rowPlaceholder.length > 0 ) {
            return rowPlaceholder.width();
        } else {
            return 0;
        }
        
    };
    
    
    /**
     * insertFullRowCell
     * 
     * Insert full row with one cell into grid in replacement of the placeholder
     */
    this.insertFullRowCell = function () {
        this.getPlaceholderElement().replaceWith(
                                                this.createGridRowMarkup().html(
                                                                                this.createRowCell()
                                                                                )
                                                );
    };
    
    /**
     * getNewCell
     * 
     * Finds a newly created cell in the grid
     * 
     * @returns object the new cell as a jQuery object
     */
    this.getNewCell = function () {
        
        var newCell = $('div[id^="imCellStyle_"]').filter(function() {
            return $(this).data('cms-grid-newrow') && $(this).data('cms-grid-newrow') == 1;
        });
        
        return newCell;
    };
    
    /**
     * markNewCellAsOld
     * 
     * Mark a newly created cell as old
     */
    this.markNewCellAsOld = function () {
        $.removeData( this.getNewCell()[0], 'cms-grid-newrow');
        return true;
        
    };
    
    /**
     * createGridRowMarkup
     * 
     * Creates the basic HTML for the grid row
     * 
     * @returns string Row HTML
     */
    this.createGridRowMarkup = function () {

        var rowWidth = (this.pageGrid.width.replace('px', '') - 2) + 'px';

        return $('<div />').css({
                                width: rowWidth,
                                float: 'left'
                            })
                           .attr('data-cms-grid', 'row')
                           .data('cms-grid-pixels-subtracted', 2);

    };
    
    /**
     * createRowCell
     * 
     * Create a cell
     * 
     * @returns string 
     */
    this.createRowCell = function (elementHoveringOver) {
      var newCssCell={}
         ,newCssCellStyle={};
        if(typeof(elementHoveringOver) !='undefined'){
          //the parameter is set so we'll want to set CSS on the rows children
          var allCells = $(elementHoveringOver).find('[id^=imCell_]');
          var allCellstyles = $(elementHoveringOver).find('[id^=imCellStyle_]');

          //get the leftmost cell and cellStyle style
          var leftCellStyle = this.getStyle(allCells[0]);
          var leftCellStyleStyle = this.getStyle(allCellstyles[0]);
          
          //Remove padding, if leftCell's padding 
          var newPadding = leftCellStyleStyle.padding;
          var paddingTolerance = 15; // Maximum padding value allowed to copy
          newPadding = newPadding.split(" ");
          for (var i = 0; i < newPadding.length; i++) {
            if(parseInt(newPadding[i].replace('px','')) > paddingTolerance){
                newPadding[i] = '0px';
            }
          }
          newPadding = newPadding.join(' '); // Merge padding array into padding shorthand format (again)

          //copy the left css to the new css styles
          //padding and margin top and bottom is taken from the leftmost cell
          newCssCell = {/*Margin*/
                        margin        :leftCellStyle.margin,
                        marginLeft    :leftCellStyle.marginLeft,
                        /*padding*/ 
                        padding       :newPadding,
                        paddingRight  :leftCellStyle.paddingLeft
                      };
          newCssCellStyle ={/*Margin*/
                        margin        :leftCellStyleStyle.margin,
                        marginLeft    :leftCellStyleStyle.marginLeft,
                        /*padding*/ 
                        padding       :newPadding,
                        paddingRight  :leftCellStyleStyle.paddingLeft
                      };
           //get the rightmost child style
           var rightCellStyle = this.getStyle(allCells[allCells.length-1]);
           var rightCellStyleStyle = this.getStyle(allCellstyles[allCells.length-1]);
           //for cell
           newCssCell.marginRight = rightCellStyle.marginRight;
           newCssCell.paddingRight=rightCellStyle.paddingRight;
           //for 
           newCssCellStyle.marginRight=rightCellStyleStyle.marginRight;
           newCssCellStyle.paddingRight=rightCellStyleStyle.paddingRight;

        }

        return $('<div />').attr('id', 'imCell_X')
                           .attr('data-cms-grid', 'cell')
                           .css({ padding: '3px' })
                           .addClass('imGrid[0, 0]')
                           .css(newCssCell) /*if newCssCell is set we'll replace the CSS */
                           .html(
                                $('<div />').attr('id', 'imCellStyleGraphics_X')
                                .after
                                (
                                    $('<div />').attr('id', 'imCellStyle_X')
                                                .data('cms-grid-newrow', 1)
                                                .css(newCssCellStyle)
                                )
                                );
    };
    
    /**
     * createRowEmptyCell
     * 
     * Create a empty cell in the row
     * 
     * @returns string 
     */
    this.createRowEmptyCell = function () {

        return $('<div />').attr('id', 'imCell_X')
                           .attr('data-cms-grid', 'emptyCell')
                           .addClass('imGrid[0, 0]')
                           .html(
                                $('<div />').attr('id', 'imCellStyleGraphics_X')
                                .after
                                (
                                    $('<div />').attr('id', 'imCellStyle_X')
                                                .html('&nbsp;')
                                )
                                );
    };
    
    /**
     * createTextObject
     * 
     * Creates X5 text object DOM structure
     * 
     * @returns object jQuery DOM element
     */
    this.createTextObject = function (placeholder) {
        return $('<div />').attr('id', 'imTextObject_X_tabX')
                           .css({ 'min-height': '50px', 'text-align': 'left' })
                           .html
                           (
                                $('<span />').html(placeholder)
                           );
    };
    
    /**
     * adjustPageMenuHeight
     * 
     * Pagemenu (div#imPgMn) has a min-height that is calculated from
     * the height of the grid (the sum of all rows)
     * When editing the page the pagemenu height must be adjusted accordingly.
     * 
     * @params extraHeight int add extra height to the pagemenu
     */
    this.adjustPageMenuHeight = function (extraHeight) {
        
        var pagemenu = $('div#imPgMn');
        if (pagemenu.length > 0) {

            // Find height of grid area
            var height = 0;
            $('[data-cms-grid="row"]').each(function () {
                height = height + $(this).height();
            });
            
            // Extra height
            if ( typeof extraHeight != 'undefined')
                height = height + extraHeight;

            // Edit pagemenu height
            pagemenu.css('min-height', height + 'px');
            
            return true;
            
        } else {
            return false;
        }
        
    };
    
    /**
     * cleanGridData
     * 
     * Cleans all data associated with the identification of the grid
     */
    this.cleanGridData = function () {
    
        // Remove data attributes
        $('[data-cms-grid]').removeAttr('data-cms-grid');
        
        // Remove data
        $('*').removeData('data-cms-grid-numberOfCols data-cms-grid-colspan data-cms-grid-colnum cms-grid-newrow');
    
    };
    
    /**
     * isMouseOverImContent
     * 
     * @returns boolean
     */
    this.isMouseOverImContent = function (e) {
        
        //var WOWSliderPosition = { x: $('a.WOWSliderDrag').offset().left, y: $('a.WOWSliderDrag').offset().top};
        var CursorPosition = { x: e.pageX, y: e.pageY };
        var ImContentPosition = { x: $('div' + GridController.wrapperID).offset().left, y: $('div' + GridController.wrapperID).offset().top, width: $('div' + GridController.wrapperID).width(), height: $('div' + GridController.wrapperID).height() };

        // Cursor is over div#imContent area
        if (CursorPosition.x > ImContentPosition.x && 
            CursorPosition.x < (ImContentPosition.x + ImContentPosition.width) &&
            CursorPosition.y > ImContentPosition.y &&
            CursorPosition.y < (ImContentPosition.y + ImContentPosition.height)) {

            return true;

        } else {
            
            return false;
            
        }
        
    };

    this.hasPageMenu = function() {
        return !!$('#imPgMn').length;
    }

    this.hasImContentWrapper = function() {
        return !!$('#imContentSortableWrapper').length;
    }

    this.setImContentID = function() {
        if (this.hasPageMenu && $('#imContentSortableWrapper').length)
        {
            this.wrapperID = '#imContentSortableWrapper';
        }
    }


    this.structureImContent = function() {

        $('div[data-cms-grid="row"]').filter(function() {
            return !$(this).prev().is('div[data-cms-grid="row"]');
        }).map(function() {
            return $(this).nextUntil(':not(div[data-cms-grid="row"])').andSelf().find('script').attr('type', 'txt/xml');
        });

        $('div[data-cms-grid="row"]').filter(function() {
            return !$(this).prev().is('div[data-cms-grid="row"]');
        }).map(function() {
            return $(this).nextUntil(':not(div[data-cms-grid="row"])').andSelf();
        }).wrap('<div id="imContentSortableWrapper" style="" />');

        $('#imContentSortableWrapper').append($('<div />').attr('id', 'excludeFromRowCount').css({ clear: 'both' })).find('script').attr('type', 'text/javascript');

        // Restructure grid
        GridController.setImContentID();
    }

    this.s4 = function() {
        return  Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
    }

    this.getUUID = function() {
        return GridController.s4() + GridController.s4() + '-' + GridController.s4() + '-' + GridController.s4() + '-' + GridController.s4() + '-' + GridController.s4() + GridController.s4() + GridController.s4();
    }

    this.generateGridRulers = function()
    {

        GridController.setEmptyCellsHeight();
        adjustPageHeight();

        $('div[data-cms-grid="row"] > div[data-cms-grid="cellParentDiv"], div[data-cms-grid="row"] > div[data-cms-grid="cellParentColDiv"]').each(function() {
            
            var coords = { offset: $(this).offset(), dimensions: [$(this).width(), $(this).height()] },
                UUID = GridController.getUUID();

            if (!$('div[data-border-id=' + UUID + ']').length)
            {
                $('html').append([
                    $('<div/>').addClass('grid-border top')
                               .attr('data-border-id', UUID)
                               .css({
                                    top:    coords.offset.top,
                                    left:   coords.offset.left,
                                    width:  coords.dimensions[0],
                                }).show(),

                    $('<div/>').addClass('grid-border right')
                               .attr('data-border-id', UUID)
                               .css({
                                    top:    coords.offset.top,
                                    left:   (coords.offset.left + coords.dimensions[0]),
                                    height: coords.dimensions[1]
                                }).show(),

                    $('<div/>').addClass('grid-border bottom')
                               .attr('data-border-id', UUID)
                               .css({
                                    top:    (coords.offset.top + coords.dimensions[1]),
                                    left:   coords.offset.left,
                                    width:  (coords.dimensions[0] + 3),
                                }).show(),

                    $('<div/>').addClass('grid-border left')
                               .attr('data-border-id', UUID)
                               .css({
                                    top:    coords.offset.top,
                                    left:   coords.offset.left,
                                    height: coords.dimensions[1]
                                }).show()
                ]);
            }

            return true;
        });
    }

    this.removeGrid = function() 
    {
        $('div.grid-border').remove();
        GridController.removeEmptyCellsHeight();
        adjustPageHeight();
    }

    this.setEmptyCellsHeight = function() 
    {
        $('div[data-cms-grid="emptyCell"]').css({ 'min-height': '200px' });
    }

    this.removeEmptyCellsHeight = function()
    {
        $('div[data-cms-grid="emptyCell"]').css({ 'min-height': 0 });
    }

    /**
     * init
     * 
     * Run functions initially
     */
    this.init = function () {

        // Convert all scripts to "xml", so it won't be removed by the .html() function
        $('body').find('script:not([type="text/xml"])').attr('type', 'text/xml');
        
        // Set ImContent-id
        GridController.setImContentID();

        // Identify X5 grid
        GridController.identifyGridOnPage();

        // Identify column numbers and spans
        GridController.identifyColumnSpanAndNumbers();

        // If the page has a menu, restructure it's content.
        // Too this is only needed, if it has not yet been structured and therefor contains wrapper "#imContentSortableWrapper"
        if (this.hasPageMenu() && !this.hasImContentWrapper())
        {
            GridController.structureImContent();
        }
        
        // If the user has enabled grid rulers, we show them.
        if (this.gridRulersShow)
        {
            this.generateGridRulers();
        }
        
        // Convert all scripts back from "xml" to javascript
        $('body').find('script[type="text/xml"]').attr('type', '');
    };
    
};


/***********************
 * 
 * jQuery event handlers
 * 
 */
$(document).ready(function() {

    GridController.init();

    // Mouseover row
    $(document).on('mouseover', 'div[data-cms-grid="row"]', function () {

        // Make sure that we are dragging an element, and are not just hovering
        // with the mouse
        if (isDraggingSideMenuElement) {

            // Ctrl is not pressed
            if ( ! isPressed) {
                GridController.hoverGridRow( this );
            }
            
        }
        
    });
    
    // Mouseover cell
    $(document).on('mouseover', 'div[data-cms-grid="cell"], div[data-cms-grid="emptyCell"]', function () {
        
        // Make sure that we are dragging an element, and are not just hovering
        // with the mouse
        if (typeof isDraggingSideMenuElement !== 'undefined' && isDraggingSideMenuElement) {

            // SHIFT is pressed
            if (isPressed) {
                GridController.hoverGridCell( this );
            }
            
        }

    });
    
    // Mouseout of div#imContent area
    $(document).on('mouseout', 'div[data-cms-grid="row"]', function (e) {

        // Make sure that we are dragging an element, and are not just hovering
        // with the mouse
        if (typeof isDraggingSideMenuElement !== 'undefined' && isDraggingSideMenuElement) {
            
            if ( ! GridController.isMouseOverImContent(e) ) {
                GridController.removePlaceholders();
            }
            
        }

    });
    
    
});
