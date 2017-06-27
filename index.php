<?
    $title = $_REQUEST['title'];
    $filename = basename($_REQUEST['i']) . '.jpg';
    $small_filename = basename($_REQUEST['i']) . '_small.jpg';
    $base_url = 'http://'.$_SERVER['HTTP_HOST'].'/'.dirname($_SERVER['SCRIPT_NAME']).'/';
    $request_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<meta property="og:title" content="Photo sphere: <?=$title?>" />
<meta property="og:type" content="article" />
<meta property="og:url" content="<?=$request_url?>" />
<meta property="og:image" content="<?=$base_url.$small_filename?>" />
<meta name="description" content="Standalone viewer for Android photo spheres" />
<meta property="og:description" content="Standalone viewer for Android photo spheres" />
<head>
	<title><?=$title?> - Photo sphere viewer</title>
	<style>
		body { margin: 0; overflow: hidden; background-color: #000; }
        #loading { position: absolute; top: 10px; left: 10px; color: white; font-size: 50pt }
		.tm  { position: absolute; top: 10px; right: 10px; }
    </style>	
</head>
<body>
    <div id="loading">Loading sphere (<span id="percent">?</span>%)...</div>
	<div id="sphere"></div>
	<script src="js/three.min.js"></script>
	<script src="js/OrbitControls.js"></script>	
	<script src="js/Detector.js"></script>		
	<script>

		var webglEl = document.getElementById('sphere');

		var width  = window.innerWidth,
			height = window.innerHeight;

		var scene = new THREE.Scene();

		var camera = new THREE.PerspectiveCamera(75, width / height, 1, 1000);
		camera.position.x = 0.1;

        var sphere = null;
        var controls = null;

        var image = null;

        var controls = new THREE.OrbitControls(camera);

		var sphere = new THREE.Mesh(
			new THREE.SphereGeometry(100, 20, 20),
			new THREE.MeshBasicMaterial({
                map: (function (url, mapping, onLoad, onProgress, onError) {
                        // load texture image using XHR to get progress events
                        var loader = new THREE.XHRLoader();
                        loader.setCrossOrigin(THREE.ImageUtils.crossOrigin);
                        var texture = new THREE.Texture( undefined, mapping );
                        var request = new XMLHttpRequest();
                        request.onload = function () {
                            image = document.createElement('img');
                            image.src = 'data:image/jpeg;base64,' + base64Encode(request.responseText);
                            image.style = 'visibility: hidden';
                            document.body.appendChild(image);
                            texture.image = image;
                            texture.needsUpdate = true;
                            if (onLoad) onLoad(texture);
                        };
                        request.onprogress = onProgress;
                        request.onerror = onError;
                        request.open('GET', url, true);
                        request.overrideMimeType('text/plain; charset=x-user-defined'); 
                        request.send(null);
                        texture.sourceFile = url;
                        return texture;
                     })('<?=$filename?>', undefined,
                        function () { var d = document.getElementById('loading'); d.parentNode.removeChild(d); finish_setup(); },
                        function (p) { document.getElementById('percent').innerHTML = Math.round(p.loaded/p.total*1000)/10; },
                        function (e) { alert('Error loading sphere!'); console.log(e); })
			})
		);

        function finish_setup() {
            sphere.scale.x = -1;
            scene.add(sphere);

            controls.noPan = true;
            controls.noZoom = true; 
            controls.autoRotate = 2;
            controls.autoRotateSpeed = 0.2;

            if ( Detector.webgl ) {
                renderer = new THREE.WebGLRenderer();
                renderer.setSize(width, height);
                webglEl.appendChild(renderer.domElement);
                render();
            } else {
                function load(s, f) {
                    var script = document.createElement('script');
                    script.src = s;
                    script.type = 'text/javascript';
                    script.onload = f;
                    document.getElementsByTagName('head')[0].appendChild(script);
                }
                load('js/Projector.js', function () {
                    load('js/SoftwareRenderer.js', function () {
                        renderer = new THREE.SoftwareRenderer();
                        renderer.setSize(width, height);
                        webglEl.appendChild(renderer.domElement);
                        render();
                    })
                });
            }
        }

		function render() {
			controls.update();
			requestAnimationFrame(render);
			renderer.render(scene, camera);
		}

		function onMouseWheel(event) {
			event.preventDefault();
			
			if (event.wheelDeltaY) { // WebKit
				camera.fov -= event.wheelDeltaY * 0.05;
			} else if (event.wheelDelta) { 	// Opera / IE9
				camera.fov -= event.wheelDelta * 0.05;
			} else if (event.detail) { // Firefox
				camera.fov += event.detail * 1.0;
			}

			camera.fov = Math.max(40, Math.min(100, camera.fov));
			camera.updateProjectionMatrix();
		}

        // This encoding function is from Philippe Tenenhaus's example at http://www.philten.com/us-xmlhttprequest-image/
        function base64Encode(inputStr) 
        {
            var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
            var outputStr = "";
            var i = 0;

            while (i < inputStr.length)
            {
                //all three "& 0xff" added below are there to fix a known bug 
                //with bytes returned by xhr.responseText
                var byte1 = inputStr.charCodeAt(i++) & 0xff;
                var byte2 = inputStr.charCodeAt(i++) & 0xff;
                var byte3 = inputStr.charCodeAt(i++) & 0xff;

                var enc1 = byte1 >> 2;
                var enc2 = ((byte1 & 3) << 4) | (byte2 >> 4);

                var enc3, enc4;
                if (isNaN(byte2))
                {
                    enc3 = enc4 = 64;
                }
                else
                {
                    enc3 = ((byte2 & 15) << 2) | (byte3 >> 6);
                    if (isNaN(byte3))
                    {
                        enc4 = 64;
                    }
                    else
                    {
                        enc4 = byte3 & 63;
                    }
                }

                outputStr += b64.charAt(enc1) + b64.charAt(enc2) + b64.charAt(enc3) + b64.charAt(enc4);
            } 

            return outputStr;
        }

		document.addEventListener('mousewheel', onMouseWheel, false);
		document.addEventListener('DOMMouseScroll', onMouseWheel, false);

	</script>
</body>
</html>
