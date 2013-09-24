<?php

require 'IlluminateJasper.php';

Illuminate\Jasper::$url = 'https://your_username:your_password@jasper.yourdomain.com:8443/jasperserver/';

use Illuminate\Jasper\Report as Report;

$format = Report::getFormat((isset($_GET['format']) ? $_GET['format'] : Report::FORMAT_HTML));
$report = new Report(array(
	'format'     => $format,
	'report_url' => 'organizations/demo/Reports/test_highchart',
	'parameters' => array(
		'ignorePagination'      => true,
		'onePagePerSheet'       => false,
		'test_parameter'        => 12345,
	),
));
$request = $report->makeRequest();

// Download to PDF/XLS
if ($format != Report::FORMAT_HTML) {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: public");
	header("Content-Type: {$request->getHeader('Content-Type')}");
	header("Content-Length: {$request->getHeader('Content-Length')}");
	header("Content-Disposition: attachment; filename=\"report.{$format}\"");
	header("Content-Transfer-Encoding: binary");
	header("Accept-Ranges: bytes");
	echo $request->getBody();
	exit;
}

// If you have a base url make sure to add it in
// e.g., /~zrankin/Illuminate/jasper_api/report_asset.php?
// We shouldn't be passing the cookie via GET, this should be stored somewhere
// that is hidden to the client, otherwise they can login as us
$html = $report->getHtml("report_asset.php?id={$request->getJsessionid()}&uri=", '/jasperserver');

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/main.css">
<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="<?php echo Illuminate\Jasper::$url ?>reportresource?resource=com/jaspersoft/jasperreports/highcharts/charts/services/default.service.js"></script>
</head>
<body>
	<div class="container">
		<a href="?format=<?php echo Report::FORMAT_HTML ?>" class="btn">Run Report</a>
		<a href="?format=<?php echo Report::FORMAT_PDF ?>" class="btn btn-primary">Download to PDF</a>
		<a href="?format=<?php echo Report::FORMAT_XLS ?>" class="btn btn-primary">Download to XLS</a>
		<div id="jasper_report_container">
			<div class="row">
			<div class="col-md-6 col-md-offset-4">
					<ul class="pagination">
						<li class="prev disabled"><a href="javascript:;" class="jasper_report_prev">&larr; Previous Page</a></li>
						<li class="next disabled"><a href="javascript:;" class="jasper_report_next">Next Page &rarr;</a></li>
						<li><a href="javascript:;" class="jasper_report_disable_pagination" class="btn btn-small">Show All</a></li>
					</ul>
				</div>
			</div>
			<div id="jasper_report">
				<?php echo $html ?>
			</div>
		</div>
	</div>

<script type="text/javascript" src="js/JasperReportPaginator.js"></script>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>