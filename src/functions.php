<?php

//have to include config.php to get the connection credentials
include('/home/lbass/public_html/fakeAirbnb-LB/config/config.php');

/* Add your functions here */


function dbConnect(){
    /* defined in config/config.php */
    /*** connection credentials *******/
    $servername = SERVER;
    $username = USERNAME;
    $password = PASSWORD;
    $database = DATABASE;
    $dbport = PORT;
    /****** connect to database **************/

    try {
        $db = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4;port=$dbport", $username, $password);
    }
    catch(PDOException $e) {
        echo $e->getMessage();
    }
    return $db;
}


// function for getting neighborhoods in order
function getNeighborhoods($db) {

    try {
        $stmt = $db->prepare("SELECT * FROM neighborhoods ORDER BY neighborhood");   
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    } 
    catch (Exception $e) {
        echo $e;
    }

}


// used to get room types
function getRoomTypes($db) {

    try {
        $stmt = $db->prepare("SELECT * FROM roomTypes ORDER BY type");   
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    } 
    catch (Exception $e) {
        echo $e;
    }

}


// for getting the listings that follow the input
function getListings($db, $guests, $neighborhoodId, $roomId) {

    try {
        $sqlInput = "SELECT listings.id, listings.name, hosts.hostName, roomTypes.type, listings.accommodates, listings.price, listings.rating, listings.pictureURL, neighborhoods.neighborhood FROM listings
            INNER JOIN hosts ON listings.hostId = hosts.id
            INNER JOIN roomTypes ON listings.roomTypeId = roomTypes.id
            INNER JOIN neighborhoods ON listings.neighborhoodId = neighborhoods.id
            WHERE listings.accommodates >= ?";

        $params = [$guests];

        if ($neighborhoodId != 'any') {
            $sqlInput .= " AND listings.neighborhoodId = ?";
            $params[] = $neighborhoodId;
        }

        if ($roomId != 'any') {
            $sqlInput .= " AND listings.roomTypeId = ?";
            $params[] = $roomId;
        }

        $sqlInput .= " ORDER BY listings.price LIMIT 20";

        $stmt = $db->prepare($sqlInput);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    return $rows;

}


// used to get a single listing
function getSingleListing($db, $id) {
    try {
        $stmt = $db->prepare("SELECT listings.name, listings.pictureUrl, neighborhoods.neighborhood, listings.price, listings.accommodates, listings.rating, hosts.hostName FROM listings
            INNER JOIN hosts ON listings.hostId = hosts.id
            INNER JOIN neighborhoods ON neighborhoods.id = listings.neighborhoodId
            WHERE listings.id = ?");
        $stmt->execute([$id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($listing) {
            $listing['amenities'] = getAmenities($db, $id);
        }

        return $listing;
    
    }
    catch (Exception $e) {
        echo $e;
    }
}


// used to get the amenities of the listings
function getAmenities($db, $id) {
    try {
        $stmt = $db->prepare("SELECT amenities.amenity FROM amenities
            INNER JOIN listingAmenities ON amenities.id = listingAmenities.amenityID
            WHERE listingAmenities.listingID = ?");
        $stmt->execute([$id]);
        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($amenities, 'amenity');
    
    } catch (Exception $e) {
        echo $e;
    }
}

?>