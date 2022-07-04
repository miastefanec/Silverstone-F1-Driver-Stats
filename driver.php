<!DOCTYPE html>
<meta charset="utf-8">
<link rel="stylesheet" href="styles/driver.css"></link>
<style>
@import url('https://fonts.googleapis.com/css2?family=Titillium+Web&display=swap');
</style>
<body>
    <script src="//d3js.org/d3.v3.min.js"></script>
    <header><a href="index.php"><img id="home"src="images/f1.png"></a></header>
    <script>

        //učitavanje potrebnih podataka preko api-ja te postavljanje imena i prezimena u header i h1 element
        var driver = document.location.href.split('id=')[1];
        d3.json("https://ergast.com/api/f1/drivers/" + driver + ".json", function (error, arr) {
            var name = arr["MRData"]["DriverTable"]["Drivers"][0]["givenName"];
            var surname = arr["MRData"]["DriverTable"]["Drivers"][0]["familyName"];
            document.title = name + " " + surname;
            d3.select("body").select("header")
                .append("h1")
                .text(name + " " + surname);
        });

        d3.select("body").select("header").append("img").attr("id","pic").attr("src","images/race.png");

        //učitavanje podataka povezanih sa utrkama u silverstonu te spremanje u varijable participations, podiums,wins, points i polje rezultata       
        d3.json("https://ergast.com/api/f1/drivers/" + driver + "/circuits/silverstone/results.json", function (error, arr) {
            console.log(arr);
            var participations = 0, podiums = 0, wins = 0, points = 0, results = [];
            for(var race of arr["MRData"]["RaceTable"]["Races"]){
                raceResult = race["Results"][0];
                if(raceResult["positionText"] == "1"){
                    wins += 1;
                }
                if(parseInt(raceResult["position"], 10) <= 3){
                    podiums += 1;
                }
                points += parseFloat(raceResult["points"], 10);
                participations += 1;
                results.push({season: parseInt(race["season"], 10), result: parseInt(raceResult["position"], 10)});
            }

            //implementacija kružnih dijagrama koji prikazuju podatke o pobjedama, postoljima i sudjelovanjima u utrci kao i broju osvojenih bodova te koliko se moglo osvojiti
            d3.select("body").append("div").attr("id","piecharts");
            var widthB = 450, heightB = 450, marginB = 40;
            
            //ako je polje prazno, umjesto izrade kružnog grafa ispisuje se poruka "NO DATA AVAILABLE"
            if(results.length==0){  
                d3.select("#piecharts").append("p").attr("class","nonp").text("NO DATA AVAILABLE")
            }

            //kružni dijagrami su izrađeni pomoću izvora https://d3-graph-gallery.com/pie.html
            var radius = Math.min(widthB, heightB) / 2 - marginB;
            var svgB = d3.select("#piecharts").append("svg")
                .attr("width", widthB)
                .attr("height", heightB)
                .append("g")
                .attr("transform", "translate(" + widthB / 2 + "," + heightB / 2 + ")");

            var ratioWins = {Wins: wins, Podiums: podiums, Participations: participations}

            var colors=d3.scale.ordinal().range(["#C71F37","#3C91E6","#A1C349"]);

            var pie = d3.layout.pie()
                .value(function(d) {return d.value; })
            var data_ready = pie(d3.entries(ratioWins))

            var arcGenerator = d3.svg.arc()
            .innerRadius(0)
            .outerRadius(radius)

            // ako postoje podaci unutar polja results, nastavlja se sa implementacijom kružnog grafa
            if(results.length>0){
            svgB
            .selectAll('mySlices')
            .data(data_ready)
            .enter()
            .append('path')
                .attr('d', arcGenerator)
                .attr('fill', function(d,i){ return colors(i) })
                .attr("stroke", "white")
                .style("stroke-width", "2px")

            svgB
            .selectAll('mySlices')
            .data(data_ready)
            .enter()
            .append('text')
            .text(function(d){ if (d.data.value > 0) {return d.data.key+":"+ " "+ d.data.value}})
            .attr("transform", function(d) { return "translate(" + arcGenerator.centroid(d) + ")";  })
            .style("text-anchor", "middle")
            .style("font-size", 20)
            .style('font-weight','bold');


            var svgC = d3.select("#piecharts").append("svg")
                .attr("width", widthB)
                .attr("height", heightB)
                .append("g")
                .attr("transform", "translate(" + widthB / 2 + "," + heightB / 2 + ")");
            var ratioPoints = {"Points Won": points, "Possible Points": participations*25}
            var colors1=d3.scale.ordinal().range(["#C9184A","#A1C349"]);
            var pie1 = d3.layout.pie()
                .value(function(d) {return d.value; })
            var data_ready1 = pie(d3.entries(ratioPoints))

            var arcGenerator1 = d3.svg.arc()
            .innerRadius(0)
            .outerRadius(radius)

            svgC
            .selectAll('mySlices')
            .data(data_ready1)
            .enter()
            .append('path')
                .attr('d', arcGenerator1)
                .attr('fill', function(d,i){ return colors1(i) })
                .attr("stroke", "white")
                .style("stroke-width", "2px")

            svgC
            .selectAll('mySlices')
            .data(data_ready1)
            .enter()
            .append('text')
            .text(function(d){ return d.data.key+":"+ " "+ d.data.value})
            .attr("transform", function(d) { return "translate(" + arcGenerator1.centroid(d) + ")";  })
            .style("text-anchor", "middle")
            .style("font-size", 20)
            .style('font-weight','bold');
            }

            //inicijalizacija i implementacija varijabli potrebnih za izradu linijskog grafa
            var margin = { top: 30, right: 60, bottom: 50, left: 100 },
                width =1060 - margin.left - margin.right,
                height = 500 - margin.top - margin.bottom;

            var x = d3.scale.linear()
                    .range([0, width]);

            var y = d3.scale.linear()
                    .range([height, 0]);

            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom")
                .tickFormat(d3.format("d"));

            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left")
                .tickFormat(d3.format("s"));

            var line = d3.svg.line()
                .x(function(d) { return x(d.season); })
                .y(function(d) { return y(d.result); });

            var svg = d3.select("body").append("div").attr("class","svgc").append("svg")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")")

            var tooltip = d3.select("body").append("div")
                .attr("class", "tooltip")
                .style("display", "none");

            results.sort(function(a, b) {
                return a.season - b.season;
            });

            //graf se iscrtava samo ako postoji više od 1 sudjelovanja
            if(results.length>0 && participations>1){
                x.domain([results[0].season, results[results.length - 1].season]);
                y.domain([d3.max(results, function (d) { return d.result }), d3.min(results, function (d) { return d.result })]);

                svg.append("g")
                    .attr("class", "x axis")
                    .attr("transform", "translate(0," + height + ")")
                    .call(xAxis)
                    .append("text")
                    .attr("text-anchor", "end")
                    .attr("x", width)
                    .attr("dy", "2.5em")
                    .text("Season");

                svg.append("g")
                    .attr("class", "y axis")
                    .call(yAxis)
                    .append("text")
                    .attr("transform", "rotate(-90)")
                    .attr("y", 6)
                    .attr("dy", ".71em")
                    .style("text-anchor", "end")
                    .text("Position");

                svg.append("path")
                    .datum(results)
                    .attr("class", "line")
                    .attr("d", line);

                var focus = svg.append("svg:image")
                    .attr("class", "focus")
                    .attr("xlink:href","images/icon.png")
                    .style("display", "none");

                var tooltipDate = tooltip.append("div")
                    .attr("class", "tooltip-season");

                var tooltipPosition = tooltip.append("div");
                tooltipPosition.append("span")
                    .attr("class", "tooltip-title")
                    .text("Position: ");

                var tooltipPositionValue = tooltipPosition.append("span")
                    .attr("class", "tooltip-position");

                svg.append("rect")
                    .attr("class", "overlay")
                    .attr("width", width)
                    .attr("height", height)
                    .on("mouseover", function() { focus.style("display", null); tooltip.style("display", null);  })
                    .on("mouseout", function() { focus.style("display", "none"); tooltip.style("display", "none"); })
                    .on("mousemove", mousemove);

                //funkcija koja omogućava da se prelaskom preko određenog rezultata pojavi box sa opisom kao i ikonica
                function mousemove() {
                    console.log(Math.ceil(x0) - results[0].season);
                    var x0 = x.invert(d3.mouse(this)[0]);
                        var prevSeason = 0;
                        var nxtSeason = 1
                        for (let [index, val] of results.entries()) {
                           if(x0 < results[index].season){
                                prevSeason =index - 1
                                nxtSeason = index;
                                break;
                           }
                        }
                        console.log(prevSeason);
                        d0 = results[prevSeason];
                        d1 = results[nxtSeason];
                        d = x0 - d0.season > d1.season - x0 ? d1 : d0;
                    focus.attr("transform", "translate(" + x(d.season) + "," + y(d.result) + ")");
                    tooltip.attr("style", "left:" + (x(d.season) + 64) + "px;top:" + (y(d.result) + 200) + "px;");
                    tooltip.select(".tooltip-season").text(d.season);
                    tooltip.select(".tooltip-position").text(d.result);
                }
            }
            
        });

        //implementacija funkcije za pretraživanje i prikaz rezultata
        var drivers = null;
        d3.json("https://ergast.com/api/f1/drivers.json?limit=1000", function (error, arr) {
                drivers= arr["MRData"]["DriverTable"]["Drivers"];
        });

        d3.select("body").append("div").attr("id","search").append("p").text("Search for a driver and see their stats:");
        d3.select("#search").append("input").attr("type", "text").attr("placeholder","Enter a driver");

        var foundDrivers = d3.select("body").append("div").attr("class", "foundDrivers");
        var notFoundMsg = d3.select("body").append("div")
            .attr("class", "emptyMsg")
            .text("No drivers found.")
            .style("display", "none");
        var input = d3.select("input")
            .on("keyup", displayResults);
        
        function displayResults(){
            var foundCount = 0;
            var dataDrivers = [];
            if (!document.querySelector("input").value.trim().length < 1) {
                for (var driver of drivers) {
                        if ((driver.givenName + " " + driver.familyName).toUpperCase().includes(document.querySelector("input").value.toUpperCase())) {
                            dataDrivers.push(driver);
                            foundCount++;
                        }
                    }
                    if (foundCount == 0) {
                        notFoundMsg.style("display", null);
                    }
                    else {
                        notFoundMsg.style("display", "none");
                    }
                }
            var driverData = foundDrivers
                .selectAll("div")
                .data(dataDrivers, function (d) { return d.driverId; });
            driverData.exit().remove();
            var driverInfo = driverData.enter()
                .append("div")
                .attr("class", "driverInfo");
            driverData.order();
            driverInfo.append("a")
                .text(function (d) { return d.givenName + " " + d.familyName; })
                .attr("class", "name")
                .attr("href", function (d) { return "driver.php?id=" + d.driverId; });               
        } 
    </script>
</body>