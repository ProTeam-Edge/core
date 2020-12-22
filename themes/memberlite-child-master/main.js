( function () {
	window.addEventListener( "message", ( event ) => {

		var name = event.data.name;
		var data = event.data.data;
		console.log( "app_message", name, data );

	});

	window.parent.postMessage({ name: "app_ready" }, "*" );

} () );
