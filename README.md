To install:

Add to your LocalSettings.php:

    require_once("$IP/extensions/WikiMaps/WikiMaps.php");

Make a map by visiting any page in the map namespace e.g. Map:Example

Click edit to drop pins and construct shapes. Edits will save automatically and will have a revision history!

Embed a map or many maps in your wiki and use class attribute to style.

    <map title="Map:Example" class="my class"></map>

# Background
The idea is to centralize functionality and representation of maps in Wikipedia and related projects. Insipiration for the current starting point is derived from the maps embedded in wikivoyage.org https://en.wikivoyage.org/w/index.php?title=Zurich#Get_around
This map and many similar maps like it are defined by using a template: In this case: {{Mapframe|47.36889|8.54796|zoom=14|layer=M|align=none|height=600|width=800}}. WikiMaps centralizes the information contained in the template parameters and basically give it a name within a seperate namespace. The current information uses GeoJSON stored in a seperate contentmodel. By having an editor you can change the definition and thus the embedded result.

# Overlap
There are many existing map related extensions and all have their usages, but this tries to bring some of those ideas together at a new point that we had not really explored before.

# Future
Future directions to explore might be defining which layers to use for the representation of the map (including raster images or historic scanned maps with their bounding boxes). A layer might also be relations from OSM or WikiData or coordinate polygons from WikiData. WikiData entities could be linked to the coordinate information. etc. This might require something more complext than the current geojson, but likely still JSON based.
