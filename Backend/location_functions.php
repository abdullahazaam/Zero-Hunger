<?php
// ============================================
// LOCATION FUNCTIONS FOR WORLDWIDE FILTERING
// ============================================

/**
 * Calculate distance between two coordinates (Haversine formula)
 * Returns distance in kilometers
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) {
        return PHP_INT_MAX;
    }
    
    $earth_radius = 6371; // Kilometers
    
    $lat1 = floatval($lat1);
    $lon1 = floatval($lon1);
    $lat2 = floatval($lat2);
    $lon2 = floatval($lon2);
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earth_radius * $c;
    
    return round($distance, 2);
}

/**
 * Get user's location from session or database
 */
function getUserLocation($conn, $user_id) {
    // First check session
    if (isset($_SESSION['user_lat']) && isset($_SESSION['user_lng'])) {
        return [
            'lat' => $_SESSION['user_lat'],
            'lng' => $_SESSION['user_lng'],
            'radius' => $_SESSION['user_radius'] ?? 40
        ];
    }
    
    // Then check database
    $stmt = $conn->prepare("SELECT latitude, longitude, location_radius_km FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && !empty($user['latitude']) && !empty($user['longitude'])) {
        return [
            'lat' => $user['latitude'],
            'lng' => $user['longitude'],
            'radius' => $user['location_radius_km'] ?? 40
        ];
    }
    
    return [
        'lat' => null,
        'lng' => null,
        'radius' => 40
    ];
}

/**
 * Update user's location
 */
function updateUserLocation($conn, $user_id, $lat, $lng, $radius = 40) {
    $stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ?, location_updated_at = NOW(), location_radius_km = ? WHERE user_id = ?");
    $stmt->bind_param("ssii", $lat, $lng, $radius, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $_SESSION['user_lat'] = $lat;
        $_SESSION['user_lng'] = $lng;
        $_SESSION['user_radius'] = $radius;
    }
    
    return $result;
}

/**
 * Filter query with location radius
 * Returns SQL WHERE clause for location filtering
 */
function getLocationFilterSQL($user_lat, $user_lng, $table_alias, $lat_column = 'latitude', $lng_column = 'longitude', $radius_km = 40) {
    if (empty($user_lat) || empty($user_lng)) {
        return " 1=1 "; // No filter if no location
    }
    
    $user_lat = floatval($user_lat);
    $user_lng = floatval($user_lng);
    $radius_km = intval($radius_km);
    
    // Haversine formula in SQL
    return " (
        6371 * acos( 
            cos( radians($user_lat) ) 
            * cos( radians( $table_alias.$lat_column ) ) 
            * cos( radians( $table_alias.$lng_column ) - radians($user_lng) ) 
            + sin( radians($user_lat) ) 
            * sin( radians( $table_alias.$lat_column ) ) 
        ) <= $radius_km
        AND $table_alias.$lat_column IS NOT NULL 
        AND $table_alias.$lat_column != ''
        AND $table_alias.$lng_column IS NOT NULL
        AND $table_alias.$lng_column != ''
    ) ";
}

/**
 * Check if user has location set, redirect if not
 */
function requireUserLocation($conn, $user_id) {
    $location = getUserLocation($conn, $user_id);
    if (empty($location['lat']) || empty($location['lng'])) {
        header('Location: location_setup.php');
        exit();
    }
    return $location;
}

/**
 * Get address from coordinates (Reverse Geocoding)
 */
function getAddressFromCoordinates($lat, $lng) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=10&addressdetails=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZeroHungerApp/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['display_name'])) {
        return $data['display_name'];
    }
    
    $city = $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? '';
    $state = $data['address']['state'] ?? '';
    $country = $data['address']['country'] ?? '';
    
    if ($city && $state) return "$city, $state, $country";
    if ($city) return $city;
    return "$lat, $lng";
}
?>