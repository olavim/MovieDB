<!doctype html>
<html>
<head>
	<title>Movie Database - Search</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="http://fonts.googleapis.com/css?family=Roboto:100" rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="styles/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="styles/default.scss">
	<link rel="stylesheet" href="styles/form.css">
	<link rel="stylesheet" href="styles/global.css">
	<script src="js/jquery-2.1.4.min.js"></script>
	<script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script>
        $(function() {
            $(document).keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                    document.forms[0].submit();
                    return false;
                } else {
                    return true;
                }
            });
        });
    </script>
</head>
<body>
	<div data-role="page" id="search-page">
		<div data-role="content">
			<div class="form input-form">
				<h1>Search</h1>
				<form method="get" action="./" data-ajax="false">
					<div class="input-section input-section-text">
						<label for="s_director">DIRECTOR</label>
						<input type="text" id="s_director" name="s_director" placeholder="...">
					</div>
					<div class="input-section input-section-text">
						<label for="s_year">YEAR</label>
						<input type="text" id="s_year" name="s_year" placeholder="...">
					</div>
					<div class="input-section input-section-text">
						<label for="s_title">TITLE</label>
						<input type="text" id="s_title" name="s_title" placeholder="...">
					</div>
					<div class="input-section">
						<input type="checkbox" id="s_pick" name="s_pick" data.mini="true">
						<label for="s_pick">Show only picked</label>
					</div>
					<div style="overflow:hidden">
						<button class="ui-button left" data-role="none" onclick="location.href='./';return false;" style="width:45%">Cancel</button>
						<button class="ui-button right" data-role="none" style="width:45%">Search</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>