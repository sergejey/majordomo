<script language="javascript">
<!--
/*
* @version 0.1 (auto-set)
*/

 function report_js_error(msg, url, linenumber) {
  stuff=" URL: "+url+" - "+msg+"; line: "+linenumber;
  tmp = new Image();
  tmp.src = ROOTHTML+"write_error.php?error="+stuff;
  return true;
 }

window.onerror=report_js_error



var bV=parseInt(navigator.appVersion);
NS4=(document.layers) ? true : false;
IE4=((document.all)&&(bV>=4))?true:false;

// <AJAX>

var Letters=new Array('%C0','%C1','%C2','%C3','%C4','%C5','%C6','%C7','%C8','%C9','%CA','%CB','%CC','%CD','%CE','%CF','%D0','%D1','%D2','%D3','%D4','%D5','%D6','%D7','%D8','%D9','%DA','%DB','%DC','%DD','%DE','%DF','%E0','%E1','%E2','%E3','%E4','%E5','%E6','%E7','%E8','%E9','%EA','%EB','%EC','%ED','%EE','%EF','%F0','%F1','%F2','%F3','%F4','%F5','%F6','%F7','%F8','%F9','%FA','%FB','%FC','%FD','%FE','%FF','%A8','%B8');
var flashing=0;

// -------------------------------------------------------------

function Win2Escape(AStr)
{
   var Result='';
   var aStrCnt = AStr.length;

   for(var i = 0; i < aStrCnt; i++)
   {
      if(AStr.charAt(i) >= '�' && AStr.charAt(i) <= '�')
         Result += Letters[AStr.charCodeAt(i) - 0x0410];
      else if (AStr.charAt(i) == '�')
         Result += Letters[64];
      else if (AStr.charAt(i) == '�')
         Result += Letters[65];
      else if (AStr.charAt(i) == '=')
         Result += '%3D';
      else if (AStr.charAt(i) == '&')
         Result += '%26';
      else
         Result += AStr.charAt(i);
   }
   
   return Result;
}

// -------------------------------------------------------------

function URLencode(sStr) {
    return (Win2Escape(sStr)).replace(/\+/g, '%2C').replace(/\"/g,'%22').replace(/\'/g, '%27');
}

// -------------------------------------------------------------

function startFlashing(block_id) {

 layer = document.getElementById(block_id);
 if (flashing==1) {
  layer.style.borderWidth='1px';
  layer.style.borderColor='#000000';
  if (layer.style.borderStyle.indexOf('none') == -1) {
   //alert(layer.style.borderStyle);
   layer.style.borderStyle='none';
  } else {
   layer.style.borderStyle='dotted';
  }
  window.setTimeout("startFlashing('"+block_id+"');", 100);

 } else {
  layer.style.borderStyle='none';
  layer.style.borderWidth='0px';
 }

}

// -------------------------------------------------------------

 function AJAXRequest(url, ready_function, first_param, proc_function) {

  proc_function = proc_function || '';
  first_param = first_param || '';

 var xmlhttp=false;

/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@end @*/

  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
   xmlhttp = new XMLHttpRequest();
  }
  xmlhttp.open("GET", url,true);
  xmlhttp.onreadystatechange=function() {
   if (xmlhttp.readyState==4) {
    eval(ready_function+'(\''+first_param+'\', xmlhttp.responseText);');
   }
  }
  if (proc_function) {
   eval(proc_function+'();');
  }
  xmlhttp.send(null);

  return false;
 }

// -------------------------------------------------------------

 function getBlockDataForm(block_id, form) {
    params='';
    var formElementsCnt = form.elements.length;
    
    for(i = 0; i < formElementsCnt; i++)
    {
       if (form.elements[i].type != 'radio' || form.elements[i].checked)
       {
         params += '&' + form.elements[i].name + '=' + URLencode(form.elements[i].value);
       }
    }
  
    url = form.action;

 layer = document.getElementById(block_id);
 old_data=layer.innerHTML;

 flashing=1;
 startFlashing(block_id);

// layer.innerHTML='<b>Loading please wait...</b>';


 var xmlhttp=false;

/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@end @*/

  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
   xmlhttp = new XMLHttpRequest();
  }

  xmlhttp.onreadystatechange=function() {
   if (xmlhttp.readyState==4) {
    flashing=0;
    layer.innerHTML=xmlhttp.responseText;
    layer.style.borderStyle='none';
   }
  }

  if (form.method=='post') {
   xmlhttp.open("POST", url, true);
   xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
   xmlhttp.send(params+'&filterblock='+block_id);
  } else {
   xmlhttp.open("GET", url+params+'&filterblock='+block_id,true);
   xmlhttp.send(null);
  }

  return false;

 }

// ------------------------------------------------------------

 function getBlockData(block_id, url) {
  layer = document.getElementById(block_id);
  old_data=layer.innerHTML;

  flashing=1;
  startFlashing(block_id);
//  return false;
  //layer.innerHTML='<b>Loading please wait...</b>';


 var xmlhttp=false;

/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@end @*/

  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
   xmlhttp = new XMLHttpRequest();
  }
  xmlhttp.open("GET", url+'&filterblock='+block_id,true);
  xmlhttp.onreadystatechange=function() {
   if (xmlhttp.readyState==4) {
    flashing=0;
    layer.style.borderStyle='none';
    layer.innerHTML=xmlhttp.responseText;
   }
  }
  xmlhttp.send(null);
  return false;
 }


// </AJAX>

//-->
</script>
