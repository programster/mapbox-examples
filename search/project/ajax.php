<?php

require_once(__DIR__ . '/vendor/autoload.php');
header('Content-Type: application/json');

function main()
{
    $polygons = $_POST['polygons'];
    

    if (!is_array($polygons))
    {
        throw new Exceptioin("polygons was not an array.");
    }
    
    // Connect to the database.
    $conn = r\connect('localhost');

    $accident_info = array();
    
    foreach ($polygons as $polygon)
    {
        if (!is_array($polygon))
        {
            throw new Exceptioin("each polygon needs to be an array");
        }

        foreach ($polygon as &$point)
        {
            if (!is_array($point))
            {
                throw new Exceptioin("Each point within a polygon needs to also be an array");
            }
            else 
            {
                // convert from string to float.
                $point[0] = floatval($point[0]);
                $point[1] = floatval($point[1]);
            }
        }
        
        $r_polygon = r\polygon($polygon);
    
        try
        {
            $docs = r\table('tablePhpTest')->getIntersecting($r_polygon, array('index' => 'location'))->run($conn);
                        
            foreach ($docs as $doc)
            {
                $accident_location = $doc['location']['coordinates'];
                
                $accident_info[] = array(
                    'location'       => $accident_location,
                    'casualty_count' => $doc['casualty_count']
                );
            }
            
        } 
        catch (Exception $ex) 
        {
            print $ex->getMessage();
        }
    }
    
    
    print json_encode($accident_info, JSON_UNESCAPED_SLASHES);
}


main();
