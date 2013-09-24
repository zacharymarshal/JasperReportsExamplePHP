JasperReportsExamplePHP
=======================

Example running a report using Jasper Reports REST API (v2)

Setup
=====

 1. Update the ```Illuminate\Jasper::$url``` in both the report.php and report_asset.php
 2. Set the base url on line 39 of report.php if you are not running your script out of ```/```

WARNING: The JSESSIONID cookie is being passed to the report_asset.php via GET, this needs to be stored in the
SESSION or DB, etc.  Passing this via the URL will allow anyone to login to your jasperserver using that cookie
id.
