
function pop(theurl, w, h, scroll)
{
 var the_atts = "width="+w+", height="+h+", top=20, screenY=20, left=20, screenX=20,  toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars="+scroll+", resizable=yes, copyhistory=no";
 // open window
 window.open(theurl,'pop',the_atts);
}

function more_info(sid) {
   if(document.getElementById('more_' + sid).style.display == 'none') {
       document.getElementById('more_' + sid).style.display = 'block';
       document.getElementById('info_' + sid).style.display = 'none';
   }
   else {
       document.getElementById('info_' + sid).style.display = 'block';
       document.getElementById('more_' + sid).style.display = 'none';
   }
}

function category(pid, catid, name, locked, order) {
	this.pid = pid;
	this.catid = catid;
	this.name = name;
	this.locked = locked;
	this.order = order;
}

function character(charid, catid, charname) { 
	this.charid = charid;
	this.catid = catid;
	this.charname = charname;
}

function resetCats(element) {
	var selItem = document.getElementById(element).selectedIndex;
	var selValue = document.getElementById(element).options[selItem].value;
	var serverPage = basedir + "includes/categorylist.php";
	myRequest = new ajaxObject(serverPage, resetCatsResponse);
	val = "element=" + element + "&";
	val = val + "catid="+selValue;
	myRequest.update(val);
}

function resetCatsResponse(responseText, responseStatus) {
	if(responseStatus == 200) {
		categories.length = 0;
		eval(responseText);
		var selItem = document.getElementById(el).selectedIndex;
		var selValue = document.getElementById(el).options[selItem].value;
		var element = document.getElementById(el);
		element.options.length = 0;
		element.options[element.options.length] = new Option(lang['Back2Cats'], -1);
		for(z = 0; z < categories.length; z++) {
			categories[z].name = unescape(categories[z].name).replaceAll("+", " ");
			if(categories[z].pid == selValue || categories[z].catid == selValue) 
				element.options[element.options.length] = new Option(categories[z].name, categories[z].catid);
		}
		if(selValue != -1) element.options.selectedIndex = 1;
	}
	else {
		alert(responseStatus + ' -- Error Processing Request');
	}
}

function addCat(fromGroup, toGroup) {
	var strValues = "";
	var listLength = document.getElementById(toGroup).length;
	var selItem = document.getElementById(fromGroup).selectedIndex;
	var selValue = document.getElementById(fromGroup).options[selItem].value;
	var selText = document.getElementById(fromGroup).options[selItem].text;
	var newItem = true;
	for(i = 0; i < categories.length; i++) {
		categories[i].name = unescape(categories[i].name).replaceAll("+", " ");
		if(categories[i].catid == selValue) {
			if(categories[i].locked == 1) {
				alert(lang['CatLocked']);
				return false;
			}
		}
	}
	strValues = selValue;
	for(i = 0; i < listLength; i++) {
		strValues = strValues + "," + document.getElementById(toGroup).options[i].value;
		if(document.getElementById(toGroup).options[i].text == selText) {
			newItem = false;
			break;
		}
	}
	if(newItem) {
		document.getElementById(toGroup).options[listLength] = new Option(selText, selValue);
		document.getElementById(fromGroup).options[selItem] = null;
		document.getElementById("catid").value = strValues;
	}
	buildCharacters(toGroup);
}

function browseCategories(element) {
	var selItem = document.getElementById(element).selectedIndex;
	var selValue = document.getElementById(element).options[selItem].value;
	var serverPage = basedir + "includes/browsecategories.php";
	myRequest = new ajaxObject(serverPage, browseCategoriesResponse);
	val = "element=" + element + "&";
	val = val + "catid="+selValue;
	myRequest.update(val);
}

function browseCategoriesResponse(responseText, responseStatus) {
	if(responseStatus == 200) {
		var categories = new Array();
		var characters = new Array();
		eval(responseText);
		var selItem = document.getElementById(el).selectedIndex;
		var selValue = document.getElementById(el).options[selItem].value;
		var element = document.getElementById(el);
		var charlist1 = document.getElementById('charlist1');
		var charlist2 = document.getElementById('charlist2');
		var copyOption1 = new Array( );
		var copyOption2 = new Array( );
		element.options.length = 0;
		if(selValue != -1) element.options[element.options.length] = new Option(lang['Back2Cats'], -1);
		else element.options[element.options.length] = new Option(lang['Categories'], -1);
		for(z = 0; z < categories.length; z++) {
			categories[z].name = unescape(categories[z].name).replaceAll("+", " ");
			element.options[element.options.length] = new Option(categories[z].name, categories[z].catid);
		}
		if(selValue != -1) element.options.selectedIndex = 1;
		var copyOption1 = characters;
		var copyOption2 = characters;
	
		for(x = 0; x < copyOption1.length; x++) {
			for(y = 0; y < charlist1.length; y++) {
				if(charlist1.options[y].selected == true && charlist1.options[y].value == copyOption1[x][1]) {
					copyOption1[x][2] = false;
					copyOption1[x][3] = true;
				}
				if(charlist2.options[y].selected == true && charlist1.options[y].value == copyOption2[x][1]) {
					copyOption2[x][2] = false;
					copyOption2[x][3] = true;
				}
			}
		}
		if(charlist1) charlist1.length = 0;
		charlist2.length = 0;
		if(charlist1) charlist1.options[charlist1.options.length] = new Option(lang['Characters'], '', false, false);
		charlist2.options[charlist2.options.length] = new Option(lang['Characters'], '', false, false);

		for(i = 0; i < copyOption1.length; i++) {
			if(charlist1) charlist1.options[charlist1.options.length] = new Option(copyOption1[i][1], copyOption1[i][0], copyOption1[i][2], copyOption1[i][3]);
			charlist2.options[charlist2.options.length] = new Option(copyOption2[i][1], copyOption2[i][0], copyOption2[i][2], copyOption2[i][3]);
		}
	}
	else {
		alert(responseStatus + ' -- Error Processing Request');
	}	
}	

function buildCharacters(cats) {
	if(document.form.formname.value == "admins") return;
	var catslist = document.getElementById(cats);
	var serverPage = basedir + "includes/characterlist.php";
	var clist = new Array( );
	for(z = 0; z < catslist.length; z++) {
		clist[z] = catslist.options[z].value;
	}
	clist[z] = '-1';
	myRequest = new ajaxObject(serverPage, buildCharactersResponse);
	val = "element=" + cats + "&";
	val = val + "catid="+clist.join(",");
	myRequest.update(val);

}

String.prototype.replaceAll = function(pcFrom, pcTo){
	var i = this.indexOf(pcFrom);
	var c = this;
	
	while (i > -1){
		c = c.replace(pcFrom, pcTo);
		i = c.indexOf(pcFrom);
	}
	return c;
}

function buildCharactersResponse(responseText, responseStatus) {
	if(responseStatus == 200) {
		characters.length = 0;
		eval(responseText);
		var charid = document.form.charid;			
		var copyOption = new Array( );
		var a = 0;
		for(y = 0; y < charid.length; y++) {
			if(charid.options[y].selected == true) {
				for(a = 0; a < characters.length; a++) {
					if(characters[a][0] == charid.options[y].value) {
						characters[a][2] = false;
						characters[a][3] = true;
					}
				}
			}
		}
		document.form.charid.length = 0;
		for(i = 0; i < characters.length; i++) {
			charid.options[charid.options.length] = new Option(unescape(characters[i][1]).replaceAll("+", " "), characters[i][0], characters[i][2], characters[i][3]);
		}
	}
	else {
		alert(responseStatus + ' -- Error Processing Request');
	}
}

function removeCat( catid ) {
	var selitem = document.getElementById(catid).selectedIndex;
	var strValues = "";
	document.getElementById(catid).options[selitem] = null;
	for(i = 0; i < document.getElementById(catid).length; i++) {
		strValues = strValues + "," + document.getElementById(catid).options[i].value;
	}
	document.getElementById("catid").value = strValues;
	buildCharacters(catid);
}

function displayCatRows(catid) {
	var serverPage = basedir + "includes/categorylist.php";
	myRequest = new ajaxObject(serverPage, displayCatRowsResponse);
	
	val = "element="+catid+"&catid="+catid;
	myRequest.update(val);
}

function displayCatRowsResponse(responseText, responseStatus) {
	if(responseStatus == 200) {
		categories = Array( );
		eval(responseText);
		if(navigator.appName.indexOf('Microsoft') > -1) var canSee = 'block';
		else var canSee = 'table-row';
		var start = document.images['c_' + el].src.lastIndexOf(".") - 3;
		if(document.images['c_' + el].src.indexOf('on', start) == -1) {
			var state = 'off';
			tmp = document.images['c_' + el].src;
			document.images['c_' + el].src = document.images['c_' + el].src.replace('_off', '_on');
		}
		else {
			var state = 'on';
			tmp = document.images['c_' + el].src;
			document.images['c_' + el].src = document.images['c_' + el].src.replace('_on', '_off');
		}
		for(x = 0; x < categories.length; x++) {
			if(state == "off" && x == 0) continue;
			if(row = document.getElementById('catid_' + categories[x].catid)) {
				if(state == 'off')
					row.style.display = 'none';
				else
					row.style.display = canSee;
			}
		}			
	}
	else {
		alert(responseStatus + ' -- Error Processing Request');
	}
}

function setCategoryForm( chosen ) {
	var serverPage = basedir + "includes/categorylist.php";
	myRequest = new ajaxObject(serverPage, setCategoryFormResponse);
	var category = chosen.options[chosen.selectedIndex].value;
	val = "element=" + chosen.name + "&" + "catid=" + category;
	myRequest.update(val);
}

function setCategoryFormResponse(responseText, responseStatus) {
	if(responseStatus == 200) {
		categories = Array( );
		eval(responseText);
		var selItem = document.getElementById(el).selectedIndex;
		var selValue = document.getElementById(el).options[selItem].value;
		var element = document.getElementById(el);
		element.options.length = 0;
		orderafter = document.getElementById("orderafter");
		orderafter.options.length = 0;
		if(selValue != -1) element.options[element.options.length] = new Option(lang['Back2Cats'], -1);
		else element.options[element.options.length] = new Option(lang['Categories'], -1);
		for(z = 0; z < categories.length; z++) {
			categories[z].name = unescape(categories[z].name).replaceAll("+", " ");
			element.options[element.options.length] = new Option(categories[z].name, categories[z].catid);
			if(!z) continue;
			orderafter.options[orderafter.options.length] = new Option(categories[z].name, categories[z].catid);
		}
		if(selValue != -1) element.options.selectedIndex = 1;
			
	}
	else {
		alert(responseStatus + " -- Error Processing Request");
	}
}

function displayTypeOpts() {
	var choice = document.getElementById('field_type').selectedIndex;
	var type = document.getElementById('field_type').options[choice].value;
	if(window.tinyMCE) tinyMCE.execCommand('mceResetDesignMode'); 

	for(i = 1; i < 6; i++) {
		if(document.getElementById('opt_' + i)) {
			document.getElementById('opt_' + i).style.display = 'none';
		}
	}
	document.getElementById('opt_' + type).style.display = 'block';
	if(window.tinyMCE) tinyMCE.execCommand('mceResetDesignMode'); 
}

