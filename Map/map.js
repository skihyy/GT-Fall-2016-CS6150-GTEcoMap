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

function changeResetButtonColor(id){
    //console.log("Click to change color!");
    var button = document.getElementById(id);
    //console.log(id);
    //console.log(button)
    button.style.background = "#0000CD";
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

        //console.log(link);
    });


    // Set the width
    var width = 960,
        //for every 15 node, add another 500 height
        height = (~~(nodes.length / 15) + 1) * 600,
        color = d3.scale.category20c();


    // Generate force
    var force = d3.layout.force()
        .nodes(d3.values(nodes))
        .links(links)
        .size([width, height])
        .linkDistance(130)
        .charge(-1200)
        .on("tick", tick)
        .start();

    // Set the range
    var v = d3.scale.linear().range([0, 100]);

    // Scale the range of the data
    v.domain([0, d3.max(links, function (d) {
        return d.value;
    })]);

    // Initilize svg canvas
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

    // This clipPath is for later cropping the avatar image for "person" node
    defs = svg.append('defs');
    var clipPath = defs.append('clipPath')
        .attr('id', 'clip-circle')
        .append('circle')
        .attr('r', 20);

    // define the links
    var link = d3.select("#d3AreaMain").selectAll("path.link")
        .style("stroke", function (d) {
            return "black";
        })

    // hide all the links in default
    link.style("visibility", "hidden");

    // define the nodes
    var node = svg.selectAll(".node")
        .data(force.nodes())
        .enter().append("g")
        .attr("class", "node")
        .call(force.drag);

    // add the circle
    node.append("circle")
    // fill with the node color
        .attr("fill", function (d) {
            if (d.color == "") return "#CCC";
            else return d.color;
        })
        // set the size of the circle
        .attr("r", function (d) {
            // person node is one-size for all
            if (d.type == "person") {
                return 20;
            }
            // project node size is based on how many people work on it
            else {
                return 60 * Math.pow(d.weight, 1 / 4);
            }
        });

    // hide all the person node in default
    node.style("visibility", function (d) {
        if (d.type == "person") {
            return "hidden";
        }
    })

    // add avatar image for person node
    node.filter(function (d) {
        return d.type == "person";
    })
        .append("svg:image")
        //.attr("xlink:href", "http://safariuganda.com/wp-content/uploads/2014/12/480px-Facebook-default-no-profile-pic.jpg")
        .attr("xlink:href", function (d) {
            return d.imgLink;
        })
        .attr("height", 20 * 2)
        .attr("width", 20 * 2)
        .attr("x", "-" + 20)
        .attr("y", "-" + 20)
        .attr('clip-path', 'url(#clip-circle)');


    // add the project's name
    // Text-warpping is bit complicate in d3 circles, foreignObject is what we decide to use
    // Use foreignObject does creatre some inconsistency and it has some flaws, replace it if any better options has been found
    node.filter(function (d) {
        return d.type == "project";
    })
        .append("foreignObject")
        .attr("x", -45)
        .attr("y", -30)
        .attr("width", function (d) {
            return 80;
        })
        .attr("height", function (d) {
            return 80;
        })
        .attr("isExpend", "false")
        .append("xhtml:body")
        .style("margin", "8pt")
        .html(function (d) {
            return d.name;
        })
        .style("font-size", "85%")
        // add the project's hyperlink if it has any
        .append("foreignObject")
        .filter(function (d) {
            return d.link != null;
        })
        .append("xhtml:body")
        .html(function (d) {
            return "more";
        })
        .on("click", function (d) {
            window.open("http://" + d.link);
        })
        .style("color", "blue")
        .style("text-decoration", "underline")
        .style("cursor", "pointer")
        .style("font-size", "75%")


    // add the person's name
    node.filter(function (d) {
        return d.type == "person";
    })
        .append("text")
        .attr("dy", 30)
        .attr("dx", 15)
        .text(function (d) {
            return d.name;
        })
        .on("click", function (d) {
            window.open("http://" + d.link);
        })


    // add the person's hyperlink text if a person has a link
    node.filter(function (d) {
        return d.type == "person";
    })
        .filter(function (d) {
            return d.link != null;
        })
        .select("text")
        .on("click", function (d) {
            window.open("http://" + d.link);
        })
        .style("text-decoration", "underline")
        .style("cursor", "pointer")


    //clicking the node logic
    node.on("click", function (d) {
        //console.log("Click!");
        //changeResetButtonColor("resetButtonButton");

        //console.log(d.id);
        // show all the links that connect to this node
        // store all the node that connect to this node to a list for later check
        var list = [];
        link.transition()
            .style("visibility", function (e) {

                if (e.source.id == d.id || e.target.id == d.id) {
                    list.push(e.source.id);
                    list.push(e.target.id);
                    return "visible";
                }
                else return "hidden";
            })
        //console.log(list);

        // gery-out all the other projects that not in the previous stored list
        node.transition()
            .style("opacity", function (e) {
                if (e.type == "project") {
                    return (list.indexOf(e.id) > -1) ? "1" : "0.2";
                }

            })
            // show the person that stored in the previous list
            .style("visibility", function (e) {
                if (e.type == "person") {
                    //console.log(e.id);
                    //console.log(list.indexOf(e.id));
                    return (list.indexOf(e.id) > -1) ? "visible" : "hidden";
                }

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

        node.attr("transform", function (d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
    };

}
