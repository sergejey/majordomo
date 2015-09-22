/*
* @version 0.1 (auto-set)
*/
var lastID
var defaultLabelWindowX = 0, defaultLabelWindowY = 0
function lmo(id){
	// find template div or span
	var templates = grabTemplateNames(window.event.srcElement)
	setStatus(templates, id)
	lastID = id
	window.event.srcElement.focus()
	var aElement = findAElement(window.event.srcElement)
	if (aElement!=null) {
		// check if there is Label dialog open
		if (labelWindow==null || labelWindow.closed) {
			// focus the link
			aElement.onkeypress=lkp
			aElement.onmouseover=lamo
			aElement.focus()
		}
	}
	window.event.returnValue = true
}
function grabTemplateNames(elem) {
	var templates = ""
	var counter=0;
	while (elem!=null && elem.tagName.toUpperCase()!='BODY') {
		if (elem.tagName.toUpperCase()=='DIV' || elem.tagName.toUpperCase()=='SPAN' || elem.tagName.toUpperCase()=='TBODY') {
			if (elem.name!=null && elem.name.indexOf('.html')>0) {
			        nm=elem.name.replace("<#DIR_TEMPLATES#>", "");
			        if (counter>0) {
					templates = nm + " > " + templates 
			        } else {
	 				templates = nm
			        }
			        counter++;
			}
		}	
		elem = elem.parentElement
	}
	return templates
}
function setStatus(templates, id) {
	window.status = templates+id 
}

function findAElement(elem) {
	while (elem.parentElement.tagName.toUpperCase()!='A' && elem.parentElement.tagName.toUpperCase()!='BODY') {
		elem = elem.parentElement
	}
	if (elem.parentElement.tagName.toUpperCase()!='A') {
		return null
	}
	return elem.parentElement
}

function lamo(){
	setStatus (grabTemplateNames(window.event.srcElement), lastID)
}

function lmu(id){
	lastID = window.status=''
	window.event.returnValue = true
}
function lkp(){
	if (window.event.keyCode==69 || window.event.keyCode==101) { // 'E', 'e'
		if (lastID!='') showLabelForm(lastID)
		window.event.returnValue = true
	} else 	if (window.event.keyCode==67 || window.event.keyCode==99) { // 'C', 'c'
		clipboardData.setData('Text', lastID)
	}

}
function lmc(id){
	if (findAElement(window.event.srcElement)!=null) {
		return // inside <a>
	}
	showLabelForm(id)
	window.event.returnValue = true
}
function setLabel(){
	// find all spans having onMouseOver=lom('labelWindow.lf.val.value')
	allElem = document.body.getElementsByTagName("SPAN")
	for (a=0; a < allElem.length; a++) {
		if (allElem[a].onmouseover!=null) {
			if (allElem[a].onmouseover.toString().indexOf("lmo('"+labelID+"')")>=0) {
				allElem[a].innerHTML = labelWindow.lf.val.value
			}
		}
	}	
	// document.all[labelWindow.lf.name.value].innerHTML = labelWindow.lf.val.value
}

var labelWindow
var labelID
var initialValue
function showLabelForm(id){
	if (labelWindow!=null && !labelWindow.closed) {
		labelWindow.close()
	}
	if (defaultLabelWindowX==0) {
		defaultLabelWindowX = window.screenLeft+100
		defaultLabelWindowY = window.screenTop+100
	}
	labelWindow = window.open("","labelWnd","width=450, height=60, left="+defaultLabelWindowX+", top="+defaultLabelWindowY)
	labelID = id
	setTimeout("fillLabelWindow()",100)
}

function copyName() {
	clipboardData.setData("Text", labelID)
	window.close()
}
function dmo() {
	if (window.status=='') {
		window.status = grabTemplateNames(window.event.srcElement)
	}
}
function dmu() {
	window.status = ''
}
// functions to run from debug console

// make a border around the template
function markTemplate(tmplt, remove){
	// find all spans and divs having name=tmplt
	var tags = new Array()
	tags[0] = "SPAN"
	tags[1] = "DIV"
	tags[2] = "TBODY"
	var i
	var tagsCnt = tags.length
	for (i = 0; i < tagsCnt; i++) {
	var allelem = document.body.getElementsByTagName(tags[i])
	var allelemCnt = allelem.length
	for (a=0; a < allelemCnt; a++) {
		if (allelem[a].name!=null && allelem[a].name==tmplt) {
			if (allelem[a].name==tmplt) {
				if (tags[i]=='TBODY') {
					if (remove) {
						borderStyle = "0pt none black"
					} else {
						borderStyle = "1pt solid black"
					}	
					var firstRow = allelem[a].children[0]
					for (col=0;col<firstRow.children.length;col++) firstRow.children[col].style.borderTop = borderStyle
					var lastRow = allelem[a].children[allelem[a].children.length-1]
					for (col=0;col<lastRow.children.length;col++) lastRow.children[col].style.borderBottom = borderStyle

					lastRow.borderBottom = borderStyle
					var j
					for (j=0; j<allelem[a].children.length; j++) {
						var child = allelem[a].children[j]
						child.children[0].style.borderLeft = borderStyle
						child.children[child.children.length-1].style.borderRight = borderStyle
					}
				} else {	
					markElement(allelem[a], remove)
				}
			}
		}
	}
	}
}

function markElement(elem, remove) {
	if (remove) {
		elem.style.border = 'none'
		elem.style.borderWidth="0pt"
	} else {
		elem.style.border = 'solid'
		elem.style.borderWidth="1pt"
		elem.style.borderColor='black'
	}
}

function tmo(tmplt) {
	markTemplate(tmplt, 0)
}
function tmu(tmplt) {
	markTemplate(tmplt, 1)
}