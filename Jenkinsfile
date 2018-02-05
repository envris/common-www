def context = config {
    project = 'common'
    application = 'www'
}

stage("Build image") {
    buildProject(context)
}

stage("Tag image") {
    tagImageWithGitTagsAndBuildInfo(context) 
}

stage("Wait for input"){
    input([message: 'Deploy to Test?'])
}

stage("Deploy to Test") {
    copyImage(context, 'dev', 'test')
}
