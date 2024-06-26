<x-app-layout>  

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="flex justify-between p-2">
                    <div class="text-2xl">
                        <h1>Selected Year: {{$yearSpot->year}}</h1>
                    </div>
                </div>
                <div id="map" style="height: 500px;" class="z-10"></div>
                <div class="w-full h-16 p-4 flex gap-4 items-center">
                    <div class="flex justify-center items-center gap-2">
                        <div class="bg-[#65B741] h-4 w-4"></div>
                        <p>High fertility</p>
                    </div>
                    <div class="flex justify-center items-center gap-2">
                        <div class="bg-[#FFFEC4] h-4 w-4"></div>
                        <p>Medium fertility</p>
                    </div>
                    <div class="flex justify-center items-center gap-2">
                        <div class="bg-[#FFCF81] h-4 w-4"></div>
                        <p>Low fertility</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- {{-- Load cdn js LeafletJS --}} -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- {{-- Load cdn js Leaflet-Search --}} -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet-search@3.0.9/dist/leaflet-search.src.min.js"></script>

    <!-- {{-- Load cdn js Leaflet fullscreen --}} -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@2.4.0/Control.FullScreen.min.js"></script>

    <script>
        var mbAttr = 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, ' +
            'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            mbUrl =
            'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiZXJpcHJhdGFtYSIsImEiOiJjbDY5OGJkajkwcHliM2xwMzdwYzZ0MjNqIn0.yRMI7Q02u6qldbDGRypgQQ';

        // Setup map
        var satellite = L.tileLayer(mbUrl, {
                id: 'mapbox/satellite-v9',
                tileSize: 512,
                zoomOffset: -1,
                attribution: mbAttr
            }),
            dark = L.tileLayer(mbUrl, {
                id: 'mapbox/dark-v10',
                tileSize: 512,
                zoomOffset: -1,
                attribution: mbAttr
            }),
            streets = L.tileLayer(mbUrl, {
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1,
                attribution: mbAttr
            });


            var map = L.map('map', {
                center: [-0.18353765071211733, 116.30192451474325],
                zoom: 5,
                layers: [satellite],
                fullscreenControl: {
                    pseudoFullscreen: false
                }
            });

            // Initiation baselayer
            var baseLayers = {
                "Streets": streets,
                "Satellite": satellite,
                "Dark": dark,
            };

            L.control.layers(baseLayers).addTo(map);

            // Looping data coordinates pada tabel spot
            var dataSearch = [
                @foreach ($spot as $key => $value)
                    {!! $value->coordinates !!},
                @endforeach
            ]

            // Initiation layergroup and adding search feature
            var markersLayer = new L.LayerGroup()
            map.addLayer(markersLayer)
            var searchControl = new L.Control.Search({
                layer: markersLayer,
                propertyName: 'name',
                zoom: 15
            })
            map.addControl(searchControl);

            // Looping variable dataSearch then push to geoJSON object
            for (i in dataSearch) {
                var coords = dataSearch,
                    marker = L.geoJSON(coords)
                markersLayer.addLayer(marker)

                //Looping data spot table and add to map
                @foreach ($spot as $data)
                    @foreach ($data->getYear as $itemYear)
                        L.geoJSON({!! $data->coordinates !!}, {
                                style: {
                                    color: '{{ $data->fillColor }}',
                                    fillColor: '{{ $data->fillColor }}',
                                    fillOpacity: 0.8,
                                },
                            })
                            .bindPopup("<div class='my-2'><strong>Nama Lokasi:</strong> <br>{{ $data->name }}</div>" +
                                "<div class='my-2'><strong>Tahun:</strong> <br>{{ $itemYear->year }}</div>" +
                                "<div class='my-2'><strong>Deskripsi Lokasi:</strong> <br>{{ $data->description }}</div>" 
                            ).addTo(map);
                    @endforeach
                @endforeach
            }
    </script>
</x-app-layout>


