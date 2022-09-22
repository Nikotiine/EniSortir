
function init(){
    //Création d'un Tableau de Lieux
    let adminLocations = {
        "Manarii à Nantes" : { "lat" : 47.218102, "long":  -1.552800 },
        "Jordan à La Roche Sur Yon" : { "lat" : 46.670502, "long":  -1.426000 },
        "Nico à Grenoble" : { "lat" : 45.171547, "long":  5.722387 },
        "Christophe à Caen" : { "lat" : 49.1811, "long":  -0.3712 }
    }
    //Initialise d'abord le Cluster de la Librairie
    let markers = L.markerClusterGroup();
    //Création du tableau de Marqueurs après avoir Initialisé le Cluster de la Librairie
    let arrayMarkers = [];

    // Initialisation de la Carte
    let map = L.map('map').setView([49.1811 ,-0.3712], 10);

    //Chargement des Tuiles
    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        maxZoom: 20,
        minZoom: 1,
        attribution: '&copy; OpenStreetMap France | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    //on parcours le Tableau de Lieux adminLocations
    for (let adminLocation in adminLocations){
        //Personnalisation du Marqueur (il faut regler le décalage de l icone par rapport au bord avec iconAnchor
        // et la Popup avec popupAnchor
        let icon = L.icon({
            iconUrl : '/build/images/icon.png',
            iconSize : [50,50],
            iconAnchor : [25,50],
            popupAnchor : [0,-50]
        })
        //Placement du Marqueur
        let marker = L.marker([adminLocations[adminLocation].lat,adminLocations[adminLocation].long], {icon:icon});//.addTo(map); plus besoin quand on utilise les Clusters
        //Attribution de son Popup
        marker.bindPopup("<p>"+adminLocation+"</p>");
        //Attribution de chaque Marqueur dans les groupe des Marqueurs
        markers.addLayer(marker);
        //Ajout du Marqueur au tableau de Marqueur
        arrayMarkers.push(marker);
    }
    //Attribution du tableau de Marqueurs à un groupe Leaflet avec la methode FeaturesGroup
    let groupMarkers = new L.featureGroup(arrayMarkers);

    //Adaptation du Zoom au groupe de Marqueurs
    map.fitBounds(groupMarkers.getBounds().pad(0.5));

    //Attribution du Groupe Marqueurs à la carte
    map.addLayer(markers);
//Création d'une zone en km Si Tableau de Zone il faut créer aussi une zoneIndex pour savoir quelle zone prendre (voir video)
    const zone = {distance : 10 , color : "#00b798"}

    //Attribution d'un marqueur et d'un cercle pour la zone
    let userMarker ;
    let userCircle;
    //Methode pour rajouter un marqueur
    function addUserMarker(latlng){
        userMarker = L.marker(latlng, {draggable : true}).addTo(map);
        userMarker.on("dragstart", removeUserCircle);
        userMarker.on("dragend", addUserCircle)
    }
    //Méthode pour enlever le marqueur
    function removeMarker(){
        userMarker.remove();
        userMarker = null;
    }
    //Méthode pour rajouter le cercle
    function addUserCircle(){
        userCircle = L.circle(userMarker.getLatLng(),{
            color: zone.color,
            fillColor:zone.color,
            fillOpacity:0.15,
            radius : zone.distance * 1000,
        }).addTo(map)
    }
    //Méthode pour supprimer le cercle
    function removeUserCircle(){
        userCircle.remove();
        userCircle = null;
    }
    //Geolocaliser le User Connecté
    if ("geolocation" in navigator ){
        navigator.geolocation.getCurrentPosition((position) => {
            const latlng = [position.coords.latitude,position.coords.longitude];
            map.panTo(latlng);
            addUserMarker(latlng);
            addUserCircle(latlng)
            userMarker.bindPopup("<p>Je suis là</p>");
        })
    }
    //Méthode pour placer un marqeur manuellement avec un cercle
    map.on("click", (e)=>{
        if (!userMarker){
            addUserMarker(e.latlng);
            addUserCircle();
        }else {
            removeUserCircle();
            userMarker.setLatLng(e.latlng);
            addUserCircle();
        }
    })
}
init()

