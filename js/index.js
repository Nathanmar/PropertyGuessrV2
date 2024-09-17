am5.ready(function() {
    
    var root = am5.Root.new("chartdiv");
    
    root.setThemes([
      am5themes_Animated.new(root)
    ]);
    
    var chart = root.container.children.push(am5map.MapChart.new(root, {
      panX: "translateX",
      panY: "translateY",
      projection: am5map.geoMercator()
    }));
    
    var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
      geoJSON: am5geodata_worldLow,
      exclude: ["AQ"]
    }));
    
    // Définir la couleur de tous les pays par défaut
    polygonSeries.mapPolygons.template.setAll({
      fill: am5.color(0x333333), // Gris foncé
      tooltipText: "{name}",
      toggleKey: "active",
      interactive: true
    });
    
    // Définir la couleur pour le survol
    polygonSeries.mapPolygons.template.states.create("hover", {
      fill: root.interfaceColors.get("primaryButtonHover")
    });
    
    // Définir la couleur pour l'état actif
    polygonSeries.mapPolygons.template.states.create("active", {
      fill: root.interfaceColors.get("primaryButtonHover")
    });
    
    // Changer la couleur de la France
    var francePolygon = polygonSeries.getDataItemById("FR"); // Assurez-vous que l'ID de la France est correct
    if (francePolygon) {
        francePolygon.mapPolygons.template.setAll({
            fill: am5.color(0x0000FF) // Bleu
        });
    }
    
    var previousPolygon;
    
    polygonSeries.mapPolygons.template.on("active", function (active, target) {
      if (previousPolygon && previousPolygon != target) {
        previousPolygon.set("active", false);
      }
      if (target.get("active")) {
        polygonSeries.zoomToDataItem(target.dataItem );
      }
      else {
        chart.goHome();
      }
      previousPolygon = target;
    });
    
    var zoomControl = chart.set("zoomControl", am5map.ZoomControl.new(root, {}));
    zoomControl.homeButton.set("visible", true);
    
    chart.chartContainer.get("background").events.on("click", function () {
      chart.goHome();
    })
    
    chart.appear(1000, 100);
    
});
