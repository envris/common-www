// abbbs.js
// JavaScript functions used by ABBBS Public and Client Web Portal forms
//

// Prevent posting of the form when Enter is pressed on a text field
// To use, add 'onkeypress="return noenter(event);"' 
// to the <input type="text" ...> element.

function noenter(event) {
  event = event || window.event; // IE sucks
  return (event.keyCode != 13);
} 

// Function to prevent multiple submits by disabling (and hiding)
// the submit button (whose id must be 'sub_button').
// Also requires an element called 'wait_msg' in the form.
// Example: <form .... onsubmit="if (checkform(this)) { return Submit(this) } else { return false; }">
//
// Apr/2006: JWemekamp
// Also allow the button to have the id form.name + '_sub_button'
// in case there are multiple forms in the document.

var submitted=false;

function Submit(frm) {
  if (!document.getElementById) {return true;}
  if (submitted) { return false; }

  var sub_button;
  if (document.getElementById('sub_button')) { sub_button = 'sub_button' }
  else {
    sub_button = frm.name + '_sub_button';
    if (!document.getElementById(sub_button)) {return true;}
  }
  submitted=true;
  if (frm.elements[sub_button].style) {
    frm.elements[sub_button].style.display = 'none';
    if (document.getElementById('wait_msg')) {
      var msg = document.getElementById('wait_msg');
      msg.style.display="block";
    }
  }
  else { frm.elements[sub_button].value = 'Please wait...'; }

  frm.elements[sub_button].disabled = true;
  return true; 
}

