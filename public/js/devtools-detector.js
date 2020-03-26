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
    function detectDevTool(allow) {
		if (window.disableDevToolsDetection) {
			return;
		}
        if (isNaN(+allow)) {
            allow = 100;
        }
        var start = +new Date();
        debugger;
        var end = +new Date();
        if (isNaN(start) || isNaN(end) || end - start > allow) {
            fireEvent(true);
        } else {
			fireEvent(false);
		}
    }
    if (window.attachEvent) {
        if (
            document.readyState === "complete" ||
            document.readyState === "interactive"
            ) {
                detectDevTool();
                window.attachEvent("onresize", detectDevTool);
                window.attachEvent("onmousemove", detectDevTool);
                window.attachEvent("onfocus", detectDevTool);
                window.attachEvent("onblur", detectDevTool);
            } else {
                setTimeout(argument.callee, 0);
            }
    } else {
        window.addEventListener("resize", detectDevTool);
        window.addEventListener("mousemove", detectDevTool);
    }
})();
