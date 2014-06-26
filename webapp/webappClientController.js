/*global $:false, jQuery:false, Spinner:false*/

/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle
 * management system
 *
 * Webapp Client Controller
 *
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.1-beta
 *
 */

/*
 * Processing dots animation
 */
/* (Replaced by spinner animation)
function processingDots(){
//dots animation shown while the server processes the request
    "use strict";
    var dots = document.getElementById('dots');
    if ($(dots).text().length < 3) {
        $(dots).text($(dots).text()+'.');
    } else {
        $(dots).text('.');
    }
}
*/

/**
 * Send a request to the web server
 */
function requestAndProcessPageJSONData(request){
    "use strict";
    var xmlhttp, //intervalID = window.setInterval(processingDots,1000), 
        opts, target, spinner, propertyName, data, params, response, subtitle;
    if (request.hasOwnProperty('subtitle')){
        subtitle = request['subtitle'];
        delete request['subtitle']; // this is bad design.
    }
    //$("#postAjaxContent").hide();
    //$("#preAjaxContent").show();
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
            //window.clearInterval(intervalID);
            spinner.stop();
            if (xmlhttp.responseText) {
                //console.log(xmlhttp.responseText);
                data=JSON.parse(xmlhttp.responseText);
                //generate and dump table on #dataTable div
                dumpTable(data,'dataTable','theMotherOfAllTables');
                //initialize datatables features and styling
                fireDataTables('theMotherOfAllTables');
                //set output text on some other fields
                $('#execTime').text(data['execTime']);
                //set the url to reflect the parameters of the JSON acquired
                //path = /^([\w\W]*\/)\d+\/\d+\/\d+(\/[\w\W]*)$/.exec(window.location.pathname);
                //path=(path===null)?window.location.pathname:path[1];
                //history.replaceState(stateObj, "", url);
                //$('#subtitle').text(data['subtitle']);
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
            }
            $('#subtitle').text(subtitle);
            //$("#preAjaxContent").hide();
            //$("#postAjaxContent").show();
        } else if(xmlhttp.readyState === 4){
            spinner.stop();
            if(xmlhttp.status === 404 && request.collection ==='raffle' && (request.action === 'list' || request.action === 'check')){
                data = {'data':{'columns':['']}};
            } else {
                alert(xmlhttp.responseText);
                try {
                    response = JSON.parse(xmlhttp.responseText);
                    data = {'data':{'columns':[],'rows':[[]]}};
                    for (propertyName in response){
                        if (response.hasOwnProperty(propertyName)){
                            data.data.columns.push(
                                xmlhttp.status === 404?'':propertyName
                            );
                            data.data.rows[0].push(
                                response[propertyName]
                            );
                        }
                    }
                } catch (err){
                    data = {'data':{'columns':['error'],'rows':[[xmlhttp.status+': '+xmlhttp.responseText]]}}
                }
            }
            dumpTable(data,'dataTable','theMotherOfAllTables');
            fireDataTables('theMotherOfAllTables');
            //$("#preAjaxContent").hide();
            //$("#postAjaxContent").show();
        }
    };
    params = '?requestUUIDv4='+'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
        /[xy]/g, 
        function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        }
    );
    for (propertyName in request){
        if (request.hasOwnProperty(propertyName)) {
            params += '&'+propertyName+'='+request[propertyName];
        }
    }
    xmlhttp.open('GET', '../main.php'+params, true);
    xmlhttp.setRequestHeader('Content-type',
        'application/x-www-form-urlencoded');
    xmlhttp.send();
    opts = {
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
    };
    target = document.getElementById('container');
    spinner = new Spinner(opts).spin(target);
}
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
 * Creates a table with id tableId using data object from JSON response 
 * and places it into current document's containerId
 * 
 * TODO: data is obscure, use meaningful names and clearly structured params 
 * instead
 * 
 * @param data
 * @param containerId
 * @param tableId
 */
function dumpTable(data,containerId,tableId){
    "use strict";
    var table, tbody, propertyName, row, th, index, td, outputNode;
    table = document.createElement('table');
    table.setAttribute('cellpadding','0');
    table.setAttribute('cellspacing','0');
    table.setAttribute('border','0');
    table.setAttribute('class',"table table-striped table-bordered");
    table.setAttribute('id',tableId);
    table.createTHead();
    row = document.createElement('tr');
    for (propertyName in data['data']['columns']){
        if (data['data']['columns'].hasOwnProperty(propertyName)) {
            th = document.createElement('th');
            $(th).text(data['data'].columns[propertyName]);
            row.appendChild(th);
        }
    }
    table.tHead.appendChild(row);
    tbody = document.createElement('tbody');
    for (index in data['data'].rows){
        if (data['data'].rows.hasOwnProperty(index)) {
            row = document.createElement('tr');
            for (propertyName in data['data'].columns){
                td = document.createElement('td');
                //noinspection JSUnfilteredForInLoop
                if (typeof data['data'].rows[index][propertyName] !== 'undefined') {
                    //noinspection JSUnfilteredForInLoop
                    if(data['data'].columns[propertyName] === 'raffleid'){
                        //noinspection JSUnfilteredForInLoop
                        $(td).html(
                            "<a " +
                                "href='#' onclick='document.getElementById(\"raffleId\").value=\""+data['data'].rows[index][propertyName]+"\"'>" +
                                data['data'].rows[index][propertyName] + 
                            "</a>"
                        );
                    } else //noinspection JSUnfilteredForInLoop
                    if (
                        //noinspection JSUnfilteredForInLoop
                        data['data'].columns[propertyName] === 'creatorid' ||
                        data['data'].columns[propertyName] === 'participantid' ||
                        data['data'].columns[propertyName] === 'winnerid'
                    ){
                        //noinspection JSUnfilteredForInLoop
                        $(td).html(
                            "<a " +
                                "href='http://plus.google.com/"+data['data'].rows[index][propertyName]+"' target='_blank'" +
                            ">" +
                                data['data'].rows[index][propertyName] +
                            "</a>"
                        );
                    } else //noinspection JSUnfilteredForInLoop
                        if(
                        data['data'].columns[propertyName] === 'created' ||
                        //noinspection JSUnfilteredForInLoop
                        data['data'].columns[propertyName] === 'joined' ||
                        //noinspection JSUnfilteredForInLoop
                        data['data'].columns[propertyName] === 'raffled'
                    ) {
                        //noinspection JSUnfilteredForInLoop
                        var dateString = data['data'].rows[index][propertyName],
                            dateParts = dateString.split(' '),
                            timeParts = dateParts[1].split(':'),
                            dateDateParts = dateParts[0].split('-'),
                            date = new Date(dateDateParts[0], parseInt(dateDateParts[1], 10) - 1, dateDateParts[2], timeParts[0], timeParts[1], timeParts[2]),
                            timeZoneOffsetInMinutes = date.getTimezoneOffset(),
                            timeZoneOffsetSign = (timeZoneOffsetInMinutes>0)?'-':'+',
                            timeZoneOffsetHours = Math.floor(Math.abs(timeZoneOffsetInMinutes)/60),
                            timeZoneOffsetMinutes = Math.abs(timeZoneOffsetInMinutes%60),
                            timeZoneString = 'GMT'+timeZoneOffsetSign+((timeZoneOffsetHours<10)?'0':'')+timeZoneOffsetHours.toString()+timeZoneOffsetMinutes.toString()+((timeZoneOffsetMinutes<10)?'0':''),
                            dateOffset = new Date(date.getTime()-timeZoneOffsetInMinutes*60*1000);
                        
                        $(td).text(dateOffset.toLocaleString() + ' ' + timeZoneString);
                        
                    } else {
                        //noinspection JSUnfilteredForInLoop
                        $(td).text(data['data'].rows[index][propertyName]);
                    }
                }
                row.appendChild(td);
            }
            tbody.appendChild(row);
        }
    }
    table.appendChild(tbody);
    outputNode = document.getElementById(containerId);
    while (outputNode.hasChildNodes()) {//overkill
        outputNode.removeChild(outputNode.lastChild);
    }
    outputNode.appendChild(table);
}
/**
 * Turns table in current document's tableId into a datatable.js table
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
if ( $.fn.DataTable['TableTools'] ) {
    // Set the classes that TableTools uses to something suitable for Bootstrap
    $.extend( true, $.fn.DataTable['TableTools'].classes, {
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
    $.extend( true, $.fn.DataTable['TableTools']['DEFAULTS']['oTags'], {
        "collection": {
            "container": "ul",
            "button": "li",
            "liner": "a"
        }
    } );
}
