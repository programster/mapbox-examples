<?php
# To perform geographical searches in our rethinkdb, we need to parse the json data and insert in a 
# special manner so lat/long are converted into points.

require_once (__DIR__ . '/vendor/autoload.php');


function main()
{
    $json_data = file_get_contents(__DIR__ . '/../converted.json');

    $accidents = json_decode($json_data, $assoc=true);

    /*
     * accidents is array of objects with following properties
    Accident_Index
    Location_Easting_OSGR
    Location_Northing_OSGR
    Longitude
    Latitude
    Police_Force
    Accident_Severity
    Number_of_Vehicles
    Number_of_Casualties
    Date
    Day_of_Week
    Time
    Local_Authority_(District)
    Local_Authority_(Highway)
    1st_Road_Class
    1st_Road_Number
    Road_Type
    Speed_limit
    Junction_Detail
    Junction_Control
    2nd_Road_Class
    2nd_Road_Number
    Pedestrian_Crossing-Human_Control
    Pedestrian_Crossing-Physical_Facilities
    Light_Conditions
    Weather_Conditions=
    Road_Surface_Conditions
    Special_Conditions_at_Site
    Carriageway_Hazards
    Urban_or_Rural_Area
    Did_Police_Officer_Attend_Scene_of_Accident
    LSOA_of_Accident_Location

    */

     // Connect to localhost
    $conn = r\connect('rethinkdb.programster.org');

    // Create a test table
    r\db("test")->tableCreate("tablePhpTest")->run($conn);

    foreach ($accidents as $accident)
    {
        $point = r\point(
            floatval($accident['Latitude']), 
            floatval($accident['Longitude'])
        );
        
        // Insert a document
        $document = array(
            'id'                        => $accident['Accident_Index'],
            'location_easting_OSGR'     => $accident['Location_Easting_OSGR'],
            'location_northing_OSGR'    => $accident['Location_Northing_OSGR'],
            'police_force'              => $accident['Police_Force'],
            'severity'                  => $accident['Accident_Severity'],
            'vehicle_count'             => $accident['Number_of_Vehicles'],
            'casualty_count'            => $accident['Number_of_Casualties'],
            'date'                      => $accident['Date'],
            'day_of_week'               => $accident['Day_of_Week'],
            'time'                      => $accident['Time'],
            'local_authority_district'  => $accident['Local_Authority_(District)'],
            'local_authority_highway'   => $accident['Local_Authority_(Highway)'],
            '1st_Road_Class'            => $accident['1st_Road_Class'],
            '1st_Road_Number'           => $accident['1st_Road_Number'],
            'road_type'                 => $accident['Road_Type'],
            'speed_limit'               => $accident['Speed_limit'],
            'junction_Detail'           => $accident['Junction_Detail'],
            'junction_control'          => $accident['Junction_Control'],
            '2nd_Road_Class'            => $accident['2nd_Road_Class'],
            '2nd_Road_Number'           => $accident['2nd_Road_Number'],
            'pedestrian_crossing_human_control'         => $accident['Pedestrian_Crossing-Human_Control'],
            'pedestrian_crossing_physical_facilities'   => $accident['Pedestrian_Crossing-Physical_Facilities'],
            'light_conditions'              => $accident['Light_Conditions'],
            'weather_conditions'            => $accident['Weather_Conditions'],
            'road_surface_conditions'       => $accident['Road_Surface_Conditions'],
            'site_special_conditions'       => $accident['Special_Conditions_at_Site'],
            'carriagewy_hazards'            => $accident['Carriageway_Hazards'],
            'area_type'                     => $accident['Urban_or_Rural_Area'],
            'police_attendance'             => $accident['Did_Police_Officer_Attend_Scene_of_Accident'],
            'LSOA_of_accident_location'     => $accident['LSOA_of_Accident_Location'],
            'location'                      => $point

        );

        $result = r\table("tablePhpTest")->insert($document)->run($conn);
        
        # after that's run the following to turn location into a geospatial index for queries.
        # r.table('tablePhpTest').indexCreate('location', {geo:true})
        # on the server because we forgot to add it to the code.
    }

}

main();