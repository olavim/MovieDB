<?php
require_once 'login.php';
include_once 'sql.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!is_numeric($id)) {
        die("Invalid id: " . $id);
    }

    $query = "SELECT director, year, title, pick FROM movie WHERE id=$id LIMIT 1";
    if ($result = $connection_moviedb->query($query)) {
        $row = $result->fetch_assoc();
        $director = $row["director"];
        $year = $row["year"];
        $title = $row["title"];
        $pick = $row["pick"];
        $result->free();
    } else {
        print_r($connection_moviedb->error);
    }

    $connection_moviedb->close();
} else if (isset($_POST['id'], $_POST['director'], $_POST['year'], $_POST['title'])) {
    $id = $_POST['id'];
    if (!is_numeric($id)) {
        die("Invalid id: " . $id);
    }

    if ($stmt = $connection_moviedb->prepare("UPDATE movie SET director = ?, year = ?, title = ?, pick = ? WHERE id='$id'")) {
        $stmt->bind_param("ssss", $director, $year, $title, $pick);

        $director = $_POST['director'];
        $year = $_POST['year'];
        $title = $_POST['title'];
        $pick = isset($_POST['pick']) ? "x" : "";

        if (!preg_match("/^([ \x{00c0}-\x{01ff}a-zA-Z'\-&])+$/u", $director)) {
            die("Error: invalid director name: " . $director);
        }

        if (!preg_match("/^[12][0-9]{3}$/", $year)) {
            die("Error: invalid year: " . $year);
        }

        $stmt->execute();

        $stmt->close();
        $connection_moviedb->close();

        header("Location: ./");
    } else {
        printf("Errormessage: %s\n", $connection_moviedb->error);
        $connection_moviedb->close();
        exit();
    }
} else {
    die("Invalid request");
}
?>
<!doctype html>
<html>
<head>
    <title>Movie Database - New Movie</title>
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
            <h1>Edit Movie</h1>
            <form method="post" action="edit_entry.php" data-ajax="false">
                <input type="hidden" name="id" value="<?=$id?>">
                <div class="input-section input-section-text">
                    <label for="director">DIRECTOR</label>
                    <input type="text" id="director" name="director" value="<?=$director?>" required pattern="^([ \u00c0-\u01ffa-zA-Z'\-&])+$">
                </div>
                <div class="input-section input-section-text">
                    <label for="year">YEAR</label>
                    <input type="text" id="year" name="year" value="<?=$year?>" required pattern="^[12][0-9]{3}$">
                </div>
                <div class="input-section input-section-text">
                    <label for="title">TITLE</label>
                    <input type="text" id="title" name="title" value="<?=$title?>" required>
                </div>
                <div class="input-section">
                    <input type="checkbox" id="pick" name="pick" <?=$pick?"checked":""?>>
                    <label for="pick">Picked</label>
                </div>
                <div style="overflow:hidden">
                    <button class="ui-button left" data-role="none" onclick="location.href='./';return false;" style="width:45%">Cancel</button>
                    <button class="ui-button right" data-role="none" style="width:45%">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>