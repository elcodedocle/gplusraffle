/*global $:false, jQuery:false*/

/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle
 * management system
 *
 * Webapp Client Controller
 *
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.0-beta
 *
 */


/**
 * Spinner minified code
 */
(function(t,e){if(typeof exports=="object")module.exports=e();else if(typeof define=="function"&&define.amd)define(e);else t.Spinner=e();})(this,function(){"use strict";var t=["webkit","Moz","ms","O"],e={},i;function o(t,e){var i=document.createElement(t||"div"),o;for(o in e)i[o]=e[o];return i;}function n(t){for(var e=1,i=arguments.length;e<i;e++)t.appendChild(arguments[e]);return t;}var r=function(){var t=o("style",{type:"text/css"});n(document.getElementsByTagName("head")[0],t);return t.sheet||t.styleSheet;}();function s(t,o,n,s){var a=["opacity",o,~~(t*100),n,s].join("-"),f=.01+n/s*100,l=Math.max(1-(1-t)/o*(100-f),t),u=i.substring(0,i.indexOf("Animation")).toLowerCase(),d=u&&"-"+u+"-"||"";if(!e[a]){r.insertRule("@"+d+"keyframes "+a+"{"+"0%{opacity:"+l+"}"+f+"%{opacity:"+t+"}"+(f+.01)+"%{opacity:1}"+(f+o)%100+"%{opacity:"+t+"}"+"100%{opacity:"+l+"}"+"}",r.cssRules.length);e[a]=1;}return a;}function a(e,i){var o=e.style,n,r;i=i.charAt(0).toUpperCase()+i.slice(1);for(r=0;r<t.length;r++){n=t[r]+i;if(o[n]!==undefined)return n;}if(o[i]!==undefined)return i;}function f(t,e){for(var i in e)t.style[a(t,i)||i]=e[i];return t;}function l(t){for(var e=1;e<arguments.length;e++){var i=arguments[e];for(var o in i)if(t[o]===undefined)t[o]=i[o]}return t;}function u(t){var e={x:t.offsetLeft,y:t.offsetTop};while(t=t.offsetParent)e.x+=t.offsetLeft,e.y+=t.offsetTop;return e;}function d(t,e){return typeof t=="string"?t:t[e%t.length];}var p={lines:12,length:7,width:5,radius:10,rotate:0,corners:1,color:"#000",direction:1,speed:1,trail:100,opacity:1/4,fps:20,zIndex:2e9,className:"spinner",top:"auto",left:"auto",position:"relative"};function c(t){if(typeof this=="undefined")return new c(t);this.opts=l(t||{},c.defaults,p);}c.defaults={};l(c.prototype,{spin:function(t){this.stop();var e=this,n=e.opts,r=e.el=f(o(0,{className:n.className}),{position:n.position,width:0,zIndex:n.zIndex}),s=n.radius+n.length+n.width,a,l;if(t){t.insertBefore(r,t.firstChild||null);l=u(t);a=u(r);f(r,{left:(n.left=="auto"?l.x-a.x+(t.offsetWidth>>1):parseInt(n.left,10)+s)+"px",top:(n.top=="auto"?l.y-a.y+(t.offsetHeight>>1):parseInt(n.top,10)+s)+"px"});}r.setAttribute("role","progressbar");e.lines(r,e.opts);if(!i){var d=0,p=(n.lines-1)*(1-n.direction)/2,c,h=n.fps,m=h/n.speed,y=(1-n.opacity)/(m*n.trail/100),g=m/n.lines;(function v(){d++;for(var t=0;t<n.lines;t++){c=Math.max(1-(d+(n.lines-t)*g)%m*y,n.opacity);e.opacity(r,t*n.direction+p,c,n);}e.timeout=e.el&&setTimeout(v,~~(1e3/h));})();}return e;},stop:function(){var t=this.el;if(t){clearTimeout(this.timeout);if(t.parentNode)t.parentNode.removeChild(t);this.el=undefined;}return this;},lines:function(t,e){var r=0,a=(e.lines-1)*(1-e.direction)/2,l;function u(t,i){return f(o(),{position:"absolute",width:e.length+e.width+"px",height:e.width+"px",background:t,boxShadow:i,transformOrigin:"left",transform:"rotate("+~~(360/e.lines*r+e.rotate)+"deg) translate("+e.radius+"px"+",0)",borderRadius:(e.corners*e.width>>1)+"px"});}for(;r<e.lines;r++){l=f(o(),{position:"absolute",top:1+~(e.width/2)+"px",transform:e.hwaccel?"translate3d(0,0,0)":"",opacity:e.opacity,animation:i&&s(e.opacity,e.trail,a+r*e.direction,e.lines)+" "+1/e.speed+"s linear infinite"});if(e.shadow)n(l,f(u("#000","0 0 4px "+"#000"),{top:2+"px"}));n(t,n(l,u(d(e.color,r),"0 0 1px rgba(0,0,0,.1)")));}return t;},opacity:function(t,e,i){if(e<t.childNodes.length)t.childNodes[e].style.opacity=i;}});function h(){function t(t,e){return o("<"+t+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',e);}r.addRule(".spin-vml","behavior:url(#default#VML)");c.prototype.lines=function(e,i){var o=i.length+i.width,r=2*o;function s(){return f(t("group",{coordsize:r+" "+r,coordorigin:-o+" "+-o}),{width:r,height:r});}var a=-(i.width+i.length)*2+"px",l=f(s(),{position:"absolute",top:a,left:a}),u;function p(e,r,a){n(l,n(f(s(),{rotation:360/i.lines*e+"deg",left:~~r}),n(f(t("roundrect",{arcsize:i.corners}),{width:o,height:i.width,left:i.radius,top:-i.width>>1,filter:a}),t("fill",{color:d(i.color,e),opacity:i.opacity}),t("stroke",{opacity:0}))));}if(i.shadow)for(u=1;u<=i.lines;u++)p(u,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(u=1;u<=i.lines;u++)p(u);return n(e,l);};c.prototype.opacity=function(t,e,i,o){var n=t.firstChild;o=o.shadow&&o.lines||0;if(n&&e+o<n.childNodes.length){n=n.childNodes[e+o];n=n&&n.firstChild;n=n&&n.firstChild;if(n)n.opacity=i}}}var m=f(o("group"),{behavior:"url(#default#VML)"});if(!a(m,"transform")&&m.adj)h();else i=a(m,"animation");return c;});


/*
 * Processing dots animation
 */
/*
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
        opts, target, spinner, propertyName, data, params, response;
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
                $('#subtitle').text(params['subtitle']);
                delete params['subtitle'];
            }
            //$("#preAjaxContent").hide();
            //$("#postAjaxContent").show();
        } else if(xmlhttp.readyState === 4){
            spinner.stop();
            if(xmlhttp.status === 404){
                data = {'data':{'columns':['']}};
            } else {
                alert(xmlhttp.responseText);
                try {
                    response = JSON.parse(xmlhttp.responseText);
                    data = {'data':{'columns':[],'rows':[[]]}};
                    for (propertyName in response){
                        data.data.columns.push(
                            xmlhttp.status === 404?'':propertyName
                        );
                        data.data.rows[0].push(
                            response[propertyName]
                        );
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
                if (data['data'].rows[index].hasOwnProperty(propertyName)) {
                    $(td).text(data['data'].rows[index][propertyName]);
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
