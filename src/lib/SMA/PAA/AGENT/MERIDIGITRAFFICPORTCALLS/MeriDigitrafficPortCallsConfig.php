<?php

return array(
    "apikey" => "",
    "serviceurl" => "https://meri.digitraffic.fi/api/v1/port-calls/",
    "locationcode" => "FIRAU",
    "offsetminutes" => "1440",
    "parametermappings" => ["imoLloyds" => "imo", "vesselName" => "vessel_name"],
    "payloadmappings" => [
        "prevPort" => "from_port",
        "portToVisit" => "to_port",
        "nextPort" => "next_port",
        "nationality" => "nationality",
        "radioCallSign" => "call_sign",
        "mmsi" => "mmsi",
        "portCallId" => "external_id"
    ],
    "timestampmappings" => [
        "eta" => ["time_type" => "Estimated", "state" => "Arrival_Vessel_PortArea"],
        "ata" => ["time_type" => "Actual", "state" => "Arrival_Vessel_Berth"],
        "etd" => ["time_type" => "Estimated", "state" => "Departure_Vessel_Berth"],
        "atd" => ["time_type" => "Actual", "state" => "Departure_Vessel_Berth"]
    ]
);
