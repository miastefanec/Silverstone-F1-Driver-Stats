<!DOCTYPE html>
<meta charset="utf-8">
<link rel="stylesheet" href="styles/style.css"></link>
<style>
@import url('https://fonts.googleapis.com/css2?family=Titillium+Web&display=swap');
</style> 
<title>Silverstone (British) Grand Prix Stats</title>

<body>
    <script src="//d3js.org/d3.v4.min.js"></script>

    <header id="mainHeader">
        <h1>Silverstone: F1 Driver Stats</h1>
    </header>

    <div id="welcomeText">
        The first ever F1 race in history was organized on 13th May, 1950 at the Silverstone circuit. Over 120,000 spectators came to watch
        the spectacle, including Royal Family. The track underwent a major redesign between the 1990 and 1991 races, transforming
        the ultra-fast track into a more technical track. 32 different racers won this Grand Prix.
        On the graph below you can see who are top 5 drivers and constructors at Silverstone Circuit with most wins.
    </div>
    <div id="contentGraph">
    <div id="btns">
                <button onclick="update(driverWins)">Drivers</button>
                <button onclick="update(constructorWins)">Constructors</button>
            </div>
        <div id="my_dataviz">
        </div>
    </div>

    <div id="search">
        <p>Search for a driver and see their stats: </p>
        <input type="text" placeholder="Enter a driver">
    </div>
    
    <script>
        var drivers = null;

        //povlačenje podataka u varijablu drivers pomoću api-ja
        d3.json("https://ergast.com/api/f1/drivers.json?limit=1000", function (error, arr) {
                drivers= arr["MRData"]["DriverTable"]["Drivers"];
        });


        //deklaracija potrebnih podataka za izradu horizontalnog stupčastog grafa i kružnih grafova
        var driverWins = [
            {name:"Michael Schumacher", wins: 3},
            {name:"Nigel Mansell", wins: 3},
            {name:"Jim Clark", wins: 3},
            {name:"Alain Prost", wins: 5},
            {name:"Lewis Hamilton", wins: 8}
        ];

        var constructorWins = [
            {name:"Red Bull", wins: 4},
            {name:"Mercedes", wins: 8},
            {name:"Williams", wins: 8},
            {name:"McLaren", wins: 12},
            {name:"Ferrari", wins: 15}
        ];

        var winnerRatio = {"British winners": 10, "Other nations": 22 };
        var winsRatio = {"Wins(British drivers)": 23, "Wins(Other nations)": 34};


        // postavljanje dimenzija i margine za horizontalni graf
        var margin = {top: 30, right: 30, bottom: 70, left: 300},
            width = 980 - margin.left - margin.right,
            height = 500 - margin.top - margin.bottom;
            
        // dodavanje svg elementa u koji će se smjestiti graf (ista logika vrijedi i za kružne grafove)
        var svg = d3.select("#my_dataviz")
        .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
        .append("g")
            .attr("transform",                                          
                "translate(" + margin.left + "," + margin.top + ")");   

        // inicijalizacija X osi
        var x = d3.scaleLinear()
        .range([ 0, width ]);
        var xAxis = svg.append("g")
        .attr("transform", "translate(0," + height + ")")
        .style("font-size", "13px")

        // inicijalizacija Y osi
        var y = d3.scaleBand()
        .range([ height, 0])
        .padding(.5);
        var yAxis = svg.append("g")
        .attr("class", "myYaxis")


        // ova funkcija ažurira graf prema učitanim podacima
        function update(data) {

            x.domain([0, d3.max(data, function(d) { return d.wins }) ]);
            xAxis.transition().duration(1000).call(d3.axisBottom(x));

            y.domain(data.map(function(d) { return d.name; }))
            yAxis.transition().duration(1000).call(d3.axisLeft(y).tickPadding(60));

            var u = svg.selectAll("rect")
                .data(data)

            //ovaj dio se koristi za nadodavanje novih pravokutnika koji predstavljaju horizontalne stupove na grafu, odnosno podatke    
            u
                .enter()
                .append("rect")
                .merge(u)
                .transition()
                .duration(1000)
                .attr("x", function(d) { return x(0); })
                .attr("y", function(d) { return y(d.name); })
                .attr("height", y.bandwidth())
                .attr("width", function(d) { return x(d.wins); })
                .attr("fill", "#a41b0e")

            // dodavanje ikona uz ime vozača na y osi
            svg.selectAll(".images")
                .data(data)
                .enter().append("svg:image")
			    .attr("x", -60 )
                .attr("y", function(d) { return y(d.name) -10;})
				.attr("width", 52)
				.attr("height", 52)
				.attr("xlink:href", function(d){
				return d.name == "Lewis Hamilton" ? "images/hamilton.jpg" : d.name == "Alain Prost" ? "images/prost.jpg" : 
                d.name == "Jim Clark" ? "images/clark.jpg" : d.name == "Nigel Mansell" ? "images/mansell.jpg": 
                d.name == "Michael Schumacher" ? "images/schumacher.jpg" : d.name == "Ferrari" ? "images/ferrari.jpg" : d.name == "McLaren" ? "images/mclaren.jpg" 
                : d.name == "Mercedes" ? "images/mercedes.jpg" : d.name == "Williams" ? "images/williams.jpg" : "images/redbull.jpg"
				});

            // brisanje podataka koji nisu trenutno aktivni 
            u
                .exit()
                .remove()
        }
        update(driverWins);


        //deklaracija boja koje će se koristit na kružnom grafu, deklaracija dimenzija
        var colors=d3.scaleOrdinal().range(["#3A0CA3","#F21B3F"]);
        var widthP = 280, heightP = 280, marginP = 20

        // radijus kružnog dijagrama je minimum između visine i širine (iste dimenzije pa je svejedno) podijeljeni s 2
        var radius = Math.min(widthP, heightP) / 2 - marginP

        // na div element s id-jem my_dataviz (koji je implementiran kroz HTML) se dodaje novi svg element
        var svgP = d3.select("#my_dataviz")
        .append("svg").attr("class","piec")
            .attr("width", widthP)
            .attr("height", heightP)
        .append("g")
            .attr("transform", "translate(" + widthP / 2 + "," + heightP / 2 + ")");

        // ovom funkcijom se određuju položaji podatkovnih grupa na grafu
        var pie = d3.pie().startAngle(20).endAngle(2*Math.PI)
        .value(function(d) {return d.value; })
        var data_ready = pie(d3.entries(winnerRatio))

        // u sljedećem dijelu koda se implementira kružni dijagram pomožu path atributa i arc funkcije
        svgP
        .selectAll('slices')
        .data(data_ready)
        .enter()
        .append('path')
        .attr('d', d3.arc()
            .innerRadius(90)         // This is the size of the donut hole
            .outerRadius(radius+2)
        )
        .attr('fill', function(d,i){ return colors(i) })
        .attr("stroke", "white")
        .style("stroke-width", "10px")
        .style("opacity", 0.9)
        
        //dodavanje teksta unutar kružnog grafa
        var pieText = svgP.append("text").attr("y","-40");
        pieText.append("tspan")
        .attr("class", "pieText")
        .style("fill", "#3A0CA3")
        .text("Brits: " + 10)
        .attr("x", "0")
        .attr("dy", "5%")
        .attr("text-anchor", "middle");

        pieText.append("tspan")
        .attr("class", "pieText")
        .style("fill", "#F21B3F")
        .text("Others: " + 22)
        .attr("x", "0")
        .attr("dy", "20%")
        .attr("text-anchor", "middle");

        var colorsi=d3.scaleOrdinal().range(["#0A369D","#D10000"]);

        var svgPi = d3.select("#my_dataviz")
        .append("svg").attr("class","piec")
            .attr("width", widthP)
            .attr("height", heightP)
        .append("g")
            .attr("transform", "translate(" + widthP / 2 + "," + heightP / 2 + ")");

        var pie = d3.pie().startAngle(30).endAngle(2*Math.PI)
        .value(function(d) {return d.value; })
        var data_ready = pie(d3.entries(winsRatio))

        svgPi
        .selectAll('slices')
        .data(data_ready)
        .enter()
        .append('path')
        .attr('d', d3.arc()
            .innerRadius(90)   
            .outerRadius(radius)
        )
        .attr('fill', function(d,i){ return colorsi(i) })
        .attr("stroke", "white")
        .style("stroke-width", "10px")
        .style("opacity", 0.9)

        var pieText = svgPi.append("text").attr("y","-40");
        pieText.append("tspan")
        .attr("class", "pieText")
        .style("fill", "#0A369D")
        .text("British wins: " + 23)
        .attr("x", "0")
        .attr("dy", "5%")
        .attr("text-anchor", "middle");

        pieText.append("tspan")
        .attr("class", "pieText")
        .style("fill", "#D10000")
        .text("Others: " + 34)
        .attr("x", "0")
        .attr("dy", "20%")
        .attr("text-anchor", "middle");


        //implementacija funkcije za pretraživanje i prikaz rezultata
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