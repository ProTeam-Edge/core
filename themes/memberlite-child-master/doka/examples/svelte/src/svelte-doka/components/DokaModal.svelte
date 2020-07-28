<script>
import { onMount, onDestroy, beforeUpdate } from 'svelte';
import { create } from '../lib/doka.esm.min.js';

// lifecycle
let instance;
onMount(() => {
    instance = create($$props);
});

beforeUpdate(() => {
    if (!instance) return;
    instance.setOptions($$props);
});

onDestroy(() => {
    instance.destroy();
    instance = null;
});
</script>

<style global>
@import '../lib/doka.min.css';
</style>