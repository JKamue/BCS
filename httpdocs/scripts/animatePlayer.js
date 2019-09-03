function animatePlayer(name, id) {
	var target = document.getElementById(id);
	var skinRender = new SkinRender({
		canvas: {
			width: "250",
			height: "500"
		},
		controls: {
			enabled: false,
			zoom: false,
			rotate: false,
			pan: false
		},camera: {
			x: -20,
			y: 20,
			z: 20,
			target: [0, 17, 0]
		},
	}, target);
	skinRender.render({
		username: name,
		optifine: true
	});
	var animate = true;
	var startTime = Date.now();
	target.addEventListener("skinRender", function (e) {
		if (animate) {
			var t = (Date.now() - startTime) / 1000;
			e.detail.playerModel.children[2].rotation.x = Math.sin(t * 5) / 2;
			e.detail.playerModel.children[3].rotation.x = -Math.sin(t * 5) / 2;
			e.detail.playerModel.children[4].rotation.x = Math.sin(t * 5) / 2;
			e.detail.playerModel.children[5].rotation.x = -Math.sin(t * 5) / 2;
		}
	})
}