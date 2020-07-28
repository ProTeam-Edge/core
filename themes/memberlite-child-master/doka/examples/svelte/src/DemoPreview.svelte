<script>
import { DokaOverlay, toURL } from './svelte-doka';

let enabled = false;
let imagePreviewCrop = {
    aspectRatio: .5,
    rotation: -1.5707963268
};
let imagePreviewSource = 'assets/photo-preview.jpeg';
let imageSource = 'assets/photo.jpeg';

const handleConfirm = (output) => {
    console.log('Confirm edit!', output);
    enabled = false;
    imagePreviewSource = toURL(output.file);
    imagePreviewCrop = output.data.crop;
}

const handleCancel = () => {
    console.log('Cancel edit!');
    enabled = false;
}

</script>

<div>

    <h2>Demo Preview</h2>

    <button on:click={() => enabled = true}>Show Overlay</button>

    <DokaOverlay
        {enabled}
        utils="crop"
        src={imageSource}
        crop={imagePreviewCrop}
        onconfirm={handleConfirm}
        oncancel={handleCancel}>
        <img src={imagePreviewSource} alt="">
    </DokaOverlay>
    
</div>