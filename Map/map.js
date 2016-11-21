/**
 * Created by Yuyang He on 2016/11/10.
 */
/**
 * Check box select all elements.
 * @param obj current check box
 * @param name name of check box
 * @param idOfForm id of the form
 */
function selectAll(obj, name, idOfForm) {
    var checkboxs = document.getElementsByName(name);
    for (var i = 0; i < checkboxs.length; i++) {
        checkboxs[i].checked = obj.checked;
    }

    submitForm(idOfForm);
}

/**
 * After click on filter, check about the select all option.
 * @param idOfSelectAll id of select all checkbox
 * @param nameOfChkBox checkbox name
 * @param idOfForm id of the form
 */
function checkSelectAll(idOfSelectAll, nameOfChkBox, idOfForm) {
    var checkboxs = document.getElementsByName(nameOfChkBox);
    var isAllSelected = true;
    for (var i = 0; i < checkboxs.length; i++) {
        if (!checkboxs[i].checked) {
            isAllSelected = false;
        }
    }
    document.getElementById(idOfSelectAll).checked = isAllSelected;

    submitForm(idOfForm);
}

/**
 * Submit form.
 * @param id id of form
 */
function submitForm(id) {
    document.getElementById(id).submit();
}

/**
 * Visualization implementation.
 * @param data json data from DB
 */
function draw(data) {
    var nodes = data.nodes;
    var links = data.links;

    //console.log(nodes);

    // Compute the distinct nodes from the links.
    links.forEach(function (link) {
        link.source = nodes[link.source] ||
            (nodes[link.source] = {name: link.source});
        link.target = nodes[link.target] ||
            (nodes[link.target] = {name: link.target});
    });

    var width = 960,
        height = 500,
        color = d3.scale.category20c();


    var force = d3.layout.force()
        .nodes(d3.values(nodes))
        .links(links)
        .size([width, height])
        .linkDistance(120)
        .charge(-800)
        .on("tick", tick)
        .start();

    // Set the range
    var v = d3.scale.linear().range([0, 100]);

    // Scale the range of the data
    v.domain([0, d3.max(links, function (d) {
        return d.value;
    })]);


    var svg = d3.select("#d3AreaMain").append("svg")
        .attr("width", width)
        .attr("height", height);


    // add the links and the arrows
    var path = svg.append("svg:g").selectAll("path")
        .data(force.links())
        .enter().append("svg:path")
        .attr("class", function (d) {
            return "link " + d.type;
        });


    // change the link color
    var link2 = d3.select("#d3AreaMain").selectAll("path.link")
        .style("stroke", function (d) {
            return "black";
        })

    link2.style("visibility", "hidden");

    // define the nodes
    var node = svg.selectAll(".node")
        .data(force.nodes())
        .enter().append("g")
        .attr("class", "node")
        .call(force.drag);

    // add the nodes
    node.append("circle")
        .attr("r", function (d) {
            if (d.type == "person") {
                return 20;
            }
            else {
                return 50 * Math.sqrt(d.weight);
            }
        });

    node.transition().attr("visibility", function (d) {
        if (d.type == "person") {
            return "hidden";
        }
    })
    // add the node name
    node.append("foreignObject")
        .attr("x", -45)
        .attr("y", -30)
        .attr("width", function (d) {
            return 2 * 20;
        })
        .attr("height", function (d) {
            return 2 * 20;
        })
        .append("xhtml:body")
        .html(function (d) {
            return d.name;
        });

    // pining the node
    node.on("click", function (d) {
        //console.log(d.id);
        var list = [];
        link2.transition()
            .style("visibility", function (e) {

                if (e.source.id == d.id || e.target.id == d.id) {
                    list.push(e.source.id);
                    list.push(e.target.id);
                    return "visible";
                }
                else return "hidden";
            })
        node.transition()
            .style("visibility", function (e) {
                console.log(list.indexOf(e.id));
                return (list.indexOf(e.id) > -1) ? "visible" : "hidden";
            })
    });

    // add the curvy lines
    function tick() {
        path.attr("d", function (d) {
            var dx = d.target.x - d.source.x,
                dy = d.target.y - d.source.y,
                dr = Math.sqrt(dx * dx + dy * dy);
            return "M" +
                d.source.x + "," +
                d.source.y + "A" +
                dr + "," + dr + " 0 0,1 " +
                d.target.x + "," +
                d.target.y;
        });

        node
            .attr("transform", function (d) {
                return "translate(" + d.x + "," + d.y + ")";
            });
    };

}
