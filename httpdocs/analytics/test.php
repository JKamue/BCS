
<!doctype html>
<!-- saved from url=(0044)http://kenedict.com/networks/worldcup14/vis/ , thanks Andre!-->
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF8">
    <title>Vis Network | Example Applications | World Cup Network</title>

    <meta name="example-screenshot-delay" content="20" />

    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

    <script src="data.js"></script>

    <style type="text/css">
        #mynetwork {
            width: 800px;
            height: 800px;
            border: 1px solid lightgray;
        }
    </style>

</head>

<body>

<h2>Performance - World Cup Network</h2>

<div style="width:700px; font-size:14px;">
    This example shows the performance of vis with a larger network. The edges in
    particular (~9200) are very computationally intensive
    to draw. Drag and hold the graph to see the performance difference if the
    edges are hidden.
    <br/><br/>
    We use the following physics configuration: <br/>
    <code>{barnesHut: {gravitationalConstant: -80000, springConstant: 0.001,
        springLength: 200}}</code>
    <br/><br/>
</div>

<div id="mynetwork"></div>

<script type="text/javascript">
    var network;


    function redrawAll() {
        // remove positoins
        for (var i = 0; i < nodes.length; i++) {
            delete nodes[i].x;
            delete nodes[i].y;
        }

        // create a network
        var container = document.getElementById('mynetwork');
        var data = {
            nodes: nodes,
            edges: edges
        };
        var options = {
            nodes: {
                shape: 'dot',
                scaling: {
                    min: 10,
                    max: 30
                },
                font: {
                    size: 12,
                    face: 'Tahoma'
                }
            },
            edges: {
                width: 0.15,
                color: {inherit: 'from'},
                smooth: {
                    type: 'continuous'
                }
            },
            physics: {
                stabilization: false,
                barnesHut: {
                    gravitationalConstant: -80000,
                    springConstant: 0.001,
                    springLength: 200
                }
            },
            interaction: {
                tooltipDelay: 200,
                hideEdgesOnDrag: true
            }
        };

        // Note: data is coming from ./datasources/WorldCup2014.js
        network = new vis.Network(container, data, options);
    }

    redrawAll()
</script>
</body>
</html>
