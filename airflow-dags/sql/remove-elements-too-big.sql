DELETE FROM oem.osmdata
WHERE (osm_tags ? 'name:etymology:wikidata' AND osm_tags ? 'wikidata' AND osm_tags->>'name:etymology:wikidata' = osm_tags->>'wikidata')
OR ST_Area(osm_geometry) >= 0.01 -- EPSG 4326 => 0.01 square degrees
