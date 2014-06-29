/*jshint bitwise: true */
/*jslint bitwise: true */
/*global $:false, Spinner:false, ActiveXObject:false*/

/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle
 * management system
 *
 * Webapp Client Controller
 *
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.4-beta
 *
 */

/**
 * Format a timedate string
 * @param dateTimeString
 * @returns {string}
 */
function formatDateTimeString(dateTimeString){
    "use strict";
    var dateParts = dateTimeString.split(' '),
        timeParts = dateParts[1].split(':'),
        dateDateParts = dateParts[0].split('-'),
        date = new Date(
            dateDateParts[0],
            parseInt(
                dateDateParts[1],
                10
            ) - 1,
            dateDateParts[2],
            timeParts[0],
            timeParts[1],
            timeParts[2]
        ),
        timeZoneOffsetInMinutes = date.getTimezoneOffset(),
        timeZoneOffsetSign = (timeZoneOffsetInMinutes>0)?'-':'+',
        timeZoneOffsetHours = Math.floor(Math.abs(timeZoneOffsetInMinutes)/60),
        timeZoneOffsetMinutes = Math.abs(timeZoneOffsetInMinutes%60),
        timeZoneString = 'GMT' +
            timeZoneOffsetSign + (
            (timeZoneOffsetHours<10)?'0':''
            ) +
            timeZoneOffsetHours.toString() +
            timeZoneOffsetMinutes.toString() + (
            (timeZoneOffsetMinutes<10)?'0':''
            ),
        dateOffset = new Date(date.getTime()-timeZoneOffsetInMinutes*60*1000);
    
    return dateOffset.toLocaleString() + ' ' + timeZoneString;
}

/**
 * Gets the options for the spinner
 * @returns {
 *     {opts: ({
 *     }|*), 
 *     target: (HTMLElement|*)}}
 */
function getSpinnerSettings(){
    "use strict";
    var opts = {
        lines: 13, // The number of lines to draw
        length: 20, // The length of each line
        width: 10, // The line thickness
        radius: 30, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: 'auto', // Top position relative to parent in px
        left: 'auto' // Left position relative to parent in px
    },
    target = document.getElementById('container');
    
    return {'opts':opts,'target':target};
}

/**
 * process an ok response
 * @param request
 * @param responseText
 * @param subtitle
 */
function processOk(request,responseText,subtitle){
    "use strict";
    var data;
    try {
        data=JSON.parse(responseText);
        if (
            typeof data === 'object'
            ){
            if (
                data.hasOwnProperty('data') &&
                    typeof data.data === 'object' &&
                    data.data.hasOwnProperty('columns')
                ){
                //generate and dump table on #dataTable div
                dumpTable(data,'dataTable','theMotherOfAllTables');
                //initialize datatables features and styling
                fireDataTables('theMotherOfAllTables');
            }
            if (data.hasOwnProperty('execTime')){
                //set output text on some other fields
                $('#execTime').text(data.execTime);
            } else {
                $('#execTime').text('');
            }
        }
    } catch (err) {
        console.log("Couldn't parse JSON response.");
    }
    if (
        request.collection === 'raffle' &&
            (
                request.action === 'open' ||
                    request.action === 'close' ||
                    request.action === 'delete'
                )
        ){
        requestAndProcessPageJSONData(
            {
                'collection':'raffle',
                'action':'list',
                'creatorid':'me',
                'subtitle': subtitle
            }
        );
    } else if (
        request.collection === 'raffle' &&
            (
                request.action === 'join'
                )
        ){
        requestAndProcessPageJSONData(
            {
                'collection':'raffle',
                'action':'list',
                'raffleid':request.raffleid,
                'subtitle': subtitle
            }
        );
    } else if (
        request.collection === 'raffle' &&
            (
                request.action === 'leave'
                )
        ){
        requestAndProcessPageJSONData(
            {
                'collection':'raffle',
                'action':'list',
                'userid':'me',
                'subtitle': subtitle
            }
        );
    } else {
        $('#subtitle').text(subtitle);
    }
}

/**
 * process a fail response
 * @param request
 * @param responseText
 * @param status
 * @param subtitle
 */
function processFail(request,responseText,status,subtitle){
    "use strict";
    var data, response;
    if(
        status === 404 &&
            request.collection ==='raffle' &&
            (request.action === 'list' || request.action === 'check')
        ){
        $('#subtitle').text(subtitle);
        data = {'data':{'columns':['']}};
    } else {
        window.alert(responseText);
        try {
            response = JSON.parse(responseText);
            data = {'data':{'columns':[],'rows':[[]]}};
            for (var propertyName in response){
                if (response.hasOwnProperty(propertyName)){
                    data.data.columns.push(
                        status === 404?'':propertyName
                    );
                    data.data.rows[0].push(
                        response[propertyName]
                    );
                }
            }
        } catch (err){
            data = {'data':{'columns':['error'],'rows':[[status+': '+responseText]]}};
        }
    }
    dumpTable(data,'dataTable','theMotherOfAllTables');
    fireDataTables('theMotherOfAllTables');
}

/**
 * Send a request to the web server
 * @param request
 */
function requestAndProcessPageJSONData(request){
    "use strict";
    var xmlhttp, //intervalID = window.setInterval(processingDots,1000), 
        spinnerSettings, spinner, params, subtitle;
    if (request.hasOwnProperty('subtitle')){
        subtitle = request.subtitle;
        delete request.subtitle; // this is bad design.
    }
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
            spinner.stop();
            processOk(request,xmlhttp.responseText,subtitle);
        } else if(xmlhttp.readyState === 4){
            spinner.stop();
            processFail(request,xmlhttp.responseText,xmlhttp.status,subtitle);
        }
    };
    params = '?requestUUIDv4='+'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
        /[xy]/g, 
        function(c) {
            var r = Math.random()*16|0, 
                v = c === 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        }
    );
    for (var propertyName in request){
        if (request.hasOwnProperty(propertyName)) {
            params += '&'+propertyName+'='+request[propertyName];
        }
    }
    xmlhttp.open('GET', '../main.php'+params, true);
    xmlhttp.setRequestHeader('Content-type',
        'application/x-www-form-urlencoded');
    xmlhttp.send();
    spinnerSettings = getSpinnerSettings();
    spinner = new Spinner(spinnerSettings.opts).spin(spinnerSettings.target);
}

/**
 * trigger a query on page load
 */
$(document).ready(function(){
//call the AJAX request sender to get data to fill the view
    requestAndProcessPageJSONData(
        {
            'collection':'raffle',
            'action':'list',
            'userid':'me'
        }
    );
});


/**
 * Table handling functions
 */

/**
 * create the table and its header
 * @param columns
 * @param tableId
 * @returns {HTMLElement}
 */
function createTableAndHeader(columns,tableId){
    "use strict";
    var row,th,propertyName,table = document.createElement('table');
    table.setAttribute('cellpadding','0');
    table.setAttribute('cellspacing','0');
    table.setAttribute('border','0');
    table.setAttribute('class',"table table-striped table-bordered");
    table.setAttribute('id',tableId);
    table.createTHead();
    row = document.createElement('tr');
    for (propertyName in columns){
        if (columns.hasOwnProperty(propertyName)) {
            th = document.createElement('th');
            $(th).text(columns[propertyName]);
            row.appendChild(th);
        }
    }
    table.tHead.appendChild(row);
    return table;
}

/**
 * create the table body
 * @param data
 * @returns {HTMLElement}
 */
function createTableBody(data){
    "use strict";
    var tbody, propertyName, row, index, td;
    tbody = document.createElement('tbody');
    for (index in data.data.rows){
        if (data.data.rows.hasOwnProperty(index)) {
            row = document.createElement('tr');
            for (propertyName in data.data.columns){
                if (data.data.columns.hasOwnProperty(propertyName)){
                    td = document.createElement('td');
                    //noinspection JSUnfilteredForInLoop
                    if (typeof data.data.rows[index][propertyName] !== 'undefined') {
                        //noinspection JSUnfilteredForInLoop
                        switch (data.data.columns[propertyName]){
                            case 'raffleid':
                                //noinspection JSUnfilteredForInLoop
                                $(td).html(
                                    "<a " +
                                        "href='#' onclick='document.getElementById(\"raffleId\").value=\"" +
                                        data.data.rows[index][propertyName] +
                                        "\"'>" +
                                        data.data.rows[index][propertyName] +
                                        "</a>"
                                );
                                break;
                            case 'creatorid':
                            case 'participantid':
                            case 'winnerid':
                                //noinspection JSUnfilteredForInLoop
                                $(td).html(
                                    "<a " +
                                        "href='http://plus.google.com/"+data.data.rows[index][propertyName]+"' target='_blank'" +
                                        ">" +
                                        data.data.rows[index][propertyName] +
                                        "</a>"
                                );
                                break;
                            case 'created':
                            case 'joined':
                            case 'raffled':
                                //noinspection JSUnfilteredForInLoop
                                $(td).text(formatDateTimeString(data.data.rows[index][propertyName]));
                                break;
                            default:
                                //noinspection JSUnfilteredForInLoop
                                $(td).text(data.data.rows[index][propertyName]);
                        }
                    }
                    row.appendChild(td);
                }
            }
            tbody.appendChild(row);
        }
    }
    return tbody;
}
 /**
 * Create a table with id tableId using data object from JSON response 
 * and place it into current document's containerId
 * 
 * @param data
 * @param containerId
 * @param tableId
 */
function dumpTable(data,containerId,tableId){
    "use strict";
    var table, outputNode;
    table = createTableAndHeader(data.data.columns,tableId);
    table.appendChild(createTableBody(data));
    outputNode = document.getElementById(containerId);
    while (outputNode.hasChildNodes()) {//overkill
        outputNode.removeChild(outputNode.lastChild);
    }
    outputNode.appendChild(table);
}
/**
 * Turn table in current document's tableId into a datatable.js table
 * 
 * TODO: "records per page" text does not use translation interface
 * 
 * @param tableId
 */
function fireDataTables(tableId) {
    "use strict";
    $('#'+tableId).dataTable({
    "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
    "sPaginationType": "full_numbers",
    "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
        }
    });
}

/*  
 * I've obtained the following code from a blog post from datatables creator, 
 * Allan Jardine, at sprymedia:
 * 
 * http://datatables.net/blog/Twitter_Bootstrap_2 
 */
/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
    "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
    "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
    }
} );


/* Default class modification */

$.extend( $.fn.dataTableExt.oStdClasses, {
    "sWrapper": "dataTables_wrapper form-inline"
} );


/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
    "use strict";
    return {
        "iStart":         oSettings._iDisplayStart,
        "iEnd":           oSettings.fnDisplayEnd(),
        "iLength":        oSettings._iDisplayLength,
        "iTotal":         oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage":          oSettings._iDisplayLength === -1 ?
            0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
        "iTotalPages":    oSettings._iDisplayLength === -1 ?
            0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
    };
};

/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ( $.fn.DataTable.TableTools ) {
    // Set the classes that TableTools uses to something suitable for Bootstrap
    $.extend( true, $.fn.DataTable.TableTools.classes, {
        "container": "DTTT btn-group",
        "buttons": {
            "normal": "btn",
            "disabled": "disabled"
        },
        "collection": {
            "container": "DTTT_dropdown dropdown-menu",
            "buttons": {
                "normal": "",
                "disabled": "disabled"
            }
        },
        "print": {
            "info": "DTTT_print_info modal"
        },
        "select": {
            "row": "active"
        }
    } );

    // Have the collection use a bootstrap compatible dropdown
    $.extend( true, $.fn.DataTable.TableTools.DEFAULTS.oTags, {
        "collection": {
            "container": "ul",
            "button": "li",
            "liner": "a"
        }
    } );
}
