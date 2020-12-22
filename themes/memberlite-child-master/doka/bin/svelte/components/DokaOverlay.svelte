<script>
import { onDestroy, beforeUpdate } from 'svelte';
import { create } from '../lib/doka.esm.min.js';

// custom property
export let enabled;

// helper methods to quickly create and destroy a doka instance
const createDokaInstance = () => {
    if (instance) return;
    instance = create(root, {
        ...$$props,
        styleLayoutMode: 'preview',
        outputData: true,
        onclose: () => {
            instance.destroy();
            instance = null;
        }
    })
}

const destroyDokaInstance = () => {
    if (!instance) return;
    instance.destroy();
    instance = null;
}

// lifecycle
let root;
let instance;

beforeUpdate(() => {
    if (enabled && !instance) {
        createDokaInstance();
    }
    else if (!enabled && instance) {
        destroyDokaInstance();
    }
    else if (instance) {
        instance.setOptions($$props);
    }
})

onDestroy(() => {
    destroyInstance()
});
</script>

<div class="doka-svelte--overlay">
    <slot></slot>
    <div class="doka-svelte--container">
        <div bind:this={root}></div>
    </div>
</div>


<style type="text/css" global>

@import '../lib/doka.min.css';

.doka-svelte--overlay {
    position: relative;
    overflow: hidden;
}

.doka-svelte--overlay > img {
    display: block;
    width: 100%;
    height: auto;
}

.doka-svelte--overlay > .doka-svelte--container {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
}
</style>