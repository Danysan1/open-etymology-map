# Contributing to Open Etymology Map

## How to contribute to the background map

The background maps are provided by Mapbox and Maptiler, which are based on OpenStreetMap. You can improve the map on [openstreetmap.org](https://www.openstreetmap.org/).
You can learn how to map on [the official welcome page](https://www.openstreetmap.org/welcome) and on [LearnOSM](https://learnosm.org/). Keep in mind that they doen't update the map immediately so if you edit something on OpenStreetMap it may take some time to appear in the map.

## How to report a problem in the etymology of an element

If the etymology associated to the element is correct but there is a problem in the details (birth date, nationality, ...):

1. From the etymology window click on the "Wikidata" button for the incorrect etymology
2. At the top of the opened page click on "Discussion"
3. Append in the opened text box the description of the problem you found in the data
4. Confirm your comment by clicking on the blue button below

If the problem is related to the etymology itself (a wrong etymology is associated to the element):

1. From the etymology window click on the "OpenStreetMap" button
2. On the left of the opened page check if the `name:etymology:wikidata`, `subject:wikidata` tag is present. If it is, click on the dialog button on the right to add a note to the map and describe the problem
3. If the tags above are absent, the `wikidata` tag will be present and its value will be clickable. Click on it.
   - If the opened page represents the element from the map (not its etymology, not something else), it should contain a "named after" or "dedicated to" relation to the wrong item:
     1. At the top of the opened page click on "Discussion"
     2. Append in the opened text box the description of the problem you found in the etymology for the item
     3. Confirm your comment by clicking on the blue button below
   - If instead the opened page represents something else, go back to the OpenStreetMap page, click on the button on the right to add a note to the map and write that the `wikidata` tag points to the wrong item

## How to contribute to the etymology data

Open Etymology Map gets the etymology of elements on the map from [OpenStreetMap](https://www.openstreetmap.org/welcome) and information about the etymology subjects from [Wikidata](https://www.wikidata.org/wiki/Wikidata:Introduction).

Some tools make it easy to contribute to OpenStreetMap by linking etymology data:

- https://mapcomplete.osm.be/etymology helps discovering missing `name:etymology:wikidata` tags and find their possible value
- https://osm.wikidata.link/ helps discovering missing `wikidata` tags and find their possible value

If those tools aren't enough for your needs and you want to manually add or correct the etymology of an element you can do it on [openstreetmap.org](https://www.openstreetmap.org/).
You can learn how to map on [the official welcome page](https://www.openstreetmap.org/welcome) and on [LearnOSM](https://learnosm.org/).

The wikidata ID of an item (object/person/...) can be found by searching its name on [wikidata.org](https://www.wikidata.org/wiki/Wikidata:Main_Page), once the subject will be opened its alphanumeric ID will be both on the right of the title and in the URL.
Suppose for example that you want to tag something named after Nelson Mandela: after searching it on wikidata you will find it's page at https://www.wikidata.org/wiki/Q8023 . As can be seen from the URL, it's ID is `Q8023`.

Open Etymology Map obtains the etymology data from multiple tags:

| Platform      | Property/Key              | Description                                                                                                        | Other info                                                                                 |
| ------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------ |
| OpenStreetMap | `wikidata`                | The ID of the Wikidata item about the feature (for example, Q9141 represents the way Taj Mahal)                    | [Documentation](https://wiki.openstreetmap.org/wiki/Key:wikidata)                          |
| OpenStreetMap | `name:etymology:wikidata` | It contains the ID of the Wikidata item for the feature's namesake                                                 | [Documentation](https://wiki.openstreetmap.org/wiki/Key:name:etymology:wikidata)           |
| OpenStreetMap | `subject:wikidata`        | It contains the ID of the Wikidata item for the event, person or thing that is memorialized in a monument/memorial | [Documentation](https://wiki.openstreetmap.org/wiki/Key:subject)                           |
| Wikidata      | `P138` ("named after")    | Entity or event that inspired the subject's name, or namesake (in at least one language)                           | [Info](https://www.wikidata.org/wiki/Property:P138)                                        |
| Wikidata      | `P547` ("commemorates")   | What the place, monument, memorial, or holiday, commemorates                                                       | [Info](https://www.wikidata.org/wiki/Property:P547)                                        |
| Wikidata      | `P825` ("dedicated to")   | Person or organization to whom the subject was dedicated                                                           | [Info](https://www.wikidata.org/wiki/Property:P825)                                        |

This project is based on the OSM-Wikidata Map Framework, the following image illustrates the complete flux in the framework (but may contain also tags or properties not used by Open Etymology Map):
![Tags and properties used by Open Etymology Map](osm-wikidata-map-framework-etymology/images/tags.svg).

In order to display the etymology of an element you need to create one of these combinations. Here's how to do it:

1. Find the element of interest on [OpenStreetMap](https://www.openstreetmap.org/)
2. Check out the element's tags:
   - If the element has a `name:etymology:wikidata` or `subject:wikidata` tag and two weeks have passed from their addition, then the element should already be available on Open Etymology Map.
     - If one of these tags is present and the time period has passed but the element isn't available on OEM, then the tag value may contain an error (like not being a valid Wikidata ID).
     - If one of these tags is available but liks to the wrong etymology/subject, search on Wikidata the ID for the correct etymology/subject and edit the incorrect tag with the new ID.
   - If the element has a `wikidata` tag check the referenced Wikidata element.
     - If it does not represent the same real world object of the OSM element, search the correct one and change it.
     - If it contains a `P138` ("named after"), `P547` ("commemorates") or `P825` ("dedicated to") relation check that it links to the correct etymology. If it is absent, add it:
       1. Click "+ Add statement"
       2. On the left choose `P138`, `P547` or `P825` (depending on which is more appropriate) as property
       3. On the right search the desired etymology to use as the value
   - If none of these tags is present, you can link the Wikidata item for the etymology to the element
     1. Search the etymology on Wikidata
     2. If the Wikidata element for the etymology is not available you can create it [on this Wikidata page](https://www.wikidata.org/wiki/Special:NewItem) using the instructions on that page.
     3. Add to the OpenStreetMap element the `name:etymology:wikidata` or `subject:wikidata` tag (depending on the meaning of the etymology) with the Wikidata ID as value. Using the example above, if you want to state an element is named after Nelson Mandela you will need to add the tag `name:etymology:wikidata`=`Q8023`.

## How to contribute to Open Etymology Map

Any suggestion to improve this documentation page is really appreciated, as it helps more newcomers to contribute to the map and more in general to the OSM and Wikidata projects. You can edit it and open a merge request or you can [open a new issue](https://gitlab.com/openetymologymap/open-etymology-map/-/issues/new) describing your suggestion.

This application is based on [OSM-Wikidata Map Framework](https://gitlab.com/openetymologymap/osm-wikidata-map-framework/), you can find some information useful to contribute to the codebase in [its CONTRIBUTING.md](https://gitlab.com/openetymologymap/osm-wikidata-map-framework/-/blob/main/CONTRIBUTING.md).