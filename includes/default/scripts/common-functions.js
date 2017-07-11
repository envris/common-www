/*
 This file contains proprietary information of the Commonwealth of Australia. Copying or reproduction without prior written approval is prohibited.
*/


/*################# event listeners #################*/

function addLoadListener(fn)
{
  if (typeof window.addEventListener != 'undefined')
  {
    window.addEventListener('load', fn, false);
  }
  else if (typeof document.addEventListener != 'undefined')
  {
    document.addEventListener('load', fn, false);
  }
  else if (typeof window.attachEvent != 'undefined')
  {
    window.attachEvent('onload', fn);
  }
  else
  {
    return false;
  }
  
  return true;
};

function attachEventListener(target, eventType, functionRef, capture)
{
  if (typeof target.addEventListener != "undefined")
  {
      target.addEventListener(eventType, functionRef, capture);
  }
  else if (typeof target.attachEvent != "undefined")
  {
      target.attachEvent("on" + eventType, functionRef);
  }
  else
  {
      return false;
  }

  return true;
};


/*################# Show / Hide Functions #################*/

function showHidden(id)
{
  if(!document.getElementById) return; // Not Supported
  if(document.getElementById)
  {
    if(document.getElementById(id) == null )
    {
      //ID not found so do nothing
    } else {
      document.getElementById(id).className = "shown";
    }
  }
}

function hideShown(id)
{
  if(!document.getElementById) return; // Not Supported
  if(document.getElementById)
  {
    if(document.getElementById(id) == null )
    {
      //ID not found so do nothing
    } else {
      document.getElementById(id).className = "hidden";
    }
  }
}

function change2Classes(id, strClassName, id2, strClassName2 )
{
  if(!document.getElementById) return; // Not Supported
  if(document.getElementById)
  {
    if(document.getElementById(id) == null )
    {
      //ID not found so do nothing
    } else {
      document.getElementById(id).className = strClassName;
    }

    if(document.getElementById(id2) == null )
    {
      //ID not found so do nothing
    } else {
      document.getElementById(id2).className = strClassName2;
    }
  }
}

function hideRadioButton(objField)
{
  //Check option 1
  objField[0].checked = true
  
  //Hide the 2nd option
  hideShown("typeoption_publications")
}


/*################# Browser resolution detection #################*/

checkBrowserWidth();

attachEventListener(window, "resize", checkBrowserWidth, false);

function checkBrowserWidth()
{
  var theWidth = getBrowserWidth();
  
  if (theWidth == 0)
  {
    var resolutionCookie = document.cookie.match(/(^|;)tmib_res_layout[^;]*(;|$)/);

    if (resolutionCookie != null)
    {
      setStylesheet(unescape(resolutionCookie[0].split("=")[1]));
    }
    
    addLoadListener(checkBrowserWidth);
    
    return false;
  }

  if (theWidth > 920)
  {
    setStylesheet("default-wide");
    document.cookie = "tmib_res_layout=" + escape("default-wide");
  }
  else
  {
    setStylesheet("");
    document.cookie = "tmib_res_layout=";
  }
  
  return true;
};

function getBrowserWidth()
{
  if (window.innerWidth)
  {
    return window.innerWidth;
  }
  else if (document.documentElement && document.documentElement.clientWidth != 0)
  {
    return document.documentElement.clientWidth;
  }
  else if (document.body)
  {
    return document.body.clientWidth;
  }
  
  return 0;
};

function setStylesheet(styleTitle)
{
  var currTag;

  if (document.getElementsByTagName)
  {
    for (var i = 0; (currTag = document.getElementsByTagName("link")[i]); i++)
    {
      if (currTag.getAttribute("rel").indexOf("style") != -1 && currTag.getAttribute("title"))
      {
        currTag.disabled = true;

        if(currTag.getAttribute("title") == styleTitle)
        {
          currTag.disabled = false;
        }
      }
    }
  }
  
  return true;
};


/*################# Flyout navigation #################*/
/* Created by Alex Fahey 0419 47 9898 */

var adjustTop = 0;                   // Set if flyout top not aligned
var adjustLeft = 0;                  // Set if flyout not aligned on right side of menu bar
var flyoutMenu = null;               // Selected menu reference
var highlighted = null;              // The top level menu item selected
var offsetX = 0;                     // Flyout left offset
var offsetY = 0;                     // Flyout top offset
var timeout = null;                  // Reference to timeout event

/*
Calculators the X and Y coordination offsets for the flyout 
from the root of the document xml tree.
*/
function calculateOffsets(obj)
{
  var tagObject = obj;
  
  offsetY = adjustTop; 
  offsetX = obj.offsetWidth;
  
  while (tagObject) // Calculate x,y offsets 
  {
    offsetY = offsetY + tagObject.offsetTop;
    offsetX = offsetX + tagObject.offsetLeft;
    tagObject = tagObject.offsetParent;
  }

  // Perform adjustments
  offsetY += adjustTop; 
  offsetX += adjustLeft;
}

/*
Cancels the current window timeout event
*/
function cancelTimeout()
{
  window.clearTimeout(timeout);
  timeout = null; // Remove reference
}

/*
Displays the flyout menu at the specified X and Y coordination
*/
function display(xPos, yPos, stayOpen)
{
//alert(xPos);
//alert(yPos);

  flyoutMenu.style.display = "block";
  flyoutMenu.style.visibility = "visible";

  //flyoutMenu.style.left = (xPos - 1000) + "px";
  flyoutMenu.style.left = "170px";
  flyoutMenu.style.top =  (yPos - 100) + "px";

  // turn highlight on  
  highLightOn();
}

/*
Hides the currently displayed flyout menu
*/
function hide()
{ 
  if((flyoutMenu != null) && (flyoutMenu != undefined))
  {
    flyoutMenu.style.display="none";
    flyoutMenu.style.visibility="hidden";

    // Reset offsets to zero
    flyoutMenu.style.left =  0 + "px";
    flyoutMenu.style.top = 0 + "px";

    // Reset references
    flyoutMeanu = null;

    // turn highlight off
    highLightOff();
  }
}
  
/*
Starts the display of flyout menu by determining the select menu
*/
function mouseOver(strGroup)
{
  if(!(typeof(strGroup) == "string")) // guard
  { 
    // The strGroup must be a string value of the id attribute of the menu group UL tag
    return; 
  }

  var stayOpen = false;
  var group = document.getElementById(strGroup) // Get the LI nav-group
  
  if((flyoutMenu != null) && (flyoutMenu.parentNode.id == group.id))
  {
    stayOpen = true;
    cancelTimeout();
  }
  else // Menu group selected
  {
    if((flyoutMenu != null) && (flyoutMenu != undefined))
    { 
      // A flyout menu currently open
      cancelTimeout();
      hide(); // Hide now
    }

    // Get the first unordered list in the nav-group DIV tag
    flyoutMenu = group.getElementsByTagName("ul")[0];

    // Calculate the x,y position from the menu_grop
    calculateOffsets(group);

    // Get the first A child tag of the DIV within the nav-group
    //highlighted = document.getElementById(strGroup);
    highlighted = group.getElementsByTagName("a")[0];
  }
  
  if((flyoutMenu != null) && (flyoutMenu != undefined))
  {
    display(offsetX, offsetY, stayOpen); // display the flyout
  }
}

/*
Closes the currently display flyout menu in a delayed time period
*/
function shut()
{
  if((flyoutMenu != null) && (flyoutMenu != undefined))
  {
    timeout = window.setTimeout("hide();", 400);
  }
}

function highLightOff()
{
  //Restore original colours
  //highlighted.style.backgroundColor = '#ff0000';
  highlighted.style.textDecorationUnderline = '';

}

function highLightOn()  
{
  // Set highlight colours
  //highlighted.style.backgroundColor = '#00ff00';
  highlighted.style.textDecorationUnderline = 'true';

}