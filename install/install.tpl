<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- START BLOCK : header -->
<title>{sitename} :: {step} - {steptitle}</title>
<!-- END BLOCK : header -->
<link rel="stylesheet" type="text/css" href="css/install.css">
</head>
<body>
<div class="container-narrow">
	<div class="masthead">
		<div class="brand">eFiction {version} installation</div>
		<div class="progress-box">
			<div class="progress-label">Installation : Step {step} of 9</div>
			<div class="progress"><div class="progress-bar" style="width: {percent}%"></div></div>
		</div>
	</div>
	<div class="panel">
		<div class="panel-heading"><h2 class="panel-title">{steptitle}</h2></div>
		<div class="panel-body">
{output}
		</div>
	</div>
<!-- START BLOCK : footer -->
	<div class="footer">{footer}</div>
<!-- END BLOCK : footer -->
</div>
</body>
</html>
