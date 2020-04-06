/**
 * Resources:
 * 
 * With window.innerWidth / firebug:
 *  https://github.com/sindresorhus/devtools-detect
 * 
 * With debugger:
 *  https://dev.to/composite/a-simple-way-to-detect-devtools-2ag0
 *  https://github.com/kinging123/devtools-detector
 * 
 * With object getter:
 *  https://jsfiddle.net/evnrorea/
 *  https://yon.fun/detect-chrome-dev-tools/
 * 
 * With function.toString:
 *  https://stackoverflow.com/a/7809413/2588319
 * 
 * 
 * With RequestAnimationFrame:
 *  https://stackoverflow.com/a/48287643/2588319
 *  https://jsfiddle.net/gcdfs3oo/44/
 * 
 * Bug discussion on Chromium:
 *  https://bugs.chromium.org/p/chromium/issues/detail?id=672625
 */

(function() {
	function fireEvent(isOpen) {
		window.dispatchEvent(
			new CustomEvent("devtoolschange", {
				detail: {
					isOpen: isOpen
				}
			})
		);
    }

    var element = new Image();
    Object.defineProperty(element, 'id', {
        get: function () {
            fireEvent(true);
        }
    });

    requestAnimationFrame(function check() {
        console.dir(element);
        requestAnimationFrame(check);
    });
})();
