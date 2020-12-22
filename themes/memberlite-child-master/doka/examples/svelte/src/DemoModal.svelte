<script>
import { DokaModal, toURL } from './svelte-doka';

let src = 'assets/photo.jpeg';
let enabled = false;
let result = null;

const handleConfirm = (output) => {
    console.log('Confirm edit!', output);
    result = toURL(output.file);
}

const handleCancel = () => {
    console.log('Cancel edit!');
}

const handleClose = () => {
    console.log('Close edit!');
    enabled = false;
}
</script>

<div>

    <h2>Demo Modal</h2>

    <button on:click={() => enabled = true}>Show Modal</button>

    {#if enabled}
    <DokaModal
        {src}
        cropAspectRatio="1"
        onconfirm={handleConfirm}
        oncancel={handleCancel}
        onclose={handleClose}/>
    {/if}
    
    {#if result}
    <img src={result} alt="" />
    {/if}
    
</div>