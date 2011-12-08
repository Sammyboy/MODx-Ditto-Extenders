<?php

/*
 * Title:       Search Filter for Ditto
 * Version:     1.0
 * Purpose:     Expands Ditto's functionality to include filtering search results
 *
 * License:     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * Author:      sam (sam@gmx-topmail.de)
 * www:         https://github.com/Sammyboy/MODx-Ditto-Extenders
 *
 * Installation:
 *      Copy this file into "assets/snippets/ditto/extenders/" of your MODx
 *      installation folder
 * Usage:
 *      [!Ditto? &extenders=`search` &searchString=`my search string` &searchFields=`content,tv1,tv2` &searchOptions=`caseSensitive` ... !]
*/

// ---------------------------------------------------
// Search Parameters
// ---------------------------------------------------

$searchFields = isset($searchFields) ? $searchFields : "content";
/*
	Param: searchFields

	Purpose:
 	Fields to search in

	Options:
	Comma separated list of document variables and template variables
	
	Default:
	"content"
*/

$searchOptions = isset($searchOptions) ? $searchOptions : "";
/*
	Param: searchOptions

	Purpose:
 	Search Options

	Options:
	"caseSensitive" - Get case sensitive results only
	"regex" -   Search for regular expressions
	"eval" -    Code of custom function. The variable $searchContent contains
	            the content of the document variable or template variable
	            Example:    &searchOptions=`eval` &searchString=`return strpos(strtolower($searchContent), 'test');`
	                        Should return then same results as
	                        &searchOptions=`` &searchString=`test`
	
	Default:
	""
*/

// ---------------------------------------------------
// Search Filter Class
// ---------------------------------------------------

if (!class_exists("searchFilter")) {
	class searchFilter {
		var $sourceFields, $searchString, $searchOptions, $caseSensitive;
	
		function searchFilter($searchString = "", $sourceFields = "content", $searchOptions = "") {
			$this->options = array_combine($options = explode(",", $searchOptions), array_fill(0, count($options), true));
			if ($this->options["caseSensitive"] = (bool) $this->options["caseSensitive"] || false)
			    $this->searchString = strtolower($this->searchString);
			
			$this->options["code"] = isset($this->options["code"]) ? (bool) $this->options["code"] : false;
			$this->searchString = (isset($this->options["file"]) && (bool) $this->options["file"] ?
			                        file_exists($searchString) : false) ? file_get_contents($searchString) : $searchString;
			$this->sourceFields = explode(",", $sourceFields);
		}

		function execute($resource) {
		    global $modx;
			
			$result = 0;
			
			foreach ($this->sourceFields as $field) {
			    $searchContent = $resource[$field];
			    
		        if ($this->options["code"]) {
		            if (eval($this->searchString) !== false)
		                $result = 1;
		        } elseif ($this->options["regex"]) {
		            $matches = array();
		            preg_match($this->searchString, $searchContent, $matches);
		            if (count($matches) > 0)
		                $result = 1;
			    } else {
			        if (!$this->options["caseSensitive"])
			            $searchContent = strtolower($searchContent);

			        if (strpos($searchContent, $this->searchString) !== false)
				        $result = 1;
                }
			}
			return $result;
		}
	}
}

// ---------------------------------------------------
// Search Filter Execution
// ---------------------------------------------------
if (isset($searchString)) {
	$searchFilterObject = new searchFilter($searchString, $searchFields, $searchOptions);
	$filters["custom"]["searchFilter"] = array($searchFields,array($searchFilterObject,"execute"));
}

?>
