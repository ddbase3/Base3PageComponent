function loadScriptOnce(src, callback) {
	if (document.querySelector(`script[src="${src}"]`)) {
		// Script already included
		callback?.();
		return;
	}

	const script = document.createElement('script');
	script.src = src;
	script.async = true;
	script.onload = () => callback?.();
	document.head.appendChild(script);
}
