/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('codeprotect', 'en'); // <- Add a comma separated list of all supported languages

/****
 * 
 * I'm not even gonna copyright this, that's just silly.
 *
 * Feel free to improve on this code and re-upload it.
 * 
 * Tijmen Schep, Holland, 9-10-2005
 * 
 * 
 ****/



/**
 * Gets executed when contents is inserted / retrived.
 */
function TinyMCE_codeprotect_cleanup(type, content) {
	switch (type) {
		case "get_from_editor":

content = content.replace(/<!--CODE/gi, "<?");
content = content.replace(/CODE-->/gi, "?>");

			break;

		case "insert_to_editor":
			
content = content.replace(/<\?/gi, "<!--CODE");
content = content.replace(/\?>/gi, "CODE-->");

			break;

		case "get_from_editor_dom":

			// Do custom cleanup code here. THIS PLUGIN DOESN'T USE THIS

			break;

		case "insert_to_editor_dom":

			// Do custom cleanup code here. BUT I LEFT IT IN ANYWAY..

			break;
	}

	return content;
}
