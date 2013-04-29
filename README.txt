## Kaplan plugin

To get this plugin working:

* Enable webservices in advanced features
* Enable the rest protocol in Plugins>Web services>Manage protocols
* Add webservice/rest:use & moodle/webservice:createtoken capabilities to the authenticated user role. This will allow users to register a token for the ws and then access the service.
* Now add the block to a moodle page and you should see all courses and users listed.