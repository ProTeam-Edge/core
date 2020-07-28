<script>
import DemoInline from './DemoInline.svelte';
import DemoModal from './DemoModal.svelte';
import DemoPreview from './DemoPreview.svelte';
import DemoProfile from './DemoProfile.svelte';
</script>

<div>
	<h1>Doka Demos</h1>
	<DemoInline/>
	<DemoModal/>
	<DemoProfile/>
	<DemoPreview/>
</div>

<style>
div {
	margin: 0;
	padding:40px;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen",
		"Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue",
		sans-serif;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}
</style>