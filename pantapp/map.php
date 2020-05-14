<!DOCTYPE html>
        <html>
            <head>
                <title>GeoJSON Import</title>
                <meta charset="utf-8">
                <script src="https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js"></script>
                <style>
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: "-apple-system-font", "HelveticaNeue-Medium", "Helvetica", "Arial", "sans-serif";
        }
        
        .container {
            position: relative;
        }
        
        #map {
            width: 100%;
            max-width: 600px;
            height: 600px;
        }
        
        #infoPopup {
            display: none;
            top: 7px;
            left: 67px;
            background: #FFFFFF;
            padding: 5px 15px;
            position: absolute;
            z-index: 1000;
            min-width:180px;
            font: 13px/16px "-apple-system-font", "HelveticaNeue-Medium", "Helvetica", "Arial", "sans-serif";
            color: #212121;
            border: 1px solid rgba(0,0,0,0.2);
            border-radius: 3px;
        }
        
        .container .map-legend {
            position: absolute;
            z-index: 1000;
            top: 7px;
            left: 7px;
        }
        
        .map-legend div {
            margin-bottom: 5px;
            width: 40px;
            font-size: 12px;
            color: #fff;
            padding: 4px 7px;
        }
        
        #infoPopup .info {
            padding: 10px 0;
            box-sizing: border-box;
        }
        
        #infoPopup .info:first-child {
            border-bottom: 1px solid rgba(0,0,0,0.2);
        }
        
        #infoPopup .info-country,  #infoPopup .info-population{
            margin-left: 5px;
            color: #464545;
            font-style: italic;
        }
        
                </style>
            </head>
            <body>
                <div class="container">
                    <div id="map"></div>
                    <div id="infoPopup">
                        <div class="info">
                            <span>State:</span>
                            <span class="info-country"></span>
                        </div>
                        <div class="info">
                            <span>Population:</span>
                            <span class="info-population"></span>
                        </div>
                    </div>
                    <div class="map-legend"></div>
                </div>
                <script>
        
        var LINE_WIDTH_DEFAULT = 0.5;
        var LINE_WIDTH_SELECTED = 3;
        var MAP_COLORS = [
            {
                color: "#fcc5c0",
                range: "< 1M",
                num: 1000000
            },
            {
                color: "#fa9fb5",
                range: "1M +",
                num: 5000000
            },
            {
                color: "#f768a1",
                range: "5M +",
                num: 10000000
            },
            {
                color: "#dd3497",
                range: "10M +",
                num: 20000000
            },
            {
                color: "#ae017e",
                range: "20M +",
                num: 30000000
            },
            {
                color: "#7a0177",
                range: "> 30M",
                num: Infinity
            }
        ];
        
        // Initialize mapkit and create a new map.
        mapkit.init({
            authorizationCallback: function(done) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "/services/jwt");
                xhr.addEventListener("load", function() {
                    done(this.responseText);
                });
                xhr.send();
            }
        });
        var map = new mapkit.Map("map", {
            isRotationEnabled: false,
            isZoomEnabled: false,
            showsZoomControl: false
        });
        
        // Setup the UI to show a popup when a state is selected.
        var infoPopup = document.getElementById("infoPopup");
        var infoCountry = infoPopup.querySelector(".info-country");
        var infoPopulation = infoPopup.querySelector(".info-population");
        var mapLegend = document.querySelector(".map-legend");
        
        function showInfo(data) {
            infoCountry.innerText = data.name;
            infoPopulation.innerText = data.population.toLocaleString();
            infoPopup.style.display = "block";
        }
        
        function closeInfo() {
            infoPopup.style.display = "none";
        }
        
        function addLegend() {
            var el, textNode;
            MAP_COLORS.forEach(function (mColor) {
                el = document.createElement("div");
                textNode = document.createTextNode(mColor.range);
                el.appendChild(textNode);
                el.style.background = mColor.color;
                mapLegend.appendChild(el);
            });
        }
        
        addLegend();
        
        // Import GeoJSON data with the shape of the states and their population.
        mapkit.importGeoJSON("states.json", {
        
            // Some states are represented as MultiPolygons; we transform them into
            // a single PolygonOverlay by concatenating the lists of lists of points.
            itemForMultiPolygon: function(collection, geoJSON) {
                var overlays = collection.getFlattenedItemList();
                var points = overlays.reduce(function(points, overlay) {
                    return points.concat(overlay.points);
                }, []);
                return new mapkit.PolygonOverlay(points);
            },
        
            // After an overlay has been created for a feature (either directly or through
            // itemForMultiPolygon above), the properties of the feature are used to add data
            // and set the style (especially the fill color) based on population count.
            itemForFeature: function(overlay, geoJSON) {
                var population = geoJSON.properties.population;
        
                // Add data to the overlay to be shown when it is selected.
                overlay.data = {
                    name: geoJSON.properties.name,
                    population: geoJSON.properties.population
                };
        
                // Find the right color for the population and the set the style.
                for (var i = 0; i < MAP_COLORS.length; ++i) {
                    if (population < MAP_COLORS[i].num) {
                        overlay.style = new mapkit.Style({
                            fillOpacity: 0.7,
                            lineWidth: LINE_WIDTH_DEFAULT,
                            fillColor: MAP_COLORS[i].color
                        });
                        break;
                    }
                }
                return overlay;
            },
        
            // When all the data has been imported, we can show the results.
            geoJSONDidComplete: function(overlays) {
                map.addItems(overlays);
                map.showItems(overlays.getFlattenedItemList().filter(function(overlay) {
                    // Focus the map on the continental states at first.
                    var name = overlay.data.name;
                    return name !== "Alaska" && name !== "Hawaii";
                }), {
                    // Leave some padding for the color key.
                    padding: new mapkit.Padding(0, 0, 0, 67)
                });
            }
        });
        
        // Show info about the selected state.
        map.addEventListener("select", function(event) {
            if (event.overlay && event.overlay.data) {
                event.overlay.style.lineWidth = LINE_WIDTH_SELECTED;
                showInfo(event.overlay.data);
            }
        });
        
        // Hide info when a state is deselected.
        map.addEventListener("deselect", function(event) {
            if (event.overlay) {
                event.overlay.style.lineWidth = LINE_WIDTH_DEFAULT;
                closeInfo();
            }
        });
        
                </script>
            </body>
        </html>