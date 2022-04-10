// from http://www.devarticles.com/c/a/JavaScript/Javascript-AutoComplete/

//initialize some global variables
var list = null;
var keep_count = 1;
function fillit(sel,fld)
{
        var field = document.getElementById(fld);
        var selobj = document.getElementById(sel);
        if(!list)
        {
                var len = selobj.options.length;
                field.value = "";
                list = new Array();
		list["default"] = selobj.selectedIndex;
                for(var i = 0;i < len;i++)
                {
                        list[i] = new Object();
                        list[i]["text"] = selobj.options[i].text;
                        list[i]["value"] = selobj.options[i].value;
                }
        }
        else
        {
            var op = document.createElement("option");
            var tmp = null;
            for(var i = 0;i < list.length;i++)
           {
                tmp = op.cloneNode(true);
                tmp.appendChild(document.createTextNode(list[i]["text"]));
                tmp.setAttribute("value",list[i]["value"]);
                selobj.appendChild(tmp);
           }
        }
}

function findit(sel,field)
{
        var selobj = document.getElementById(sel);
        var len = list.length;
                if(!list)
                {
                        fillit(sel,field);
                }
                var op = document.createElement("option");
                selobj.options.length = keep_count;
                var reg = new RegExp(field.value,"i");
                var tmp = null;
                var count = 0;
                var msg = "";
		selobj.selectedIndex = 0;
                for(var i = 0;i < len;i++)
                {
                        if(reg.test(list[i].text) && (list[i].value != ""))
                        {
                                tmp = op.cloneNode(true);
                                tmp.setAttribute("value",list[i].value);
                                tmp.appendChild(document.createTextNode(list[i].text));
                                selobj.appendChild(tmp);
				count++;
				selobj.selectedIndex = 1;
                        }
                } 
                if (field.value.length == 0) {
                        selobj.selectedIndex = list["default"];
                }

}

function incrementor(objname) {
	// adds +/- option to a integer field
	obj = document.getElementById(objname);
	document.write("<input type='button' value='+' onClick='obj = document.getElementById(\"" + objname + "\"); obj.value = parseInt(obj.value) + 1' />");
	document.write("<input type='button' value='-' onClick='obj = document.getElementById(\"" + objname + "\"); obj.value = Math.max(1, parseInt(obj.value) - 1)' />");
}

