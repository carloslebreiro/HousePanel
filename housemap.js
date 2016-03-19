// jquery functions to do Ajax on housemap.php

// the main jquery setup function that is called initially
$(document).ready(function(event){

    // activate click for all the up/down buttons for quantity
    setupButtons();

});

var setupButtons = function() {

    var buttons = $('input[name^=sw-]').filter(":button");
    buttons.each(function(i) {
    
        $(this).bind("click", function(event) {
            var theid= this.id;
            var theval= $(this).val();
            var theclass;
            if (theval=="on") {
            	theval = "off";
            	theclass = "reddot";
            } else {
            	theval = "on";
            	theclass = "greendot";
            }

            var maintitle= $("#maintitle").text() || "";
            var isswitch = $('input[type="hidden"][name="setswitch"]');
            
            // alert("value = " + theval);

            // make Ajax call to update the page
            if (isswitch ) {
                // alert("token="+token+" productid="+pid+" quantity="+qnewval);
                // $.post("housemap.php",
                //         {id: theid, useajax: "1", value: theval}, handleAjax);
                $(this).attr("value",theval);
                $(this).attr("class",theclass);
                $.post("housemap.php",
                        {id: theid, useajax: "1", value: theval});
            }
                
	    // alert("clicked button=" + $(this).attr("name") + " with id=" + this.id + " and class="+$(this).attr("class"));
        });
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

