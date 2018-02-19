/*

Build image only.

*/

def context

stage("Create app context") {
	context = createApplicationContext()
}


stage("Build image") {
	buildImage(context)
}


/* TODO:
stage("Unit tests") {
}
*/

milestone 1


