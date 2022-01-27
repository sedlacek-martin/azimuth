# Development

### Branches, merge requests
When developing, use pull requests to merge the code to master branch. 
When you push code to branch, an automatic code-style pipeline will run for your code. 

Pull request could only be merged, when pipeline succeeds ✅.

### Code style
When developing, you can fix your code style in whole project using this command

    ./bin/csfix

### Translations
On production server, the translations are persistent. It means, that translations in repository are ignored on deployment. 
It's recommended to change the translations on _Beta_ server environment and then copy the translations to _Production_ environment


To do so, you can use helper command on server

    /tools/copy-translations <from> <to>

So for example command `/tools/copy-translations prod beta` will copy all translations from production to beta.

### Deployment
Deployment can be done using GitHub actions. There are to automatic deployment workflows setup (one for Production, one for Beta).
Both of these actions have to be triggered manually.