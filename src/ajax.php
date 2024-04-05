<?php
/* PHP code for AJAX interaction goes here */
require("functions.php");
//echo("test");

if (isset($_POST['id'])) {
    $listingId = $_POST['id'];

    $db = dbConnect();
    $listing = getSingleListing($db, $listingId);

    echo json_encode($listing);
}
?>