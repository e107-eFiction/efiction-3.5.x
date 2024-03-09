var secs = 10;
var timerID = null;
var timerRunning = false;
var timedElement = null;
var lastQuery = null;
var THROTTLE_PERIOD = 1000;

function findPos(obj) {
	var curleft = 0; var curtop = 0;
	if(obj.offsetParent) {
		while(obj.offsetParent) {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
			obj = obj.offsetParent;	
		}
	}
	else{
		if(obj.x) curleft += obj.x;
		if(obj.y) curtop = obj.y;
	}
	return Array(curleft, curtop);
}

function setUserSearch(element) {
	timedElement = element;
	timerID = setTimeout('getUsers();', THROTTLE_PERIOD);
}

function getUsers() {
	if(timedElement != null) {
		str = document.getElementById(timedElement + "Select").value;
		if(str != "" && str != lastQuery) {
			var serverPage = "includes/userlist.php";
			myRequest = new ajaxObject(serverPage, setUsers);
			val = "element=" + timedElement + "&";
			val = val + "str="+str;
			myRequest.update(val);
			lastQuery = str;
			clearTimeout(timerID);
		}
		else timerID = setTimeout('getUsers();', THROTTLE_PERIOD);
	}
	else timerID = setTimeout('getUsers();', THROTTLE_PERIOD);
}

function setUsers(responseText, responseStatus) {
	if(responseStatus == 200) {
		var element;
		var userList = new Array( );
		eval(responseText);
		var input = document.getElementById(element+"Select");
		var div = document.getElementById(element+"Div");

		// remove the old names first.	
		while(div.firstChild) {
			div.removeChild(div.firstChild);
		}
		if(userList.length == 0) return;
		posObj = findPos(input);
		var list = document.createElement('div');
		list.className = "ajaxOptList";
		list.setAttribute("id", element+"Names");
		list.onblur = removeList;
		for(x = 0; x < userList.length; x++) {
			var opt = document.createElement('div');
			opt.appendChild(document.createTextNode(userList[x][1]));
			opt.setAttribute("id", element + "__" + userList[x][0]);
			opt.className = "ajaxListOpt";
			opt.onmouseover = ajaxOptOver;
			opt.onmouseout = ajaxOptOut;
			opt.onclick = setUser;
			list.appendChild(opt);
		}
		div.appendChild(list);
		var shim = document.getElementById(element+"shim");
		shim.style.height = list.offsetHeight + "px";
		shim.style.width = list.offsetWidth + "px";
		listPos = findPos(document.getElementById(element + "Names"));
		shim.style.left = posObj[0] + "px";
		shim.style.top = posObj[1] + "px";
		shim.style.zIndex = 100;
		shim.style.display = "block";
		list.style.top = (posObj[1] + 23) + "px";
		list.style.left = posObj[0] + "px";
		div.style.visibility = "visible";
		secs = 10;
		removeList();
	}
	else {
		alert(responseStatus + ' -- Error Processing Request');
	}
}

function removeList() {
	if(secs == 0 && timerRunning == true) {
		clearTimeout(timerID);
		timerRunning = false;
		document.getElementById(timedElement + "Div").style.visibility = "hidden";
	}
	else {
		self.status = secs;
		secs = secs - 1;
		timerRunning = true;
		timerID = self.setTimeout("removeList( )", 5000);
	}
}
function ajaxOptOver( ) {
	this.className = "ajaxListOptOver";
}
function ajaxOptOut( ) {
	this.className = "ajaxListOpt";
}

function setUser() {
	var list = this.parentNode;
	var div = list.parentNode;
	div.style.height = "0px";
	div.style.width = "0px";
	div.style.visibility = "hidden";
	var info = this.id;
	var split = info.split("__");	
	var shim = document.getElementById(split[0] + "shim");
	shim.style.visiblity = "hidden";
	shim.style.zIndex = -1;
	var hidden = document.getElementById(split[0]);
	var selected = document.getElementById(split[0] + "Selected");
	if(selected) {
		var selectedList = new Array( );
		var newItem = true;
		for(i = 0; i < selected.length; i++) {
			selectedList.push(selected.options[i].value);
			if(selected.options[i].text == this.firstChild.nodeValue) {
				newItem = false;
				break;
			}
		}
		if(newItem) {
			selectedList.push(split[1]);
			selected.options[selected.length] = new Option(this.firstChild.nodeValue, split[1]);
			hidden.value = selectedList.join( );
		}	
	}
	else {
		hidden.value = split[1];
		var input = document.getElementById(split[0] + "Select");	
		input.value = this.innerHTML;
		div.removeChild(list);
	}
	lastQuery = document.getElementById(split[0] + "Select").value;
	clearTimeout(timerID);
	timerID = setTimeout("getUsers( )", 1000);
}

function removeMember(element) {
	var hidden = document.getElementById(element);
	var selected = document.getElementById(element + "Selected");
	var newOpts = new Array( );
	var y = 0;
	for(x = 0; x < selected.length; x++) {
		if(selected.options[x].selected == false) {
			newOpts[y] = Array(selected.options[x].text, selected.options[x].value);
			y++;
		}
	}
	selected.options.length = 0; // clear the whole list;
	var newIDs = new Array( );
	for(x = 0; x < newOpts.length; x++) {
		newIDs[x] = newOpts[x][1];
		selected.options[selected.length] = new Option(newOpts[x][0], newOpts[x][1]);
	}
	hidden.value = newIDs.join( );
}