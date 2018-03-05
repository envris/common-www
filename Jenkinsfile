/*

Build and deploy from a regular docker image.

*/

// 'defaults' override the auto-generated values. Uncomment items to change the default value:
def defaults = [:]
//	defaults.product     = ''	// Default: The Jenkins job folder name
//	defaults.function    = ''	// Default: The Jenkins job project name
	defaults.project     = 'common'	// Default: '<product>-<zone>'
//	defaults.zone        = ''	// Default: 'private'
//	defaults.projectDesc = ''	// Default: The Jenkins job folder description 
//	defaults.appDesc     = ''	// Default: ''

def config

stage("Parse config") {
	config = createApplicationContext(defaults)
}

stage("Build image") {
	buildImage(config)
}

/* TODO:
stage("Unit tests") {
}
*/

milestone 1
