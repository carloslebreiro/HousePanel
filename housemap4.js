// jquery functions to do Ajax on housemap.php

// the main jquery setup function that is called initially
$(document).ready(function(event){

    // activate click for all the up/down buttons for quantity
    setupButtons();
    setupMotions();
    setupContacts();
    
    // activate tabs
    $( "#tabs" ).tabs();

});

var setupButtons = function() {

    var buttons = $('input[name^=sw-]').filter(":button");
    buttons.each(function(i) {
    
        $(this).bind("click", function(event) {
            var theid= this.id;
            var theval= $(this).val();
            var theclass = $(this).attr("class");
            // alert(theclass);
            if (theclass=="swon") {
            	theval = "off";
            	theclass = "swoff";
            } else {
            	theval = "on";
            	theclass = "swon";
            }

            // var maintitle= $("#maintitle").text() || "";
            // var isswitch = $('input[type="hidden"][name="setswitch"]');
            // alert("value = " + theval);

            // make Ajax call to update the page
            // alert("token="+token+" productid="+pid+" quantity="+qnewval);
            // $.post("housemap.php",
            //         {id: theid, useajax: "1", value: theval}, handleAjax);
            // $(this).attr("value",theval);
            $(this).attr("class",theclass);
            $.post("housemap4.php",
                    {id: theid, useajax: "1", value: theval});
	    // alert("clicked button=" + $(this).attr("name") + " with id=" + this.id + " and class="+$(this).attr("class"));
        });
    });
};

var setupMotions = function() {

    $("td[name^=motion-]").click(function() {
	var thename = encodeURIComponent($(this).html());
	$("td[name^=motion-]").attr("class","sensoroff");
	$(this).attr("class","sensoron");
	// alert("sensor name = "+thename);
	$("#sensordata").load("housemap4.php",
                              {sensorajax: "1", type: "motion", value: thename});
    });
};

var setupContacts = function() {

    $("td[name^=contact-]").click(function() {
	var thename = encodeURIComponent($(this).html());
	$("td[name^=contact-]").attr("class","sensoroff");
	$(this).attr("class","sensoron");
	// alert("contact name = "+thename);
	$("#doordata").load("housemap4.php",
                              {sensorajax: "1", type: "contact", value: thename});
    });
};

var handleAjax = function(xmldoc) {

    if (!xmldoc) {
        alert("Unknown Ajax error");
        return;
    }
    
    if ( !xmldoc.documentElement) {
        alert("AJAX Error: " + xmldoc);
        return;
    }

    alert("after pages " + xmldoc);
    
}

