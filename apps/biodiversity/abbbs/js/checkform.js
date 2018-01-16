// ABBBS BirdBatMan Application:
// Function to validate all fields in a form.
// Requires styling for "div#formerrormsg p" and ".form_error"; see below
// Specify the elements to be checked via a hidden input element; eg.
// <input type="hidden" name="required" id="required" value="name,surname,email" />
//
// Apr/2006: JWemekamp
// To allow multiple forms in the same document, the 'required' field
// may have the form's name prepended.

function checkform(of) {
  // Test if DOM is available
  if (!document.getElementById || !document.createTextNode) {return true;}
  // Test if a 'required' or of.name + '_required' element is present
  var reqd;
  if (document.getElementById('required')) { reqd = 'required'; }
  else { 
    reqd = of.name + '_required';
    if (!document.getElementById(reqd)) { return true; }
  }

  // Define error messages and split the required fields (NO embedded spaces!)

  // var formID=of.id;
  var errorID='formerrormsg';
  var errorClass='form_error'
  var errorMsg='Please enter or change the fields marked with ';
  var errorImg='/apps/biodiversity/abbbs/images/alert.gif';
  var errorAlt='Error';
  var errorTitle='This field has an error!';
  var reqfields=document.getElementById(reqd).value.split(',');

  // Cleanup old mess: if there is an old error message field, delete it
  // remove old images and classes from the required fields

  cleanup();

  // loop over required fields

  var i,f,fid,ff;
  for (i=0;i<reqfields.length;i++) {
    // alert("Testing field " + reqfields[i]);

    f=document.getElementById(reqfields[i]);	// check for required field
    if (!f) {continue;}

    	// test if the required field has an error, according to its type
    switch(f.type.toLowerCase()) {
      case 'text':
	fid = f.id;
			// email fields are special and need checking
	if (fid.match(/email/)) {
	  if (f.value=='' || ! cf_isEmailAddr(f.value)) {cf_adderr(f)}
	}
	else {
	  if (f.value=='') {cf_adderr(f)}
	}
	break;
      case 'password':
	if (f.value=='') {cf_adderr(f)}
	break;
      case 'textarea':
	if (f.value=='' || f.value.match(/^\s+$/g)) {cf_adderr(f)}
	break;
      case 'checkbox':
	if (!f.checked) {cf_adderr(f)}
	break;
      case 'select-one':
        // alert("select-one: index= " + f.selectedIndex);
	if (!f.selectedIndex && f.selectedIndex==0) {cf_adderr(f)}
	break;
      case 'file':
	if (f.value=='') {cf_adderr(f)}
	break;
      case 'radio':
	if (f.value=='') {cf_adderr(f)};
        break;
    }
  }
  return !document.getElementById(errorID);

  /* Tool methods */
  function cf_adderr(o) {
    // create image, add to and colourise the error fields
    var errorIndicator=document.createElement('img');
    errorIndicator.alt=errorAlt;
    errorIndicator.src=errorImg;
    errorIndicator.title=errorTitle;
    o.className=errorClass;
    o.parentNode.insertBefore(errorIndicator,o);

    // Check if there is no error message
    if (!document.getElementById(errorID)) {
	// create errormessage and insert before submit button
      var em=document.createElement('div');
      em.id=errorID;
      var newp=document.createElement('p');
      newp.appendChild(document.createTextNode(errorMsg))
	// clone and insert the error image
      newp.appendChild(errorIndicator.cloneNode(true));
      em.appendChild(newp);
	// find the submit button
      for (var i=0;i<of.getElementsByTagName('input').length;i++) {
	if (/submit/i.test(of.getElementsByTagName('input')[i].type)) {
	  var sb=of.getElementsByTagName('input')[i];
	  break;
	}
      }
      if (sb) { sb.parentNode.insertBefore(em,sb); }
    }
  }
  function cf_isEmailAddr(str) {	// Not complete but a start
    return str.match(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/);
  }
  return true;

  // Clean up old error indicators for ALL forms on the document

  function cleanup() {
  // if there is an old error message field, delete it
  if (document.getElementById(errorID)) {
    var em=document.getElementById(errorID);
    em.parentNode.removeChild(em);
  }
  // If there's a 'required' element, clean up its fields
  // else clean up required elements in all forms:
  // removing old images and classes from any required fields

  if (document.getElementById('required')) { 
    cleanup_element('required');
    return true;
  }
  // Search thru all the forms

  var f, reqd, reqfields;
  var allForms = document.getElementsByTagName("form");
  for (var i=0; i<allForms.length; i++) {
    f = allForms.item(i);
  	// Test if f.name + '_required' element is present
    reqd = f.name + '_required';
    if (document.getElementById(reqd)) {
      // alert("Name of required field = " + reqd);
      cleanup_element(reqd);
    }
  }

  return true; 
  }
  // Clean up all required fields in the specified element

  function cleanup_element(reqd) {
    var reqfields = document.getElementById(reqd).value.split(',');
    for (i=0;i<reqfields.length;i++) {
      f=document.getElementById(reqfields[i]);
      if (!f) {continue;}
      if (f.previousSibling && /img/i.test(f.previousSibling.nodeName)) {
        f.parentNode.removeChild(f.previousSibling);
      }
      f.className='';
    }
  }
  return true;
}

