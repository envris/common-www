/*
 This file contains proprietary information of the Commonwealth of Australia. Copying or reproduction without prior written approval is prohibited.
 Copyright 2006
 Version 0.1
 Created by Alex Fahey 0419 47 9898
*/

/*############################### Show / Hide Functions ##############*/

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
