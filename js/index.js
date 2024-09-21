am5.ready(function() {

  var root = am5.Root.new("chartdiv");

  root.setThemes([
    am5themes_Animated.new(root)
  ]);

  // Créer la carte avec la projection
  var chart = root.container.children.push(am5map.MapChart.new(root, {
    panX: "none", // Désactiver le déplacement horizontal
    panY: "none", // Désactiver le déplacement vertical
    projection: am5map.geoMercator()
  }));

  // Ajouter un fond bleu clair (représentant la mer)
  var backgroundSeries = chart.series.unshift(am5map.MapPolygonSeries.new(root, {}));
  
  backgroundSeries.mapPolygons.template.setAll({
    fill: am5.color(0xd4efff), // Bleu clair pour la mer
    stroke: am5.color(0xd4efff) // Bordure du même bleu pour la mer
  });

  backgroundSeries.data.push({
    geometry: am5map.getGeoRectangle(90, 180, -60, -180) // Couvrir tout le globe
  });

  // Créer la série des pays
  var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
    geoJSON: am5geodata_worldLow,
    exclude: ["AQ"]
  }));

  // Définir la couleur de tous les pays par défaut (gris foncé)
  polygonSeries.mapPolygons.template.setAll({
    fill: am5.color(0x333333), // Gris foncé
    tooltipText: "{name}", // Texte par défaut du tooltip
    toggleKey: "active",
    interactive: true
  });

  // Définir la couleur pour le survol des autres pays (rouge)
  polygonSeries.mapPolygons.template.states.create("hover", {
    fill: am5.color(0x841D1D) // Rouge pour le survol
  });

  // Modifier la couleur de la France
  polygonSeries.events.on("datavalidated", function() {
    polygonSeries.mapPolygons.each(function(polygon) {
      if (polygon.dataItem.get("id") === "FR") {
        // Mettre la France en bleu
        polygon.set("fill", am5.color(0x0052D9)); // Bleu pour la France

        // Empêcher la France de changer de couleur au survol
        polygon.states.create("hover", {
          fill: am5.color(0x0052D9) // Bleu au survol aussi
        });
      }
    });
  });

  // Modifier le tooltip pour afficher un message personnalisé au survol
  polygonSeries.mapPolygons.template.events.on("pointerover", function(event) {
    var polygon = event.target;
    var id = polygon.dataItem.get("id");
    if (id !== "FR") {
      // Afficher le message personnalisé pour les autres pays
      polygon.set("tooltipText", "Pays non disponible pour l'instant");
    } else {
      // Afficher le texte par défaut pour la France
      polygon.set("tooltipText", "{name}");
    }
  });

  polygonSeries.mapPolygons.template.events.on("pointerout", function(event) {
    var polygon = event.target;
    // Réinitialiser le texte du tooltip lorsque la souris quitte l'élément
    polygon.set("tooltipText", "{name}");
  });

  var previousPolygon;

  // Gestion du clic uniquement pour la France, sans zoom
  polygonSeries.mapPolygons.template.events.on("click", function (event) {
    var target = event.target;

    // Vérifier si c'est la France (ID "FR")
    if (target.dataItem.get("id") === "FR") {
      if (previousPolygon && previousPolygon != target) {
        previousPolygon.set("active", false);
      }

      previousPolygon = target;

      // Déclencher l'animation du cercle
      setTimeout(triggerCircleAnimation, 500);
    }
  });

  // Fonction pour déclencher l'animation du cercle
  function triggerCircleAnimation() {
    var overlay = document.createElement('div');
    overlay.classList.add('circle-overlay');
    document.body.appendChild(overlay);

    // Ajouter la classe pour démarrer l'animation
    setTimeout(function() {
      overlay.classList.add('expand');
    }, 10);

    // Rediriger vers la page map.html
    setTimeout(function() {
      window.location.href = "map.html"; // Redirige vers map.html
    }, 1500); // 1.5s d'animation
  }

  var zoomControl = chart.set("zoomControl", am5map.ZoomControl.new(root, {}));
  zoomControl.homeButton.set("visible", true);

  chart.chartContainer.get("background").events.on("click", function () {
    chart.goHome();
  });

  chart.appear(1000, 100);
});