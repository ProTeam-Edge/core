<script>
import { onMount, onDestroy, beforeUpdate } from 'svelte';
import { create } from '../lib/doka.esm.min.js';

// custom prop
export let style;

// lifecycle
let root;
let wrapper;
let instance;

onMount(() => {
    wrapper.style.cssText = style;
    instance = create(root || wrapper.children[0], $$props);
    console.log(style);
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

<div bind:this={wrapper}>
    <slot>
        <div bind:this={root}></div>
    </slot>
</div>

<style global>
@import '../lib/doka.min.css';
</style>